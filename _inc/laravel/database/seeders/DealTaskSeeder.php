<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Models\DealTask;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DealTaskSeeder extends Seeder
{
    private const MAX_TASKS_PER_DEAL_DISTRIBUTION = 16;

    private const UNIQUE_NAME_MAX_ATTEMPTS = 12;
    private const BUILD_PLAN_MAX_ATTEMPTS = 25;
    private const LINK_INDEX_MAX_ATTEMPTS = 100000;

    private const PRIORITY_MIN_EACH = 2;
    private const PRIORITY_MAX_EACH = 8;

    private const STATUS_MIN_EACH = 2;
    private const STATUS_MAX_EACH = 4;

    private const MIN_LINKED_TASK_RATIO = 0.10; // >= 10%
    private $output = null;

    public function run(): void
    {
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $io = $this->makeIo();
        $faker = FakerFactory::create('pt_BR');

        if (!Schema::hasTable(DC::TABLE_DL_TSK) || !Schema::hasTable(DC::TABLE_DEALS)) {
            $io->warning('Missing required tables; skipping DealTaskSeeder.');
            return;
        }

        $table = DC::TABLE_DL_TSK;

        $deals = $this->loadDealIdsRaw();
        if ($deals->isEmpty()) {
            $io->warning('No deals found; skipping DealTaskSeeder.');
            return;
        }

        $tasksPool = $this->loadTasksPoolRaw(); // may be empty
        $hasTasks = $tasksPool->isNotEmpty();

        $requested = $this->readCountOption();

        // Build base plan: 0..16 tasks per deal, inverted quadratic => more tendency to low values
        $plan = $this->buildPlanPerDeal($deals);

        // Enforce minimal coverage totals (priority/status minima) by increasing target (never decreasing plan)
        $minPriorityTotal = 8 * self::PRIORITY_MIN_EACH;  // codes 0..7
        $minStatusTotal = 13 * self::STATUS_MIN_EACH;     // codes 0..12

        $rawTotal = max($plan->count(), $minPriorityTotal, $minStatusTotal);
        if ($rawTotal === 0) $rawTotal = 1;

        // Adjust final count: always multiple of 64; if --count exists, it's a minimum (also rounded up to 64-multiple)
        $target = $this->adjustToMultipleOf64($rawTotal);
        if ($requested !== null) {
            $target = max($target, $this->adjustToMultipleOf64($requested));
        }

        // If plan smaller than target, pad by sampling deals (break-out strategy)
        if ($plan->count() < $target) {
            $plan = $plan->concat($this->buildPaddingPlan($deals, $target - $plan->count()));
        }

        // Recompute: we will assign priority/status across the final plan size
        $finalTotal = $plan->count();

        // Priority/status assignment pools
        [$priorityPool, $priorityOverflow] = $this->buildBoundedPool(
            keys: range(0, 7),
            minEach: self::PRIORITY_MIN_EACH,
            maxEach: self::PRIORITY_MAX_EACH,
            target: $finalTotal,
            weightFn: function (int $code): int {
                // Middle-ground bias around 2..3 (Medium/High)
                // higher weight when closer to 2.5
                $d = abs($code - 2.5);
                $w = (int)round(120 - ($d * $d * 18));
                return max(1, $w);
            }
        );

        [$statusPool, $statusOverflow] = $this->buildBoundedPool(
            keys: range(0, 12),
            minEach: self::STATUS_MIN_EACH,
            maxEach: self::STATUS_MAX_EACH,
            target: $finalTotal,
            weightFn: function (int $code): int {
                // "Middle-ground" for status: favor common operational states.
                // 0 InProgress, 1 Completed, 2 Pending, 3 Active, 4 Suspended, 5 Draft
                // others rarer.
                $map = [
                    0 => 120,
                    1 => 100,
                    2 => 95,
                    3 => 90,
                    4 => 50,
                    5 => 55,
                    6 => 30,
                    7 => 20,
                    8 => 18,
                    9 => 25,
                    10 => 15,
                    11 => 15,
                    12 => 22,
                ];
                return max(1, (int)($map[$code] ?? 10));
            }
        );

        // Decide which indices will be linked to tasks (>= 10% if tasks exist)
        $linkedIndices = $this->chooseLinkedIndices($finalTotal, $hasTasks ? self::MIN_LINKED_TASK_RATIO : 0.0);

        // Unique name local cache
        $usedNames = [];

        // Counters for reporting
        $created = 0;
        $linked = 0;
        $byPriority = [];
        $byStatus = [];
        $byDeal = [];

        $io->section('DealTaskSeeder plan');
        $io->text([
            'Deals: ' . $deals->count(),
            'Tasks pool: ' . $tasksPool->count() . ($hasTasks ? '' : ' (none; task_id will remain null)'),
            'Requested (--count): ' . ($requested !== null ? (string)$requested : 'n/a'),
            'Final target (multiple of 64): ' . $finalTotal,
            'Priority bounded overflow: ' . ($priorityOverflow ? 'yes' : 'no'),
            'Status bounded overflow: ' . ($statusOverflow ? 'yes' : 'no'),
            'Target linked ratio: ' . ($hasTasks ? (string)(int)round(self::MIN_LINKED_TASK_RATIO * 100) . '%' : '0%'),
        ]);

        // Persist using Model::create() to keep $casts + booted() behavior
        for ($i = 0; $i < $finalTotal; $i++) {
            $dealId = (string)($plan[$i]['deal_id'] ?? '');
            if ($dealId === '') continue;

            $priorityCode = (int)$priorityPool[$i];
            $statusCode = (int)$statusPool[$i];

            $linkToTask = $hasTasks && isset($linkedIndices[$i]);
            $taskId = null;
            $taskDate = null;
            $taskTime = null;

            if ($linkToTask) {
                $picked = $tasksPool->random();
                $taskId = (string)$picked['id'];
                $taskDate = $picked['date'] instanceof Carbon ? $picked['date'] : null;
                $taskTime = is_string($picked['time'] ?? null) ? trim((string)$picked['time']) : null;
                if ($taskTime === '') $taskTime = null;
            }

            // date/time are NOT NULL in migration => always set
            $date = $taskDate ?? $this->randomPastDate($faker, 120);
            $time = $taskTime ?? $this->randomTimeHHMMSS($faker);

            $attrs = [
                AC::COL_DL        => $dealId,
                PJC::COL_NM       => $this->generateUniqueNameWithExists($table, $usedNames),
                'description'     => (random_int(1, 100) <= 75) ? rtrim((string)$faker->sentence(random_int(6, 16)), '.') : null,
                AC::COL_TSK_DATE  => $date->toDateString(),
                AC::COL_TSK_TIME  => $time,
                PJC::COL_PRT      => $priorityCode,
                AC::COL_TSK_STT   => $statusCode,
                AC::COL_TSK_ID    => $taskId, // nullable; keep nulls
                'attachments'     => $this->randomStringList($faker, 0, 2, prefix: 'file_'),
                'tags'            => $this->randomStringList($faker, 0, 5, prefix: 'tag_'),
            ];

            $this->output->writeln('Creating DealTask for deal ' . $dealId . ' with priority ' . $priorityCode . ' and status ' . $statusCode . ($taskId !== null ? ' linked to task ' . $taskId : ' without task link'));
            DealTask::query()->create($attrs);

            $created++;

            $byPriority[$priorityCode] = (int)($byPriority[$priorityCode] ?? 0) + 1;
            $byStatus[$statusCode] = (int)($byStatus[$statusCode] ?? 0) + 1;

            $byDeal[$dealId] = (int)($byDeal[$dealId] ?? 0) + 1;

            if ($taskId !== null) $linked++;
        }

        $linkedPct = $created > 0 ? round(($linked / $created) * 100, 2) : 0.0;

        $io->section('DealTaskSeeder result');
        $io->text([
            'Created: ' . $created,
            'Linked to Task (FK): ' . $linked . ' (' . $linkedPct . '%)',
            'Priority distribution (code => count): ' . json_encode($byPriority, JSON_UNESCAPED_UNICODE),
            'Status distribution (code => count): ' . json_encode($byStatus, JSON_UNESCAPED_UNICODE),
            'Deals touched: ' . count($byDeal),
        ]);

        // Small deal distribution summary (min/max/avg) without dumping full map
        if ($byDeal !== []) {
            $vals = array_values($byDeal);
            sort($vals);
            $min = (int)($vals[0] ?? 0);
            $max = (int)($vals[count($vals) - 1] ?? 0);
            $avg = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : 0.0;
            $io->text([
                'Per-deal tasks: min=' . $min . ', max=' . $max . ', avg=' . $avg,
            ]);
        }

        if ($hasTasks && $linkedPct + 0.00001 < (self::MIN_LINKED_TASK_RATIO * 100)) {
            $io->warning('Linked ratio ended below 10% (check if tasks pool is too small or failed picks).');
        }
    }

    private function makeIo(): SymfonyStyle
    {
        $cmd = $this->command;

        $input = ($cmd instanceof Command && method_exists($cmd, 'getInput'))
            ? $cmd->getInput()
            : new ArrayInput([]);

        $output = ($cmd instanceof Command && method_exists($cmd, 'getOutput'))
            ? $cmd->getOutput()
            : new NullOutput();

        return new SymfonyStyle($input, $output);
    }

    private function readCountOption(): ?int
    {
        $cmd = $this->command;
        if (!($cmd instanceof Command)) return null;

        try {
            if (method_exists($cmd, 'hasOption') && $cmd->hasOption('count')) {
                $v = $cmd->option('count');
                if (is_numeric($v)) {
                    $n = (int)$v;
                    return $n > 0 ? $n : null;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @return Collection<int,string> deal IDs
     */
    private function loadDealIdsRaw(): Collection
    {
        $rows = DB::select('select id from ' . DC::TABLE_DEALS);
        $out = collect();

        foreach ($rows as $r) {
            $id = is_scalar($r->id ?? null) ? (string)$r->id : '';
            if ($id !== '') $out->push($id);
        }

        return $out->unique()->values();
    }

    /**
     * @return Collection<int,array{id:string,date:?Carbon,time:?string}>
     */
    private function loadTasksPoolRaw(): Collection
    {
        if (!Schema::hasTable(DC::TABLE_TASKS)) return collect();

        $table = DC::TABLE_TASKS;
        $cols = Schema::getColumnListing($table);

        $select = ['id'];
        if (in_array(AC::COL_TSK_DATE, $cols, true)) $select[] = AC::COL_TSK_DATE;
        if (in_array(AC::COL_TSK_TIME, $cols, true)) $select[] = AC::COL_TSK_TIME;

        $sql = 'select ' . implode(', ', $select) . ' from ' . $table;
        $rows = DB::select($sql);

        $out = collect();

        foreach ($rows as $r) {
            $id = is_scalar($r->id ?? null) ? (string)$r->id : '';
            if ($id === '') continue;

            $dt = null;
            if (property_exists($r, AC::COL_TSK_DATE) && $r->{AC::COL_TSK_DATE} !== null && (string)$r->{AC::COL_TSK_DATE} !== '') {
                try {
                    $dt = Carbon::parse((string)$r->{AC::COL_TSK_DATE})->startOfDay();
                } catch (\Throwable) {
                    $dt = null;
                }
            }

            $tm = null;
            if (property_exists($r, AC::COL_TSK_TIME)) {
                $s = trim((string)($r->{AC::COL_TSK_TIME} ?? ''));
                $tm = $s !== '' ? $s : null;
            }

            $out->push([
                'id' => $id,
                'date' => $dt,
                'time' => $tm,
            ]);
        }

        return $out->values();
    }

    /**
     * Build initial plan: per deal, sample 0..16 tasks with inverted quadratic bias to low values.
     * @param Collection<int,string> $deals
     * @return Collection<int,array{deal_id:string}>
     */
    private function buildPlanPerDeal(Collection $deals): Collection
    {
        $plan = collect();

        foreach ($deals as $dealId) {
            $n = $this->sampleInvertedQuadratic(0, self::MAX_TASKS_PER_DEAL_DISTRIBUTION);
            for ($i = 0; $i < $n; $i++) {
                $plan->push(['deal_id' => (string)$dealId]);
            }
        }

        return $plan->values();
    }

    /**
     * Padding plan: pick random deals until reaching pad size (break-out strategy).
     * @param Collection<int,string> $deals
     * @return Collection<int,array{deal_id:string}>
     */
    private function buildPaddingPlan(Collection $deals, int $pad): Collection
    {
        $out = collect();
        if ($pad <= 0 || $deals->isEmpty()) return $out;

        $maxAttempts = max(self::BUILD_PLAN_MAX_ATTEMPTS, $pad * 2);
        $attempts = 0;

        while ($out->count() < $pad && $attempts < $maxAttempts) {
            $attempts++;
            $dealId = (string)$deals->random();
            if ($dealId === '') continue;
            $out->push(['deal_id' => $dealId]);
        }

        return $out->values();
    }

    /**
     * @return array{0:array<int,int>,1:bool} [pool, overflowedBeyondMax]
     */
    private function buildBoundedPool(
        array $keys,
        int $minEach,
        int $maxEach,
        int $target,
        callable $weightFn
    ): array {
        $keys = array_values($keys);

        $counts = [];
        foreach ($keys as $k) $counts[(int)$k] = $minEach;

        $used = $minEach * count($keys);
        $target = max($target, $used);

        $overflow = false;

        $remaining = $target - $used;

        // Allocate up to maxEach using weighted random
        while ($remaining > 0) {
            $candidates = [];
            foreach ($keys as $k) {
                $k = (int)$k;
                if (($counts[$k] ?? 0) < $maxEach) $candidates[] = $k;
            }

            if ($candidates === []) break;

            $pick = $this->weightedPick($candidates, $weightFn);
            $counts[$pick] = (int)($counts[$pick] ?? 0) + 1;
            $remaining--;
        }

        // If still remaining, overflow beyond maxEach (log via overflow flag)
        while ($remaining > 0) {
            $overflow = true;
            $pick = $this->weightedPick($keys, $weightFn);
            $counts[$pick] = (int)($counts[$pick] ?? 0) + 1;
            $remaining--;
        }

        $pool = [];
        foreach ($counts as $k => $c) {
            for ($i = 0; $i < $c; $i++) $pool[] = (int)$k;
        }

        shuffle($pool);

        // Ensure exact length (defensive)
        if (count($pool) > $target) $pool = array_slice($pool, 0, $target);
        while (count($pool) < $target) $pool[] = (int)$keys[array_rand($keys)];

        return [$pool, $overflow];
    }

    private function weightedPick(array $keys, callable $weightFn): int
    {
        $weights = [];
        $sum = 0;

        foreach ($keys as $k) {
            $k = (int)$k;
            $w = (int)$weightFn($k);
            if ($w < 1) $w = 1;
            $weights[] = [$k, $w];
            $sum += $w;
        }

        $sum = max(1, $sum);
        $r = random_int(1, $sum);
        $acc = 0;

        foreach ($weights as [$k, $w]) {
            $acc += $w;
            if ($r <= $acc) return (int)$k;
        }

        return (int)$weights[0][0];
    }

    /**
     * Choose indices to link to tasks (as a set: index => true).
     * Break-out strategy included.
     *
     * @return array<int,bool>
     */
    private function chooseLinkedIndices(int $total, float $ratio): array
    {
        if ($total <= 0 || $ratio <= 0) return [];

        $want = (int)floor($total * $ratio);
        if ($want < 1) $want = 1;
        if ($want > $total) $want = $total;

        $out = [];
        $attempts = 0;
        $maxAttempts = min(self::LINK_INDEX_MAX_ATTEMPTS, max(200, $want * 50));

        while (count($out) < $want && $attempts < $maxAttempts) {
            $attempts++;
            $idx = random_int(0, $total - 1);
            $out[$idx] = true;
        }

        return $out;
    }

    /**
     * For contextually important uniqueness (name), check exists with raw SQL (do/while) + break-out.
     *
     * @param array<string,bool> $localUsedNames
     */
    private function generateUniqueNameWithExists(string $table, array &$localUsedNames): string
    {
        $attempts = 0;

        do {
            $attempts++;
            $candidate = 'DL-TSK-' . (string)Str::uuid();

            if (isset($localUsedNames[$candidate])) {
                $exists = true;
            } else {
                $row = DB::selectOne('select 1 as x from ' . $table . ' where ' . PJC::COL_NM . ' = ? limit 1', [$candidate]);
                $exists = $row !== null;
            }

            if (!$exists) {
                $localUsedNames[$candidate] = true;
                return $candidate;
            }
        } while ($attempts < self::UNIQUE_NAME_MAX_ATTEMPTS);

        $fallback = 'DL-TSK-' . (string)Str::uuid();
        $localUsedNames[$fallback] = true;
        return $fallback;
    }

    private function adjustToMultipleOf64(int $rawTotal): int
    {
        if ($rawTotal <= 0) return 64;
        $mod = $rawTotal % 64;
        return $mod === 0 ? $rawTotal : ($rawTotal + (64 - $mod));
    }

    private function sampleInvertedQuadratic(int $min, int $max): int
    {
        if ($min >= $max) return $min;

        $weights = [];
        $sum = 0;

        for ($k = $min; $k <= $max; $k++) {
            $w = (int)pow(($max + 1) - $k, 2);
            if ($w < 1) $w = 1;
            $weights[$k] = $w;
            $sum += $w;
        }

        $sum = max(1, $sum);
        $r = random_int(1, $sum);
        $acc = 0;

        foreach ($weights as $k => $w) {
            $acc += $w;
            if ($r <= $acc) return (int)$k;
        }

        return $min;
    }

    private function randomPastDate(Faker $faker, int $maxDaysBack): Carbon
    {
        $days = max(0, $maxDaysBack);
        $back = $days > 0 ? random_int(0, $days) : 0;
        return now()->subDays($back)->startOfDay();
    }

    private function randomTimeHHMMSS(Faker $faker): string
    {
        // Faker time() can vary; keep it strict
        $h = str_pad((string)random_int(0, 23), 2, '0', STR_PAD_LEFT);
        $m = str_pad((string)random_int(0, 59), 2, '0', STR_PAD_LEFT);
        $s = str_pad((string)random_int(0, 59), 2, '0', STR_PAD_LEFT);
        return $h . ':' . $m . ':' . $s;
    }

    /**
     * Return a list of unique strings (for JSON array casts), without null-stripping DB columns.
     *
     * @return array<int,string>
     */
    private function randomStringList(Faker $faker, int $min, int $max, string $prefix = ''): array
    {
        $min = max(0, $min);
        $max = max($min, $max);

        $n = ($max === $min) ? $min : random_int($min, $max);
        if ($n === 0) return [];

        $out = collect();

        for ($i = 0; $i < $n; $i++) {
            $w = trim(Str::ascii(mb_strtolower((string)$faker->word())));
            if ($w === '') continue;
            $out->push($prefix . $w);
        }

        return $out->unique()->values()->all();
    }
}
