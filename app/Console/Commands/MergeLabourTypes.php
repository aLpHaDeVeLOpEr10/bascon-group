<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Collapses labour types that are the same thing spelled differently.
 *
 * A labour type is not a row anywhere that other rows point at — it is the
 * `type` STRING on a `labour` row (a type assigned to a project) and on every
 * `labour_instalment` recorded against it, plus a name in the `labour_category`
 * catalogue. Nothing is keyed to it. So two spellings of one trade are two
 * types as far as every screen is concerned: they get separate rows in the
 * picker, separate totals, and separate tables on the site page.
 *
 * That is how 'Tile fixing', 'Tile-Fixer' and 'Tile-Fixing' came to be three
 * trades, and 'Contracter ' a fourth alongside 'Contractor'.
 *
 * WHAT COUNTS AS THE SAME
 * Two names match when they are equal once case, spaces and the separators
 * people vary between them (- and _) are taken out: 'Polish-Work',
 * 'polish work' and 'PolishWork' are one trade. Nothing else is guessed at —
 * 'Ceilling' and 'False-Celling' survive as two, which they should, and
 * 'Tile-Fixer' only merges with 'Tile-Fixing' when --loose is passed, because
 * the two differ by more than punctuation.
 *
 * WHICH SPELLING WINS
 * The one without separators, then the one with the most rows behind it, then
 * alphabetical order. Passing --prefer overrides that for one group.
 *
 * NOTHING IS DELETED. Every labour and labour_instalment row survives with its
 * dates, amounts and project intact; only the name on it changes. The single
 * exception is the row that a rename can duplicate — where one project ends up
 * holding the same type twice — and there the two are combined into one, their
 * contract totals added, which the run reports line by line.
 *
 * Dry run by default. Pass --apply to write.
 */
class MergeLabourTypes extends Command
{
    protected $signature = 'labour:merge
        {--apply : Write the changes (otherwise dry-run)}
        {--loose : Also group names where one is a prefix of the other, e.g. Tile-Fixer with Tile-Fixing}
        {--prefer=* : Force the winning spelling, e.g. --prefer="Polish Work"}
        {--merge=* : Merge a name that no rule would catch, e.g. --merge="Contracter=Contractor"}';

    protected $description = 'Merge duplicate labour type spellings, keeping every row';

    /** Every place a labour type name is stored. */
    private const COLUMNS = [
        'labour' => 'type',
        'labour_instalment' => 'type',
        'labour_category' => 'type',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply ? 'APPLYING labour type merges' : 'DRY RUN — nothing will be written.');
        $this->newLine();

        $groups = $this->groups();

        if (! $groups) {
            $this->info('No duplicate labour types found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($groups as $canonical => $names) {
            foreach ($names as $name) {
                $rows[] = [
                    $name === $canonical ? "<info>$canonical</info>" : $name,
                    $name === $canonical ? 'keep' : "-> $canonical",
                    $this->count('labour', $name),
                    $this->count('labour_instalment', $name),
                ];
            }

            $rows[] = new \Symfony\Component\Console\Helper\TableSeparator();
        }

        array_pop($rows);

        $this->table(['type', 'action', 'labour rows', 'instalments'], $rows);
        $this->newLine();

        if (! $apply) {
            $this->comment('Re-run with --apply to write these changes.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($groups) {
            foreach ($groups as $canonical => $names) {
                $stale = array_values(array_filter($names, fn ($n) => $n !== $canonical));

                foreach (self::COLUMNS as $table => $column) {
                    $renamed = DB::table($table)->whereIn($column, $stale)->update([$column => $canonical]);

                    if ($renamed) {
                        $this->line("  $table: renamed $renamed to '$canonical'");
                    }
                }

                $this->combineDuplicateAssignments($canonical);
                $this->dedupeCatalogue($canonical);
            }
        });

        $this->newLine();
        $this->info('Done. Remaining types:');

        foreach ($this->distinctNames() as $name) {
            $this->line(sprintf("  %-26s %d labour, %d instalments",
                $name, $this->count('labour', $name), $this->count('labour_instalment', $name)));
        }

        return self::SUCCESS;
    }

    /**
     * Duplicate spellings, keyed by the one that wins.
     *
     * @return array<string, list<string>>
     */
    private function groups(): array
    {
        $names = $this->distinctNames();
        $buckets = [];

        foreach ($names as $name) {
            $buckets[$this->key($name)][] = $name;
        }

        // Pairs named on the command line are folded in first, and win over
        // whatever the automatic rules would have said. This is the escape
        // hatch for the ones no rule can safely catch: 'Contracter' and
        // 'Contractor' differ by a letter, not by punctuation, and a rule
        // loose enough to pair them would pair plenty that should not be.
        foreach ((array) $this->option('merge') as $pair) {
            [$from, $to] = array_pad(explode('=', $pair, 2), 2, null);

            if ($to === null || trim($from) === '' || trim($to) === '') {
                $this->warn("Ignoring --merge=\"$pair\": expected the form from=to.");

                continue;
            }

            $fromKey = $this->key($from);
            $toKey = $this->key($to);

            if (! isset($buckets[$fromKey]) || ! isset($buckets[$toKey])) {
                $this->warn("Ignoring --merge=\"$pair\": no rows carry one of those names.");

                continue;
            }

            if ($fromKey === $toKey) {
                continue;
            }

            $buckets[$toKey] = array_merge($buckets[$toKey], $buckets[$fromKey]);
            unset($buckets[$fromKey]);

            // The named target is the winner, whatever the ranking prefers.
            $this->forced[$toKey] = trim($to);
        }

        if ($this->option('loose')) {
            $buckets = $this->mergePrefixes($buckets);
        }

        $groups = [];

        foreach ($buckets as $bucket) {
            if (count($bucket) < 2) {
                continue;
            }

            $groups[$this->forced[$this->key($bucket[0])] ?? $this->canonical($bucket)] = $bucket;
        }

        return $groups;
    }

