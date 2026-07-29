<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive only — nothing existing is renamed, retyped or dropped, so the
 * CodeIgniter app keeps working against the same database.
 *
 * Adds:
 *   1. `date_n DATE NULL` beside every legacy VARCHAR `date` column. The
 *      original stays authoritative for the old app; date_n becomes the
 *      sortable/queryable version for Laravel. Populated by `dates:normalize`.
 *   2. Indexes on the project foreign keys. Every listing screen filters on
 *      these and there was not a single index on the legacy schema.
 */
return new class extends Migration
{
    /** table => the FK column that needs an index (null = none) */
    private const DATE_TABLES = [
        'material' => 'project_id',
        'b_material' => 'project_id',
        'labour_instalment' => 'project_id',
        'misc' => 'proj_id',
        'return_payment' => 'proj_id',
        'expense' => null,
        'payments_recieved' => 'proj_id',
        'architect_detail' => 'proj_id',
        'architect_detail_company' => 'proj_id',
        'construction_detail' => 'proj_id',
        'misc_admin' => null,
    ];

    private const EXTRA_INDEXES = [
        'labour' => 'project_id',
        'total_payement' => 'project_id',
        'finish_total' => 'project_id',
        'labour_total' => 'project_id',
    ];

    public function up(): void
    {
        foreach (self::DATE_TABLES as $table => $fk) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $fk) {
                if (! Schema::hasColumn($table, 'date_n')) {
                    $t->date('date_n')->nullable()->after('date');
                    $t->index('date_n', "{$table}_date_n_idx");
                }

                if ($fk && ! $this->hasIndex($table, "{$table}_{$fk}_idx")) {
                    $t->index($fk, "{$table}_{$fk}_idx");
                }
            });
        }

        foreach (self::EXTRA_INDEXES as $table => $fk) {
            if (Schema::hasTable($table) && ! $this->hasIndex($table, "{$table}_{$fk}_idx")) {
                Schema::table($table, fn (Blueprint $t) => $t->index($fk, "{$table}_{$fk}_idx"));
            }
        }
    }

    public function down(): void
    {
        foreach (self::DATE_TABLES as $table => $fk) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $fk) {
                if (Schema::hasColumn($table, 'date_n')) {
                    $t->dropIndex("{$table}_date_n_idx");
                    $t->dropColumn('date_n');
                }

                if ($fk && $this->hasIndex($table, "{$table}_{$fk}_idx")) {
                    $t->dropIndex("{$table}_{$fk}_idx");
                }
            });
        }

        foreach (self::EXTRA_INDEXES as $table => $fk) {
            if (Schema::hasTable($table) && $this->hasIndex($table, "{$table}_{$fk}_idx")) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex("{$table}_{$fk}_idx"));
            }
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn ($i) => $i['name'] === $index);
    }
};
