<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfills the additive `date_n DATE` columns from the legacy VARCHAR `date`.
 *
 * WHY TWO FORMATS EXIST
 * The construction screens set an explicit jQuery UI format —
 * site_settings.php:1125-1140 all use dateFormat 'dd/mm/yy' — while every admin
 * picker (expense.php:209,214, construction_site.php:360-366,
 * architect_site.php:256, show_expense.php:264,268) is a bare .datepicker()
 * and therefore uses jQuery UI's default 'mm/dd/yy'. So the module that wrote a
 * row determines its format.
 *
 * That prediction is confirmed by the data: counting rows where a component
 * exceeds 12 (and must therefore be the day) gives a clean per-table split.
 * Three tables contain both formats because the 'dd/mm/yy' line was added
 * partway through their life — and in each the changeover is clean by id, with
 * no overlap, so every row can be converted deterministically:
 *
 *     material           mm/dd for id <= 226, dd/mm from id 237
 *     labour_instalment  mm/dd for id <= 66,  dd/mm from id 73
 *     misc               mm/dd for id <= 35,  dd/mm from id 39
 *
 * Dry run by default. Pass --apply to write.
 */
class NormalizeDates extends Command
{
    protected $signature = 'dates:normalize {--apply : Write date_n values (otherwise dry-run)}';

    protected $description = 'Populate the date_n DATE columns from the legacy VARCHAR date strings';

    /**
     * table => ['pk' => id column, 'rule' => 'dmy'|'mdy'|['mdy_max_id' => N]]
     */
    private const TABLES = [
        'b_material' => 'dmy',
        'return_payment' => 'dmy',

        'expense' => 'mdy',
        'payments_recieved' => 'mdy',
        'architect_detail' => 'mdy',
        'architect_detail_company' => 'mdy',
        'construction_detail' => 'mdy',
        'misc_admin' => 'mdy',

        // switched format mid-life; boundary verified against the data
        'material' => ['mdy_max_id' => 226],
        'labour_instalment' => ['mdy_max_id' => 66],
        'misc' => ['mdy_max_id' => 35],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply ? 'APPLYING date_n values' : 'DRY RUN — nothing will be written.');
        $this->newLine();

        $summary = [];
        $grandUnparsed = 0;

        foreach (self::TABLES as $table => $rule) {
            if (! DB::getSchemaBuilder()->hasColumn($table, 'date_n')) {
                $this->error("$table has no date_n column — run the migration first.");

                return self::FAILURE;
            }

            $converted = $unparsed = 0;
            $samples = [];

            DB::table($table)->orderBy('id')->chunkById(500, function ($rows) use (
                $table, $rule, $apply, &$converted, &$unparsed, &$samples
            ) {
                foreach ($rows as $row) {
                    $format = $this->formatFor($rule, (int) $row->id);
                    $parsed = $this->parse((string) $row->date, $format);

                    if ($parsed === null) {
                        $unparsed++;
                        if (count($samples) < 3 && trim((string) $row->date) !== '') {
                            $samples[] = "id={$row->id} '{$row->date}'";
                        }
                        continue;
                    }

                    $converted++;

                    if ($apply) {
                        DB::table($table)->where('id', $row->id)->update(['date_n' => $parsed]);
                    }
                }
            });

            $total = DB::table($table)->count();
            $grandUnparsed += $unparsed;

            $summary[] = [
                $table,
                is_array($rule) ? 'mixed (id<='.$rule['mdy_max_id'].' = m/d)' : ($rule === 'dmy' ? 'd/m/Y' : 'm/d/Y'),
                $total,
                $converted,
                $unparsed ?: '—',
                $samples ? implode(', ', $samples) : '',
            ];
        }

        $this->table(['table', 'format', 'rows', 'converted', 'unparsed', 'examples'], $summary);

        if ($grandUnparsed > 0) {
            $this->warn("$grandUnparsed row(s) had a date that could not be parsed (usually an empty string).");
        }

        if (! $apply) {
            $this->newLine();
            $this->line('Re-run with --apply to write. Take a mysqldump first.');
        }

        return self::SUCCESS;
    }

    private function formatFor(string|array $rule, int $id): string
    {
        if (is_array($rule)) {
            return $id <= $rule['mdy_max_id'] ? 'm/d/Y' : 'd/m/Y';
        }

        return $rule === 'dmy' ? 'd/m/Y' : 'm/d/Y';
    }

    private function parse(string $value, string $format): ?string
    {
        $value = trim($value);

        if ($value === '' || $value === '0') {
            return null;
        }

        $dt = DateTimeImmutable::createFromFormat($format.'|', $value);

        if ($dt === false) {
            return null;
        }

        // createFromFormat happily rolls 31/02 over into March; reject that.
        $errors = DateTimeImmutable::getLastErrors();
        if (! empty($errors['warning_count']) || ! empty($errors['error_count'])) {
            return null;
        }

        return $dt->format('Y-m-d');
    }
}