    /** Winners fixed by --merge, keyed the same way the buckets are. */
    private array $forced = [];

    /** The comparison key: case, spaces and - _ separators removed. */
    private function key(string $name): string
    {
        return mb_strtolower(preg_replace('/[\s_-]+/u', '', trim($name)));
    }

    /**
     * --loose: fold a bucket into another whose key it starts with.
     *
     * This is what pairs 'tilefixer' with 'tilefixing'. It is off by default
     * because the same rule would also swallow a genuinely narrower trade
     * whose name happens to extend a broader one.
     */
    private function mergePrefixes(array $buckets): array
    {
        $keys = array_keys($buckets);
        sort($keys);

        foreach ($keys as $key) {
            if (! isset($buckets[$key])) {
                continue;
            }

            foreach ($keys as $other) {
                if ($other === $key || ! isset($buckets[$other]) || ! isset($buckets[$key])) {
                    continue;
                }

                // A shared stem of at least five characters, so 'Painter' does
                // not reel in every other name beginning with 'Pa'.
                $stem = min(mb_strlen($key), mb_strlen($other));

                if ($stem >= 5 && str_starts_with($other, mb_substr($key, 0, $stem))) {
                    $buckets[$key] = array_merge($buckets[$key], $buckets[$other]);
                    unset($buckets[$other]);
                }
            }
        }

        return $buckets;
    }

    /** The spelling this group collapses to. */
    private function canonical(array $names): string
    {
        foreach ((array) $this->option('prefer') as $preferred) {
            foreach ($names as $name) {
                if ($this->key($name) === $this->key($preferred)) {
                    return $preferred;
                }
            }
        }

        $ranked = $names;

        usort($ranked, function (string $a, string $b) {
            // Trimmed beats untrimmed; no separators beats separators; then
            // whichever carries more rows; then alphabetical, so a run on
            // unchanged data always picks the same winner.
            return [$a !== trim($a), substr_count($a, '-') + substr_count($a, '_'), -$this->count('labour', $a), $a]
               <=> [$b !== trim($b), substr_count($b, '-') + substr_count($b, '_'), -$this->count('labour', $b), $b];
        });

        return trim($ranked[0]);
    }

    /**
     * One project, the same type twice — the only way a rename can leave two
     * rows where the app expects one. Their contract totals are added and the
     * spare row goes; the instalments already key on (project, type) and so
     * are pointing at the survivor either way.
     */
    private function combineDuplicateAssignments(string $type): void
    {
        $projects = DB::table('labour')
            ->select('project_id')
            ->where('type', $type)
            ->groupBy('project_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('project_id');

        foreach ($projects as $projectId) {
            $rows = DB::table('labour')
                ->where('type', $type)->where('project_id', $projectId)
                ->orderBy('id')
                ->get();

            $keep = $rows->shift();
            $total = (float) ($keep->total ?: 0);

            foreach ($rows as $row) {
                $total += (float) ($row->total ?: 0);
                DB::table('labour')->where('id', $row->id)->delete();
            }

            DB::table('labour')->where('id', $keep->id)->update(['total' => $total]);

            $this->line(sprintf("  combined %d '%s' rows on project %d into id %d (total %s)",
                $rows->count() + 1, $type, $projectId, $keep->id, number_format($total)));
        }
    }

    /** The catalogue is a plain name list, so a repeat is simply dropped. */
    private function dedupeCatalogue(string $type): void
    {
        $ids = DB::table('labour_category')->where('type', $type)->orderBy('id')->pluck('id');

        if ($ids->count() > 1) {
            DB::table('labour_category')->whereIn('id', $ids->slice(1))->delete();
            $this->line("  catalogue: dropped ".($ids->count() - 1)." repeat of '$type'");
        }
    }

    /** @return list<string> */
    private function distinctNames(): array
    {
        $names = collect();

        foreach (self::COLUMNS as $table => $column) {
            $names = $names->merge(DB::table($table)->distinct()->pluck($column));
        }

        return $names
            ->filter(fn ($name) => trim((string) $name) !== '')
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function count(string $table, string $name): int
    {
        return DB::table($table)->where(self::COLUMNS[$table], $name)->count();
    }
}
