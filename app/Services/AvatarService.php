<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Profile pictures for all three roles.
 *
 * Admins and clients publish straight away; a worker's upload waits for an
 * admin, exactly as their material and payment entries do. The difference is
 * one branch in store() rather than three separate implementations, so the
 * file handling, validation and cleanup can only behave one way.
 *
 * Files land in public/uploads/avatars rather than the storage disk: the app
 * is served from public/ by both `artisan serve` and Apache, and this avoids
 * depending on `storage:link` having been run on whichever machine is hosting.
 * The legacy app put its images under public/assets for the same reason.
 */
class AvatarService
{
    public const PENDING = 0;
    public const SETTLED = 1;
    public const REJECTED = 2;

    /** Relative to public/, so it is also the URL path. */
    public const DIR = 'uploads/avatars';

    /** What a file has to be to get anywhere near the disk. */
    public const RULES = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    /**
     * Save an upload and return the stored path, relative to public/.
     *
     * The name is random rather than the uploaded one: two people uploading
     * "photo.jpg" must not collide, and a user-supplied name is not something
     * to trust as a path.
     */
    public function put(UploadedFile $file): string
    {
        $dir = public_path(self::DIR);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move($dir, $name);

        return self::DIR.'/'.$name;
    }

    /** Remove a stored file, ignoring one that has already gone. */
    public function forget(?string $path): void
    {
        if (! $path) {
            return;
        }

        $full = public_path($path);

        if (is_file($full)) {
            @unlink($full);
        }
    }

    /**
     * Take a worker's or client's upload.
     *
     * Returns true when the picture is live immediately, false when it has
     * been filed for approval — the caller words its message from that.
     */
    public function storeForUser(User $user, UploadedFile $file): bool
    {
        $path = $this->put($file);

        // Clients publish directly, like admins. Only workers are reviewed.
        if ($user->isClient()) {
            $this->forget($user->avatar);

            $user->forceFill([
                'avatar' => $path,
                'avatar_pending' => null,
                'avatar_status' => self::SETTLED,
                'avatar_seen' => 1,
            ])->save();

            return true;
        }

        // Replacing one pending upload with another: the superseded file is of
        // no use to anyone, so it does not linger on disk.
        $this->forget($user->avatar_pending);

        $user->forceFill([
            'avatar_pending' => $path,
            'avatar_status' => self::PENDING,
            'avatar_seen' => 1,
        ])->save();

        return false;
    }

    /** Approve a worker's pending picture: it becomes the live one. */
    public function approve(User $user): bool
    {
        if (! $user->avatar_pending) {
            return false;
        }

        $this->forget($user->avatar);

        return $user->forceFill([
            'avatar' => $user->avatar_pending,
            'avatar_pending' => null,
            'avatar_status' => self::SETTLED,
            'avatar_seen' => 0,
        ])->save();
    }

    /** Reject it: the file goes, and whatever they had before stays. */
    public function reject(User $user): bool
    {
        if (! $user->avatar_pending) {
            return false;
        }

        $this->forget($user->avatar_pending);

        return $user->forceFill([
            'avatar_pending' => null,
            'avatar_status' => self::REJECTED,
            'avatar_seen' => 0,
        ])->save();
    }
}
