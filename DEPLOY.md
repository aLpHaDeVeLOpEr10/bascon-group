# Deploying Bascon Accounts

Operational guide for the Laravel app at <https://petal-monetary-cube.ngrok-free.dev>.
For *why* the port works the way it does, read [MIGRATION.md](MIGRATION.md) first —
this file assumes you already know it is a CodeIgniter port sharing the legacy
schema.

---

## The one thing that will catch you out

**This app's schema came from a SQL dump, and migrations were retrofitted on top.**

`database/migrations/2026_07_01_000000_create_legacy_schema.php` now creates the
24 legacy tables, so `migrate` works against an empty database. But it is a
*reconstruction* of the dump, and there are **no seeders**. A rebuilt database is
structurally perfect and completely empty.

So: `migrate:fresh` on production gives you a working, blank accounts system.
There is a guard in `AppServiceProvider::blockDestructiveMigrateCommands()` that
refuses `migrate:fresh`, `migrate:refresh` and `migrate:reset` while
`APP_ENV=production`. Don't remove it.

---

## The server

| | |
|---|---|
| Host | `204.168.226.248` — Hetzner, `ubuntu-8gb-hel1-1` |
| Access | `ssh root@204.168.226.248` |
| App root | `/var/www/bascon` |
| Repo | `git@github.com:aLpHaDeVeLOpEr10/bascon-group.git`, branch `main` |
| Web root | `/var/www/bascon/public` |
| nginx | `/etc/nginx/sites-enabled/bascon` — listens on **8090**, not 80/443 |
| PHP | 8.2.30 FPM, socket `/run/php/php8.2-fpm.sock`, workers run as `www-data` |
| Database | MySQL Community Server (`mysql.service`), schema `bascon`, user `bascon` |
| Public URL | ngrok, systemd unit `ngrok-bascon.service`, config `/etc/ngrok/bascon.yml` |
| Backups | `/root/bascon-backups/` |
| Tooling | Composer 2.9.5, Node 18.19.1, npm 9.2.0 |

This box hosts eight sites. **Pixbee is on the same machine** and serves the
default HTTPS vhost, so hitting the bare IP in a browser shows Pixbee, not
Bascon. Always confirm you are in `/var/www/bascon` before touching anything.

### How the public URL works

nginx serves Bascon on port **8090**, bound to localhost. `ngrok-bascon.service`
tunnels `https://petal-monetary-cube.ngrok-free.dev` → `http://localhost:8090`.
The domain is reserved on the ngrok account, so it survives agent restarts.

```bash
systemctl status ngrok-bascon      # tunnel health
systemctl restart ngrok-bascon     # if the public URL 502s but localhost:8090 works
curl -I http://localhost:8090/     # does nginx serve it at all?
```

---

## Routine deploy

No deploy script exists for Bascon yet. `/root/02-deploy.sh` is **Pixbee's**
(`APP=/var/www/pixbee`) — useful as a template, not runnable here.

```bash
ssh root@204.168.226.248
cd /var/www/bascon

# 1. Back up the database first. Always.
bash /root/backup.sh

# 2. Maintenance mode
php artisan down --retry=60

# 3. Pull
git fetch origin main
git merge --ff-only origin/main
git rev-parse --short HEAD          # note this — it is your rollback target

# 4. Dependencies, only if composer.lock changed
composer install --no-dev --optimize-autoloader --no-interaction
# otherwise just refresh the classmap:
composer dump-autoload -o --no-interaction

# 5. Migrations
php artisan migrate --force

# 6. Rebuild caches
php artisan optimize:clear
php artisan optimize

# 7. Fix ownership — artisan ran as root, php-fpm runs as www-data
chown -R www-data:www-data storage bootstrap/cache
systemctl reload php8.2-fpm

# 8. Back up
php artisan up

# 9. Verify
curl -s -o /dev/null -w "%{http_code}\n" https://petal-monetary-cube.ngrok-free.dev/
tail -20 storage/logs/laravel.log
tail -20 /var/log/nginx/bascon-error.log
```

**Step 7 is not optional.** Running artisan as root leaves root-owned files in
`storage/framework/views` and `bootstrap/cache`; php-fpm then cannot write them
and every page 500s. It is the most common way to break this deploy.

### Frontend assets

`public/build/` **is committed to git**, so a normal deploy needs no Node step.
Build locally, commit the output, push.

```bash
npm run build && git add public/build && git commit
```

If you do build on the server, commit or delete the result — the server
currently carries two untracked files in `public/build/assets/` that are not in
git, which means the deployed CSS/JS does not match any commit.

`php artisan storage:link` is **not** needed. Avatars are written straight to
`public/uploads/avatars` (see `App\Services\AvatarService`), not to
`storage/app/public`. That directory must stay writable by `www-data`.

---

## Database operations

The `bascon` MySQL user is scoped to its own schema and has no `PROCESS`
privilege, so `mysqldump` prints:

```
mysqldump: Error: 'Access denied; you need (at least one of) the PROCESS
privilege(s) for this operation' when trying to dump tablespaces
```

