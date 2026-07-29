<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Hash;

/**
 * Authenticates against the legacy password formats and transparently upgrades
 * them to bcrypt on first successful login.
 *
 * Two legacy formats exist in the imported production data:
 *   - `users.password` — md5 (32 hex chars), written by Admin_setting::save_user
 *   - `admin.password` — PLAINTEXT, compared with a raw SQL WHERE by
 *     User_model::get_admin
 *
 * Once a user logs in successfully, the stored value is replaced with a bcrypt
 * hash, so the weak formats disappear from the table as people sign in. No one
 * is locked out and nobody has to be told anything.
 *
 * Set `legacy_plaintext` => true on the guard's provider config for the admin
 * table; it defaults to md5 for `users`.
 */
class LegacyHashUserProvider extends EloquentUserProvider
{
    protected bool $legacyPlaintext = false;

    public function setLegacyPlaintext(bool $plaintext): static
    {
        $this->legacyPlaintext = $plaintext;

        return $this;
    }

    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? null;

        if (! is_string($plain) || $plain === '') {
            return false;
        }

        $stored = (string) $user->getAuthPassword();

        if ($stored === '') {
            return false;
        }

        // Already migrated to bcrypt/argon — normal path.
        if ($this->looksHashed($stored)) {
            return $this->hasher->check($plain, $stored);
        }

        return $this->legacyMatches($plain, $stored);
    }

    /**
     * Called by the guard after a successful login. Upgrading here (rather than
     * inside validateCredentials) keeps the write out of the comparison path.
     */
    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false): void
    {
        // While the CodeIgniter app is still serving the same database,
        // upgrading a password to bcrypt would lock that person OUT of the old
        // app, which compares md5/plaintext with a raw SQL WHERE. That would
        // break both the side-by-side parity testing and the rollback path.
        // Leave this off until cutover, then set AUTH_LEGACY_REHASH=true and
        // the weak hashes convert themselves as people sign in.
        if (! config('auth.legacy_rehash', false)) {
            return;
        }

        $plain = $credentials['password'] ?? null;

        if (! is_string($plain) || $plain === '') {
            return;
        }

        $stored = (string) $user->getAuthPassword();

        if (! $force && $this->looksHashed($stored) && ! $this->hasher->needsRehash($stored)) {
            return;
        }

        // Only upgrade once we know the supplied password really is the right one.
        if (! $this->looksHashed($stored) && ! $this->legacyMatches($plain, $stored)) {
            return;
        }

        $user->forceFill([
            $user->getAuthPasswordName() => $this->hasher->make($plain),
        ])->save();
    }

    protected function legacyMatches(string $plain, string $stored): bool
    {
        if ($this->legacyPlaintext) {
            return hash_equals($stored, $plain);
        }

        return hash_equals(strtolower($stored), md5($plain));
    }

    /** True when the value is a modern crypt hash rather than md5/plaintext. */
    protected function looksHashed(string $value): bool
    {
        return (bool) preg_match('/^\$(2[axyb]|argon2i|argon2id)\$/', $value);
    }
}
