# Deploying Bascon Accounts

Laravel 12 application. Steps to deploy on a new server.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`
- MySQL 5.7+ or MariaDB 10.4+
- Composer 2
- Web server able to serve a document root (nginx + PHP-FPM, or Apache)

## 1. Get the code

```bash
git clone <repo-url> bascon
cd bascon
git checkout main
```

## 2. Install dependencies

```bash
composer install --no-dev --optimize-autoloader
```

## 3. Create the database

```sql
CREATE DATABASE bascon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bascon'@'localhost' IDENTIFIED BY '<password>';
GRANT ALL PRIVILEGES ON bascon.* TO 'bascon'@'localhost';
FLUSH PRIVILEGES;
```

## 4. Configure `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Set:

```ini
APP_NAME="Bascon Accounts"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bascon
DB_USERNAME=bascon
DB_PASSWORD=<password>

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

AUTH_LEGACY_REHASH=false
```

> Change the three driver lines. `.env.example` defaults them to `database`, and
> this app has no migrations creating the `sessions`, `cache` or `jobs` tables —
> leave them and the app fails on first request.

## 5. Set up the database

**With existing data** — import first, migrate second:

```bash
mysql -u bascon -p bascon < dump.sql
php artisan migrate --force
php artisan dates:normalize --apply
```

**Empty system:**

```bash
php artisan migrate --force
```

> Order matters. Most migrations only add columns to tables the dump creates,
> and they skip silently if those tables are absent.

> `migrate` creates the schema but no rows — there are no seeders. On an empty
> system you must insert the first `admin` row by hand; there is no admin
> registration screen.

## 6. Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache public/uploads
chmod -R 775 storage bootstrap/cache public/uploads
```

> `php artisan storage:link` is not needed — uploads are written directly to
> `public/uploads`.

## 7. Web server

Document root must be **`public/`**, not the project root.

```nginx
server {
    listen 80;
    server_name your-domain;
    root /path/to/bascon/public;
    index index.php;

    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

Apache: enable `mod_rewrite`, point `DocumentRoot` at `public/`.

Behind a TLS-terminating proxy, also pass `fastcgi_param HTTPS on;`.

## 8. Cache and verify

```bash
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache

curl -s -o /dev/null -w "%{http_code}\n" https://your-domain/
php artisan migrate:status
tail -20 storage/logs/laravel.log
```

---

## Updating a deployment

```bash
mysqldump -u bascon -p --single-transaction --no-tablespaces bascon > backup-$(date +%F-%H%M).sql

php artisan down --retry=60

git fetch origin main
git merge --ff-only origin/main
git rev-parse --short HEAD                          # rollback target

composer install --no-dev --optimize-autoloader     # only if composer.lock changed
composer dump-autoload -o                           # otherwise just this

php artisan migrate --force
php artisan optimize:clear
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

> If you ran any of the above as root, the `chown` is required — root-owned
> files in `storage/framework/views` and `bootstrap/cache` make every page 500.

### Rollback

```bash
php artisan down
git checkout <previous-sha>
composer install --no-dev --optimize-autoloader     # only if the lock changed
php artisan optimize:clear && php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

If a migration caused it, restore the backup rather than relying on `down()`.

---

## Frontend assets

`public/build/` is committed, so no Node is needed on the server. To change
assets, build locally and commit:

```bash
npm ci && npm run build
git add public/build && git commit -m "build assets"
```

---

## Console commands

```bash
php artisan dates:normalize          # dry run; --apply to write
php artisan rollups:repair           # dry run; --apply to write
```

**`dates:normalize`** backfills the `date_n` columns from the legacy VARCHAR
dates. Run after every import. Confirm the dry run reports zero unparsed rows
before applying.

**`rollups:repair`** reconciles the `total_payement`, `finish_total` and
`labour_total` caches. `--apply` rewrites customer-visible financial figures —
review the dry run first.

---

## Notes

**Never run `migrate:fresh` on a deployment holding data.** Migrations rebuild
the schema, nothing rebuilds the rows, and there are no seeders. A guard in
`AppServiceProvider` blocks it while `APP_ENV=production`; leave it in place.

**`AUTH_LEGACY_REHASH`** upgrades legacy md5/plaintext passwords to bcrypt on
login. Keep it `false` while the original CodeIgniter app still runs against the
same database, or those users lose access to it. Enable only after that app is
retired.

**Table name case matters on Linux.** `Company_architect_site` is capitalised
and `App\Models\CompanyArchitectSite` expects it. A dump taken on Windows or
macOS may fold it to lowercase. Check after importing.

**Mixed table charsets are intentional** — some legacy tables are `latin1`,
some `utf8mb4`. Do not normalise without checking the effect on sorting.

See [MIGRATION.md](MIGRATION.md) for background on the legacy schema.

## Troubleshooting

| Symptom | Fix |
|---|---|
| Every page 500s after deploy | `chown -R www-data:www-data storage bootstrap/cache` |
| `Table '<db>.labour' doesn't exist` when migrating | Import the dump first, then migrate |
| `Table '<db>.sessions'` / `'.cache'` missing | Set the drivers to `file`/`file`/`sync` |
| Config or route change ignored | `php artisan optimize:clear && php artisan optimize` |
| URLs are `http://` behind TLS | Pass `HTTPS on` to PHP; check `APP_URL` |
| `.env` reachable in a browser | Document root must be `public/` |
