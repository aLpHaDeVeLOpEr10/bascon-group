<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Legacy table `admin` — the single back-office login, unrelated to `users`.
 *
 * The column is `Name` (capital N) and passwords were stored in PLAINTEXT:
 * User_model::get_admin compared them with a raw WHERE clause. They are
 * upgraded to bcrypt on first successful login by LegacyHashUserProvider.
 *
 * The table also has a `username` column, but the login form matched on
 * `email` (Admin.php:44 posts 'username' into User_model::get_admin, which
 * queries WHERE email = ?). That behaviour is preserved.
 */
class Admin extends Authenticatable
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['Name', 'username', 'email', 'password', 'avatar'];

    protected $hidden = ['password'];

    /** Admins publish their picture directly — no pending column. */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    /** The legacy column is `Name`; expose it under the usual lowercase name. */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['Name'] ?? null;
    }

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
