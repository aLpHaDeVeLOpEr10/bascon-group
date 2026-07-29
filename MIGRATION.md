# Bascon Accounts — CodeIgniter 3 → Laravel 12

Port of `c:\xampp\htdocs\account.bascon` to Laravel 12.64 on PHP 8.2, running
against the **same `bascon` database**. Both apps can serve it at once, which is
what makes the parity testing and the rollback path possible.

| | URL |
|---|---|
| Laravel (this app) | http://localhost:8080 |
| CodeIgniter (original) | http://localhost/account.bascon |

Apache vhost is in `conf/extra/httpd-vhosts.conf` (backup: `.bak-20260729`).
Laravel must serve from `public/`, so it cannot sit under `htdocs/` as a
subfolder the way the old app does — hence the separate port.

## Logins

Credentials are unchanged; the legacy `users` md5 hashes and the plaintext
`admin` password are both accepted as-is.

| Role | Email | Password |
|---|---|---|
| Admin | bascongroup@gmail.com | bascongrp884 |
| Worker | ahsanbukhari@bascon.pk | ahsan@09 |
| Client (project 69) | alisalman@bascon.pk | PUEHS102 |

`adnankhalid@bascon.pk` cannot be logged in as: its `for_admin` plaintext column
disagrees with its md5 hash, so the real password is not recoverable from the
data. That predates this migration.

## Verification status

`scratchpad/parity.php` replays every JSON endpoint against both apps and diffs
the responses:

```
checked 197 | identical 196 | different 1 | unparseable 0
```

The single difference is `admin_setting/get_users`, which no longer returns
`password` or `for_admin` — the intended security fix.

Pages: 17/17 admin, 5/5 construction, 8/8 client return 200 through Apache.
Write paths, rollups and the approval workflow: 14/14 checks pass.

## What changed behaviourally

Everything else is a faithful port — URLs, the swapped A/B category naming, the
`update_rerturn` / `get_site_comapny` / `get_funish_payment` typos, and the
inconsistent status filtering on `show_bricks` / `show_labour` are all preserved
deliberately, with inline comments explaining why.

1. **Admin area now requires authentication.** `Admin_setting::__construct`
   rendered the login view but never called `exit`, so all 80 endpoints ran for
   anonymous callers — `/admin_setting/get_users` returned every user's md5 hash
   and plaintext password. Now `EnsureAdmin` middleware stops the request.
2. **Client data is scoped to the signed-in client.** The legacy JSON endpoints
   read the project id from the URL, so any client could read any project by
   editing the address bar. The id now comes from the session; a mismatched URL
   id returns 403.
3. **CSRF on every write.** The old app sent no token anywhere.
4. **Passwords are never returned** in JSON, and `users.for_admin` (the
   plaintext copy) is never read or written.
5. **`construction/update_misc` now exists.** `site_settings.php:3172` has always
   POSTed to it, but the controller never defined it — editing a Miscellaneous
   row silently 404'd.
6. **Rollups recompute on edit and delete**, not just insert. See below.
7. **The sidebar renders for users with an empty role.** The legacy sidebar
   tested `== 'Worker'` and `== 'Client'` independently, so user 28 (the busiest
   data-entry account) got no navigation at all, even though the controller let
   them into the whole construction module.
8. **Assets load locally.** The views echoed a hardcoded `base_url` constant
   pointing at `https://accounts.bascongroup.pk/`, so even a local checkout
   pulled its CSS/JS from the live site. Now `asset()`.

## Console commands

```bash
php artisan rollups:repair      # dry run; --apply to write
php artisan dates:normalize     # dry run; --apply to write (already applied)
```

### `rollups:repair`

`total_payement`, `finish_total` and `labour_total` are denormalised caches. The
legacy app only refreshed one when a **new** row was inserted into that
project+category — edits and deletes never triggered a recalculation, so totals
drifted. The client dashboards read these tables directly, so the drift is
visible to customers.

Current state of the production data: **57 rows drifted, 21 on the money
column.** The largest is project 62 "Aluminum&Glass" at PKR 1,900,000 stored
against zero underlying rows. **Not yet applied** — it rewrites financial
figures and needs a human decision.

### `dates:normalize` (applied)

Legacy `date` columns are VARCHAR in two different formats, because
`site_settings.php:1125-1140` sets `dateFormat: 'dd/mm/yy'` while every admin
picker uses jQuery UI's `mm/dd/yy` default. Three tables contain both, having
switched when that line was added; in each the changeover is clean by `id`:

| table | `m/d/Y` | `d/m/Y` |
|---|---|---|
| `material` | id ≤ 226 | id ≥ 237 |
| `labour_instalment` | id ≤ 66 | id ≥ 73 |
| `misc` | id ≤ 35 | id ≥ 39 |

The migration added a `date_n DATE` column beside each legacy column (plus
indexes on the project foreign keys, of which the legacy schema had none). All
**4,070 rows converted, zero failures**. The original VARCHAR is untouched and
remains authoritative for the CodeIgniter app.

## Cutover

`AUTH_LEGACY_REHASH=false` in `.env`. While the old app is still live, leave it
off: upgrading a password to bcrypt would lock that person out of the
CodeIgniter app, which compares md5/plaintext with a raw SQL `WHERE`. Set it to
`true` at cutover and the weak hashes convert themselves as people sign in.

Pre-migration snapshot: `storage/app/backups/bascon-before-migration-20260729.sql`

## Not migrated (dead code)

`Sites_sidebar.php` (598 lines of unreferenced theme demo nav),
`clienttotal_payment.php` and `admin/total_payment.php` (orphan duplicates),
`welcome_message.php` (927 lines of Neon demo dashboard with fake charts), and
the `construction_site` table (superseded by `sites`).

`Company_architect_site` is empty in production and read by nothing — the
"company architect" screens all read `sites`. Its save endpoint is kept so
behaviour is identical.