**This is a warning, not a failure** — the dump still completes with every table
and a clean `-- Dump completed` footer. Pass `--no-tablespaces` to silence it.
Verify a dump by its footer and table count, not by whether that line appeared.

### Back up

```bash
bash /root/backup.sh        # → /root/bascon-backups/, .sql plus .sql.gz
```

### Restore

```bash
cd /var/www/bascon
DBU=$(sed -n 's/^DB_USERNAME=//p' .env | tr -d '"\r')
export MYSQL_PWD=$(sed -n 's/^DB_PASSWORD=//p' .env | tr -d '"\r')
mysql -u"$DBU" bascon < /root/bascon-backups/<dump>.sql
php artisan optimize:clear
chown -R www-data:www-data storage bootstrap/cache
```

### Rebuild from a dump

Import **first**, migrate **second**. The additive migrations all guard on
`Schema::hasTable()`, so running them against an empty database silently does
nothing useful.

```bash
mysql -u root -p -e "DROP DATABASE bascon; CREATE DATABASE bascon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p bascon < <dump>.sql
php artisan migrate --force
php artisan dates:normalize --apply     # see the warning below
```

### Copying files to/from this server

`pscp`/`scp` is unreliable against this host — it failed 11 consecutive attempts
during the last migration. Base64 over the SSH channel works first time:

```bash
ssh root@204.168.226.248 "base64 -w0 /root/file.gz" > out.b64
base64 -d out.b64 > file.gz
```

---

## Console commands

```bash
php artisan dates:normalize         # dry run
php artisan dates:normalize --apply
php artisan rollups:repair          # dry run
php artisan rollups:repair --apply
```

### `dates:normalize`

Populates the `date_n DATE` columns from the legacy VARCHAR `date` strings. It
is a **point-in-time backfill** — it only fills what exists when it runs. Run it
after any data import, never before.

> **Known issue, not yet deployed.** The server's copy of
> `app/Console/Commands/NormalizeDates.php` still carries a stale rule at line 65:
> `'payments_recieved' => ['mdy_max_id' => 293]`. Production data is entirely
> `m/d/Y`; that boundary leaves 12 payment dates NULL and silently records 4 more
> in the wrong month. The fix (`'payments_recieved' => 'mdy'`) is in the repo —
> **deploy it before ever running this command again.**

### `rollups:repair`

`total_payement`, `finish_total` and `labour_total` are denormalised caches the
client dashboards read directly. The legacy app only refreshed them on insert,
so edits and deletes left them drifted. Currently **57 rows drifted, 21 on money
columns**. Applying rewrites customer-visible financial figures — get a human
decision first. Safe to leave alone.

---

## Things that bite

**Never run `migrate:fresh` against production.** Guarded, but understand why:
migrations rebuild the schema, nothing rebuilds the rows, and there are no
seeders.

**`AUTH_LEGACY_REHASH` stays `false`** while the CodeIgniter app is still live.
Flipping it upgrades passwords to bcrypt on login, which locks those users out
of the old app — it compares md5/plaintext with a raw SQL `WHERE`. Set it to
`true` only once CodeIgniter is retired for good.

**`config:cache` is safe here.** There are zero `env()` calls outside `config/`,
and routes contain no closures, so `php artisan optimize` will not silently
break configuration. Verified.

**Table name case matters on Linux.** `Company_architect_site` has a capital C
in production and in `App\Models\CompanyArchitectSite`. A dump taken on Windows
folds it to lowercase, and the lowercase table will not resolve here.

**`QUEUE_CONNECTION=database` with no `jobs` table.** Nothing in `app/` dispatches
jobs or uses `Cache::`, so this is latent rather than broken. If you start using
either, create the tables or switch to `sync`/`file`.

---

## Rollback

```bash
cd /var/www/bascon
php artisan down
git checkout <previous-sha>
composer install --no-dev --optimize-autoloader     # only if lock changed
php artisan optimize:clear && php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

If a migration is the problem, restore the database from the backup taken in
step 1 rather than relying on `down()` — several migrations are guarded and
their `down()` is deliberately conservative.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| Every page 500s after a deploy | root-owned cache files | `chown -R www-data:www-data storage bootstrap/cache && systemctl reload php8.2-fpm` |
| Public URL 502s, `localhost:8090` fine | ngrok agent died | `systemctl restart ngrok-bascon` |
| Bare IP shows a wallpaper site | that is Pixbee's default vhost | expected — use the ngrok URL |
| `Table 'bascon.labour' doesn't exist` during migrate | migrating an empty database that was never imported | import a dump, then `migrate` |
| `mysqldump: ... PROCESS privilege` | `bascon` user is schema-scoped | harmless — dump still completes; add `--no-tablespaces` to silence |
| Config change has no effect | stale cache | `php artisan optimize:clear && php artisan optimize` |

Logs: `storage/logs/laravel.log`, `/var/log/nginx/bascon-error.log`,
`journalctl -u ngrok-bascon -n 50`.
