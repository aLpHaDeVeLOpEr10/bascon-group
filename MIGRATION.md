# Bascon Accounts — CodeIgniter 3 → Laravel 12

Background on the port: why the database and the code look the way they do.
For deployment steps see [DEPLOY.md](DEPLOY.md).

The application was ported from CodeIgniter 3 to Laravel 12 on PHP 8.2,
**running against the same database as the original**. Both applications can
serve it simultaneously, which is what made parity testing and a rollback path
possible during the migration.

That decision shapes everything below. The schema was inherited, not designed.

---

## Schema and migrations

The legacy schema — 24 tables — came from the original application's database,
not from Laravel. `database/migrations/` reflects that in two layers:

| Migration | Role |
|---|---|
| `create_legacy_schema` | Creates the 24 inherited tables |
| the six that follow | Add columns and indexes to those tables |

**The six additive migrations all guard on `Schema::hasTable()`** and skip
silently when their table is absent. This is deliberate — it lets them run
harmlessly against a database populated from a dump — but it has a consequence
worth internalising:

> Running the additive migrations against an empty database reports `DONE` for
> each one while doing nothing at all.

`create_legacy_schema` was added later, precisely because that failure mode was
confusing: before it existed, `migrate` on an empty database built nothing, then
died seeding `labour_category` from a `labour` table nobody had created. **When
restoring from a dump, import first and migrate second.**

`create_legacy_schema` uses raw `CREATE TABLE` statements rather than Blueprint
calls. The Blueprint API cannot express this schema faithfully: the charset is
per-table (some tables `latin1`, some `utf8mb4`, which changes how their strings
sort), and column naming is inconsistent by inheritance — `admin`.`Name` is the
one capitalised column and has to survive verbatim. Integer display widths from
the legacy schema (`int(22)`, `int(200)`) are deliberately dropped: width never
constrained anything, MySQL 8 removed the feature, and keeping them would make a
migrated database disagree with one restored from a dump.

`Company_architect_site` keeps its capital letter. That is how it is spelled in
the live database and in `App\Models\CompanyArchitectSite`; a dump taken on a
case-insensitive filesystem folds it to lowercase, which then fails to resolve
on Linux.

### `migrate:fresh` is blocked in production

`AppServiceProvider::blockDestructiveMigrateCommands()` refuses `migrate:fresh`,
`migrate:refresh` and `migrate:reset` while `APP_ENV=production`. The migrations
rebuild the schema correctly, but **there are no seeders** — a rebuilt database
is structurally complete and entirely empty, including no account to log in
with.

---

## Authentication

Credentials carried over unchanged. The legacy formats are both still accepted:
md5 hashes in `users`, and a plaintext password in `admin`.

`AUTH_LEGACY_REHASH` controls the upgrade path. With it enabled, each password
is rehashed to bcrypt on that user's next successful login. **Keep it disabled
while the original CodeIgniter application is still running against the same
database** — it compares md5/plaintext with a raw SQL `WHERE`, so a bcrypt hash
locks that user out of the old system. Enable it at cutover and the weak hashes
convert themselves as people sign in.

One legacy account cannot be logged into at all: its `for_admin` plaintext
column disagrees with its own md5 hash, so the real password is not recoverable
from the data. That predates this migration.

---

## What changed behaviourally

Everything not listed here is a faithful port. The swapped A/B category naming,
the `update_rerturn` / `get_site_comapny` / `get_funish_payment` typos, and the
inconsistent status filtering on `show_bricks` / `show_labour` are all preserved
deliberately, with inline comments explaining why.

1. **The admin area now requires authentication.** `Admin_setting::__construct`
   rendered the login view but never called `exit`, so all 80 endpoints ran for
   anonymous callers — `/admin_setting/get_users` returned every user's md5 hash
   and plaintext password. `EnsureAdmin` middleware now stops the request.
2. **Client data is scoped to the signed-in client.** The legacy JSON endpoints
   read the project id from the URL, so any client could read any project by
   editing the address bar. The id now comes from the session; a mismatched URL
   id returns 403.
