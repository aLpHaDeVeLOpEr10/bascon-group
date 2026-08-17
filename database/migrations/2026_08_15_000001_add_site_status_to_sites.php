<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive only — the CodeIgniter app never selects this column, so it keeps
 * working unchanged.
 *
 * A site is either still being built ('running') or finished ('closed'). The
 * construction Sites screen splits its listing on this. Every existing row
 * predates the distinction and is therefore running.
 *
 * Stored as VARCHAR rather than ENUM so a third state can be added later
 * without an ALTER on a table the legacy app also writes to.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sites') || Schema::hasColumn('sites', 'site_status')) {
            return;
        }

        Schema::table('sites', function (Blueprint $t) {
            $t->string('site_status', 20)->default('running');
            $t->index('site_status', 'sites_site_status_idx');
        });

        DB::table('sites')->whereNull('site_status')->update(['site_status' => 'running']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('sites') || ! Schema::hasColumn('sites', 'site_status')) {
            return;
        }

        Schema::table('sites', function (Blueprint $t) {
            $t->dropIndex('sites_site_status_idx');
            $t->dropColumn('site_status');
        });
    }
};
