<?php

namespace App\Console\Commands;

use App\Models\BMaterial;
use App\Models\FinishTotal;
use App\Models\LabourInstalment;
use App\Models\LabourTotal;
use App\Models\Material;
use App\Models\TotalPayement;
use Illuminate\Console\Command;

/**
 * Finds and repairs drift in the three denormalised rollup tables.
 *
 * WHY THE DRIFT EXISTS
 * The CodeIgniter app only recalculated a rollup when a NEW entry was inserted
 * into that project+category (Construction.php:581-610, :660-687, :729-754).
 * Editing or deleting an entry left the stored total untouched, so any category
 * whose rows were later corrected or removed kept its old figure forever. A few
 * rows are orphans: their category no longer exists in `category`/`b_category`
 * at all (e.g. project 62 "Aluminum&Glass", "Fans"), so the underlying rows sum
 * to zero while the rollup still shows the original amount.
 *
 * This matters because the client dashboards read these tables directly —
 * ClientController::getPayment returns `total_payement` rows and
 * ::getFinishPayment returns `finish_total` rows — so the drift is visible to
 * customers as inflated category totals.
 *
 * The Laravel port fixes the cause: RollupService is now invoked on update and
 * delete as well as insert. This command fixes the accumulated history.
 *
 * Defaults to a dry run. Pass --apply to write.
 */
class RepairRollups extends Command
{
    protected $signature = 'rollups:repair {--apply : Write the corrected totals (otherwise dry-run)}';

    protected $description = 'Report (and optionally fix) drift between the rollup tables and their source rows';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply
            ? 'APPLYING corrections to total_payement, finish_total, labour_total'
            : 'DRY RUN — no changes will be written. Re-run with --apply to fix.');
        $this->newLine();

        $drift = 0;
        $rows = [];

        // --- civil ------------------------------------------------------------
        foreach (TotalPayement::all() as $t) {
            $price = (float) Material::where('project_id', $t->project_id)->where('type', $t->name)->sum('price');
            $qty = (float) Material::where('project_id', $t->project_id)->where('type', $t->name)->sum('quantity');

            if ((float) $t->price !== $price || (float) $t->quantity !== $qty) {
                $drift++;
                $rows[] = ['total_payement', $t->project_id, $t->name,
                    $t->price, $price, $price - (float) $t->price,
                    $t->quantity, $qty, $qty - (float) $t->quantity];

                if ($apply) {
                    $t->update(['price' => $this->tidy($price), 'quantity' => $this->tidy($qty)]);
                }
            }
        }

        // --- finishing --------------------------------------------------------
        foreach (FinishTotal::all() as $f) {
            $price = (float) BMaterial::where('project_id', $f->project_id)->where('type', $f->name)->sum('price');
            $qty = (float) BMaterial::where('project_id', $f->project_id)->where('type', $f->name)->sum('quantity');

            if ((float) $f->price !== $price || (float) $f->quantity !== $qty) {
                $drift++;
                $rows[] = ['finish_total', $f->project_id, $f->name,
                    $f->price, $price, $price - (float) $f->price,
                    $f->quantity, $qty, $qty - (float) $f->quantity];

                if ($apply) {
                    $f->update(['price' => $this->tidy($price), 'quantity' => $this->tidy($qty)]);
                }
            }
        }

        // --- labour -----------------------------------------------------------
        foreach (LabourTotal::all() as $l) {
            $sum = (float) LabourInstalment::where('project_id', $l->project_id)->where('type', $l->labour)->sum('instalmet');

            if ((float) $l->instalment !== $sum) {
                $drift++;
                // labour_total has no quantity column
                $rows[] = ['labour_total', $l->project_id, $l->labour,
                    $l->instalment, $sum, $sum - (float) $l->instalment,
                    null, null, 0.0];

                if ($apply) {
                    $l->update(['instalment' => $this->tidy($sum)]);
                }
            }
        }

        if ($rows === []) {
            $this->info('No drift found — every rollup matches its source rows.');

            return self::SUCCESS;
        }

        usort($rows, fn ($a, $b) => abs($b[5]) <=> abs($a[5]));

        $moneyDrift = count(array_filter($rows, fn ($r) => abs($r[5]) > 0.001));

        $this->table(
            ['table', 'project', 'category', 'stored', 'recomputed', 'money change', 'qty change'],
            array_map(fn ($r) => [
                $r[0], $r[1], $r[2],
                number_format((float) $r[3]),
                number_format($r[4]),
                abs($r[5]) < 0.001 ? '—' : ($r[5] > 0 ? '+' : '').number_format($r[5]),
                abs($r[8]) < 0.001 ? '—' : ($r[8] > 0 ? '+' : '').number_format($r[8]),
            ], $rows)
        );

        $this->newLine();
        $this->warn(sprintf(
            '%d rollup row(s) drifted — %d of them on the money column, %d on quantity only.',
            $drift, $moneyDrift, $drift - $moneyDrift
        ));

        if (! $apply) {
            $this->line('Re-run with --apply to correct them. Take a mysqldump first.');
        } else {
            $this->info('Corrections written.');
        }

        return self::SUCCESS;
    }

    /** Keep whole numbers whole — these columns are a mix of INT and VARCHAR. */
    protected function tidy(float $value): int|float
    {
        return $value == (int) $value ? (int) $value : $value;
    }
}