3. **CSRF on every write.** The old app sent no token anywhere.
4. **The password hash is never returned** in JSON. `users.for_admin` — the
   plaintext copy — is still returned and still written, by explicit decision:
   the admin Show Users page has a Password column bound to it, and leaving it
   unwritten would have made that column stale for every new account. What
   changed is that the endpoint serving it now requires an authenticated admin.
   Note this keeps every password recoverable by anyone with database or backup
   access.
5. **`construction/update_misc` now exists.** `site_settings.php:3172` had always
   POSTed to it, but the controller never defined it — editing a Miscellaneous
   row silently 404'd.
6. **Rollups recompute on edit and delete**, not just insert. See below.
7. **The sidebar renders for users with an empty role.** The legacy sidebar
   tested `== 'Worker'` and `== 'Client'` independently, so the busiest
   data-entry account got no navigation at all, even though the controller let
   it into the whole construction module.
8. **Assets load locally.** The views echoed a hardcoded `base_url` constant
   pointing at the production domain, so even a local checkout pulled its CSS
   and JS from the live site. Now `asset()`.

### Verification at the time of the port

A parity harness replayed every JSON endpoint against both applications and
diffed the responses: **197 checked, 196 identical**. The single difference was
`admin_setting/get_users`, which no longer returns the `password` hash
(`for_admin` is still returned — see point 4).

All pages returned 200: 17/17 admin, 5/5 construction, 8/8 client. Write paths,
rollups and the approval workflow passed 14/14 checks.

The harness itself was a scratch script and is no longer in the repository.

---

## Console commands

```bash
php artisan rollups:repair      # dry run; --apply to write
php artisan dates:normalize     # dry run; --apply to write
```

### `dates:normalize`

Legacy `date` columns are VARCHAR in two different formats, because
`site_settings.php:1125-1140` sets `dateFormat: 'dd/mm/yy'` while every admin
picker uses jQuery UI's `mm/dd/yy` default. The command writes a parsed value
into the `date_n DATE` column beside each legacy column. The original VARCHAR is
untouched and remains authoritative for the CodeIgniter application.

Three tables contain both formats, having switched when that line was added. In
each, the changeover is clean by `id`:

| table | `m/d/Y` | `d/m/Y` |
|---|---|---|
| `material` | id ≤ 226 | id ≥ 237 |
| `labour_instalment` | id ≤ 66 | id ≥ 73 |
| `misc` | id ≤ 35 | id ≥ 39 |

`payments_recieved` is `m/d/Y` for its entire life. It briefly carried a
boundary at id 293, read off a development snapshot in which the rows above it
looked hand-entered as `d/m/Y`. Production had no such rows — across all 300,
187 have a second component over 12 and so must be `m/d/Y`, and not one has a
first component over 12. The boundary only mis-read the newer payments, leaving
12 unparsed and silently recording 4 more in the wrong month. Corrected.

**This is a point-in-time backfill.** It fills only what exists when it runs, so
run it after every data import, never before. Check the dry run reports zero
unparsed rows before applying.

### `rollups:repair`

`total_payement`, `finish_total` and `labour_total` are denormalised caches. The
legacy app only refreshed one when a **new** row was inserted into that
project+category — edits and deletes never triggered a recalculation, so totals
drifted. The client dashboards read these tables directly, so the drift is
visible to customers.

Last measured against production data: **57 rows drifted, 21 of them on the
money column.** The largest was a project carrying PKR 1,900,000 against zero
underlying rows. **Not applied** — it rewrites customer-visible financial
figures and needs a human decision.

---

## Not migrated (dead code)

`Sites_sidebar.php` (598 lines of unreferenced theme demo nav),
`clienttotal_payment.php` and `admin/total_payment.php` (orphan duplicates),
`welcome_message.php` (927 lines of demo dashboard with fake charts), and the
`construction_site` table (superseded by `sites`).

`Company_architect_site` is empty in the live data and read by nothing — the
"company architect" screens all read `sites`. Its save endpoint is kept so
behaviour is identical.
