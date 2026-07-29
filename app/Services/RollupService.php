<?php

namespace App\Services;

use App\Models\BMaterial;
use App\Models\FinishTotal;
use App\Models\LabourInstalment;
use App\Models\LabourTotal;
use App\Models\Material;
use App\Models\TotalPayement;

/**
 * Maintains the three denormalised per-category rollup tables.
 *
 * The CodeIgniter app recalculated these inline in three near-identical blocks
 * (Construction.php:581-610, :660-687, :729-754). They are unified here.
 *
 * Behaviour deliberately preserved from the original:
 *   - The rollups sum EVERY row for the project/category, including entries
 *     still pending approval (status 0) and rejected ones (status 2). The old
 *     code applied no status filter when recomputing, and the listing screens
 *     read these totals directly, so filtering here would silently change
 *     numbers the client already sees.
 *   - A row is inserted when the (project, category) pair is new, otherwise
 *     updated in place.
 *
 * One original bug is NOT reproduced: Construction.php:584 looked up
 * finish/civil rows with a stray tab in the column name ("project_id\t"),
 * which MySQL tolerates but which made the SELECT match on name alone. That
 * could bind a total to the wrong project when two projects shared a category
 * name. The lookup here uses the correct column.
 */
class RollupService
{
    /** Recompute `total_payement` for one project + civil material category. */
    public function civil(int|string $projectId, string $category): void
    {
        $rows = Material::query()
            ->where('project_id', $projectId)
            ->where('type', $category)
            ->get();

        TotalPayement::updateOrCreate(
            ['project_id' => $projectId, 'name' => $category],
            [
                'price' => $this->sum($rows, 'price'),
                'quantity' => $this->sum($rows, 'quantity'),
            ],
        );
    }

    /** Recompute `finish_total` for one project + finishing material category. */
    public function finishing(int|string $projectId, string $category): void
    {
        $rows = BMaterial::query()
            ->where('project_id', $projectId)
            ->where('type', $category)
            ->get();

        FinishTotal::updateOrCreate(
            ['project_id' => $projectId, 'name' => $category],
            [
                'price' => $this->sum($rows, 'price'),
                'quantity' => $this->sum($rows, 'quantity'),
            ],
        );
    }

    /** Recompute `labour_total` for one project + labour type. */
    public function labour(int|string $projectId, string $category): void
    {
        $total = LabourInstalment::query()
            ->where('project_id', $projectId)
            ->where('type', $category)
            ->get();

        LabourTotal::updateOrCreate(
            ['project_id' => $projectId, 'labour' => $category],
            ['instalment' => $this->sum($total, 'instalmet')],
        );
    }

    /**
     * Money and quantity columns are a mix of INT and VARCHAR across tables
     * (material.price is INT, b_material.price is VARCHAR), and some rows hold
     * an empty string. Casting per value matches PHP's loose "+=" in the
     * original loops.
     */
    protected function sum(iterable $rows, string $column): float|int
    {
        $total = 0;

        foreach ($rows as $row) {
            $total += (float) ($row->{$column} ?: 0);
        }

        return $total == (int) $total ? (int) $total : $total;
    }
}
