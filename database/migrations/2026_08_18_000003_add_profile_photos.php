<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profile pictures, with the same approval workflow payments use.
 *
 * Additive only — the CodeIgniter app never selects these.
 *
 *   users.avatar          the picture currently shown. Null = fall back to
 *                         initials, which is what every account starts as.
 *   users.avatar_pending  a worker's upload waiting on an admin. Kept apart
 *                         from `avatar` so the live picture stays up while the
 *                         new one is judged, and a rejection costs nothing.
 *   users.avatar_status   0 pending, 1 nothing outstanding, 2 rejected —
 *                         the same three values material.status uses.
 *   users.avatar_seen     0 when a decision is waiting to be told to the
 *                         worker. Mirrors payments_recieved.decision_seen.
 *
 *   admin.avatar          admins publish straight away, so there is no pending
 *                         column here.
 *
 * Clients live in `users` too but upload directly, so their rows simply never
 * carry a pending value.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('avatar', 255)->nullable();
                $t->string('avatar_pending', 255)->nullable();
                $t->tinyInteger('avatar_status')->default(1);
                $t->tinyInteger('avatar_seen')->default(1);
                $t->index('avatar_status', 'users_avatar_status_idx');
            });
        }

        if (Schema::hasTable('admin') && ! Schema::hasColumn('admin', 'avatar')) {
            Schema::table('admin', function (Blueprint $t) {
                $t->string('avatar', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropIndex('users_avatar_status_idx');
                $t->dropColumn(['avatar', 'avatar_pending', 'avatar_status', 'avatar_seen']);
            });
        }

        if (Schema::hasTable('admin') && Schema::hasColumn('admin', 'avatar')) {
            Schema::table('admin', function (Blueprint $t) {
                $t->dropColumn('avatar');
            });
        }
    }
};
