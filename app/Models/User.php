<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Legacy table `users` — workers and clients (the admin login lives in `admin`).
 *
 * Roles seen in production data: 'Worker', 'Client', and one row with an empty
 * role (user 28). The CodeIgniter app only ever tested for the exact strings
 * 'Worker' (Client.php:22) and 'Client' (Construction.php:24), so an empty role
 * behaved as a worker. isClient()/isWorker() preserve exactly that.
 *
 * `password` holds an md5 hash in legacy rows; it is upgraded to bcrypt on the
 * first successful login by LegacyHashUserProvider.
 *
 * `for_admin` is a plaintext copy of the password. The admin "Show Users" page
 * shows it in its Password column, so it is deliberately kept readable and kept
 * current on create/update (AdminSettingController::saveUser and ::updateUser).
 * Retained by explicit decision: it does mean every account's password is
 * recoverable by anyone with database or backup access. What changed from the
 * CodeIgniter version is that the endpoint serving it now requires an
 * authenticated admin — previously it was open to anonymous callers.
 *
 * The `password` hash stays hidden: nothing in the UI reads it, so there is no
 * reason to ship it to the browser.
 *
 * No 'hashed' cast is used on `password`: the legacy upgrade path needs to
 * write the hash explicitly and must never double-hash an existing value.
 */
class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name', 'username', 'email', 'address', 'password', 'for_admin',
        'contact', 'role', 'project_id',
    ];

    protected $hidden = ['password'];

    public function isClient(): bool
    {
        return $this->role === 'Client';
    }

    public function isWorker(): bool
    {
        return ! $this->isClient();
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'project_id');
    }

    // The legacy table has no remember_token column and the old app had no
    // "remember me". Disable it rather than altering the shared schema.
    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // no-op
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
