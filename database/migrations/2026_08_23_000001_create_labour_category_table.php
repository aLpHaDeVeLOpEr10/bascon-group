<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A catalogue of labour type names.
 *
 * Civil and finishing materials have had one since the legacy app — `category`
 * and `b_category`, each a bare (id, name) list that their Category page adds
 * to and deletes from. Labour never did: a type existed only as the `type`
 * string on a `labour` row, which is a type *assigned to a project*. So there
 * was nowhere to record a new type before assigning it, and nothing to remove
 * when one fell out of use. This is the missing third table, shaped like its
 * two siblings.
 *
 * The `labour` rows are untouched and still carry the name as a string, exactly
 * as `material` does against `category`. Nothing is keyed to this table; it is
 * the list of names on offer, not a foreign key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 200);
        });

        // Seeded from the names already in use, so the picker opens on exactly
        // what it showed before this table existed.
        $existing = DB::table('labour')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        foreach ($existing as $type) {
            DB::table('labour_category')->insert(['type' => $type]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_category');
    }
};
