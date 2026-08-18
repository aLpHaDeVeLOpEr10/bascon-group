<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a worker be told what happened to a payment they filed.
 *
 * decision_seen: 1 = there is nothing to tell, 0 = an admin has approved or
 * rejected this and the worker has not looked yet. Defaults to 1 so the 284
 * payments that predate the workflow raise no notifications, and so a row the
 * CodeIgniter app inserts never does either.
 *
 * A flag on the payment rather than a notifications table: there is exactly one
 * kind of notice here, it belongs to the row it is about, and a separate table
 * would need its own lifecycle for no benefit yet. If a second kind of notice
 * appears, that is the point to promote this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments_recieved') || Schema::hasColumn('payments_recieved', 'decision_seen')) {
            return;
        }

        Schema::table('payments_recieved', function (Blueprint $t) {
            $t->tinyInteger('decision_seen')->default(1);
        });

        DB::table('payments_recieved')->update(['decision_seen' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasTable('payments_recieved') && Schema::hasColumn('payments_recieved', 'decision_seen')) {
            Schema::table('payments_recieved', function (Blueprint $t) {
                $t->dropColumn('decision_seen');
            });
        }
    }
};
