<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payments gain the same approval workflow civil and finishing materials have.
 *
 * Additive only, so the CodeIgniter app keeps working: it never selects either
 * column, and the defaults mean rows it inserts behave as instantly live.
 *
 *   payments_recieved.status  0 = awaiting an admin, 1 = live, 2 = rejected.
 *     Mirrors material.status exactly. Existing rows predate the workflow and
 *     are therefore live.
 *
 *   setting.payment_status    0 = instant, 1 = approval required.
 *     Mirrors civil_status / finish_status, including the inversion — the
 *     setting says "does this need approval", the row status says "is it
 *     approved". Defaults to instant, so nothing changes until an admin turns
 *     it on.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments_recieved') && ! Schema::hasColumn('payments_recieved', 'status')) {
            Schema::table('payments_recieved', function (Blueprint $t) {
                $t->tinyInteger('status')->default(1);
                $t->index('status', 'payments_recieved_status_idx');
            });

            DB::table('payments_recieved')->update(['status' => 1]);
        }

        if (Schema::hasTable('setting') && ! Schema::hasColumn('setting', 'payment_status')) {
            Schema::table('setting', function (Blueprint $t) {
                $t->integer('payment_status')->default(0);
            });

            DB::table('setting')->update(['payment_status' => 0]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments_recieved') && Schema::hasColumn('payments_recieved', 'status')) {
            Schema::table('payments_recieved', function (Blueprint $t) {
                $t->dropIndex('payments_recieved_status_idx');
                $t->dropColumn('status');
            });
        }

        if (Schema::hasTable('setting') && Schema::hasColumn('setting', 'payment_status')) {
            Schema::table('setting', function (Blueprint $t) {
                $t->dropColumn('payment_status');
            });
        }
    }
};
