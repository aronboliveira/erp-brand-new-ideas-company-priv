<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Models\TimeTracker;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class TimeTrackerSeeder extends Seeder
{
	// private const MAX_LOOP_GUARD = 2000;
	private const MAX_LOOP_GUARD = 4; /* original: 2000 */

	private const DEFAULT_POOL_LIMIT = 1600;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		$out = $this->output();
		$out->writeln('<info>[TimeTrackerSeeder]</info> start');
		$clock = microtime(true);
		if (!Schema::hasTable(DC::TABLE_TM_TRK)) {
			$out->writeln('<comment>[TimeTrackerSeeder]</comment> missing table: ' . DC::TABLE_TM_TRK);
			return;
		}

		DB::disableQueryLog();

		// --- Load pools (raw SQL only) ---
		$projects = $this->loadIdPool(DC::TABLE_PROJECTS, self::DEFAULT_POOL_LIMIT);
		$departments = $this->loadIdPool(DC::TABLE_DEPARTMENTS, self::DEFAULT_POOL_LIMIT);

		$tasks = $this->loadTaskPool(DC::TABLE_PROJ_TSKS, self::DEFAULT_POOL_LIMIT);
		$users = $this->loadUserPool(DC::TABLE_USERS, self::DEFAULT_POOL_LIMIT);

		$projectIndex = [];
		foreach ($projects as $pid) {
			$projectIndex[$pid] = true;
		}

		$out->writeln(sprintf(
			'<info>[TimeTrackerSeeder]</info> pools projects=%d tasks=%d users=%d departments=%d',
			count($projects),
			count($tasks),
			count($users),
			count($departments),
		));

		// --- Determine target count (64 * N, or --count) ---
		$baseN = $this->inferBaseEntityCount();
		$rawTarget = $this->inferTargetCount($baseN);
		$target = $this->roundUp64(max(64, $rawTarget));

		$out->writeln(sprintf(
			'<info>[TimeTrackerSeeder]</info> baseN=%d rawTarget=%d target=%d',
			$baseN,
			$rawTarget,
			$target
		));

		$faker = FakerFactory::create('pt_BR');

		$created = 0;
		$guard = 0;

		while ($created < $target) {
			$guard++;
			if ($guard > self::MAX_LOOP_GUARD) {
				$out->writeln('<error>[TimeTrackerSeeder]</error> guard stop (MAX_LOOP_GUARD)');
				return;
			}
			if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
				$out->writeln('<error>[TimeTrackerSeeder]</error> time limit reached, stopping early');
				return;
			}

			// ---- FK selection (make most not-null when pools exist) ----
			$user = $this->maybePick($users, 95);
			$userId = $user['id'] ?? null;

			$deptId = null;
			if (!empty($departments) && random_int(1, 100) <= 80) {
				$deptId = $departments[array_rand($departments)];
			}
			if ($deptId === null && isset($user['department_id']) && $user['department_id'] !== null) {
				$deptId = (string) $user['department_id'];
			}

			$task = $this->maybePick($tasks, 85);
			$taskId = $task['id'] ?? null;

			$projectId = null;

			// Prefer project_id from task when available (keeps consistency)
			if ($taskId !== null && isset($task[PJC::COL_PJ_ID]) && is_string($task[PJC::COL_PJ_ID]) && $task[PJC::COL_PJ_ID] !== '') {
				$pid = $task[PJC::COL_PJ_ID];
				$projectId = isset($projectIndex[$pid]) ? $pid : null;
			}

			if ($projectId === null && !empty($projects) && random_int(1, 100) <= 90) {
				$projectId = $projects[array_rand($projects)];
			}

			// If there is a task but project is missing, still keep task_id (FK is valid),
			// and let project_id be independent/nullable.

			// ---- Time generation (mostly with timer) ----
			$withTimer = random_int(1, 100) <= 88;

			$start = null;
			$end = null;
			$diffSeconds = null;

			if ($withTimer) {
				[$start, $end, $diffSeconds] = $this->randomStartEnd($faker);
			}

			// Legacy total_time string: mix numeric seconds vs HH:MM:SS
			$ttlSeed = (random_int(1, 100) <= 50) ? '0' : '00:00:00';

			// ---- Billing ----
			$isBillable = random_int(1, 100) <= 60;

			$hourlyPrice = null;
			$currency = null;

			if ($isBillable) {
				$hourlyPrice = $this->money2(random_int(2500, 60000) / 100); // 25.00 .. 600.00
				// sometimes blank to exercise booted() currency defaulting
				$currency = (random_int(1, 100) <= 85) ? $this->pickCurrency() : '';
			} else {
				// small chance to set a value and ensure booted() nullifies
				if (random_int(1, 100) <= 5) {
					$hourlyPrice = $this->money2(random_int(1000, 15000) / 100);
					$currency = '';
				}
			}

			$totalHours = $diffSeconds !== null ? round($diffSeconds / 3600, 2) : 0.0;

			$hoursWithoutTimer = null;
			if ($withTimer && random_int(1, 100) <= 45) {
				$max = max(0.0, $totalHours);
				$hoursWithoutTimer = $this->money2($max > 0 ? (random_int(0, (int) round($max * 100)) / 100) : 0.0);
			}

			$billableHours = $this->money2(0.0);
			if ($isBillable) {
				$max = max(0.0, $totalHours);
				$billableHours = $this->money2($max > 0 ? (random_int(0, (int) round($max * 100)) / 100) : 0.0);
			}

			// ---- Tags / attachments ----
			$tagsArr = $this->randomTags($faker);
			$tagIdLegacy = $this->tagIdLegacyString($tagsArr);

			$attachments = $this->randomAttachments($faker);

			// ---- Name / description ----
			$name = $this->makeName($faker, $projectId, $taskId, $userId);
			$description = (random_int(1, 100) <= 75)
				? rtrim((string) $faker->sentence(random_int(6, 14)), ". \t\n\r\0\x0B")
				: null;

			// is_active legacy string
			$isActive = (random_int(1, 100) <= 90) ? '1' : '0';

			// ---- Persist via model (triggers booted() normalization/caps) ----
			$m = new TimeTracker();
			$m->fill([
				PJC::COL_NM => $name,
				'description' => $description,

				PJC::COL_PJ_ID => $projectId,
				AC::COL_TSK_ID => $taskId,
				UC::COL_USER_ID => $userId,
				CC::COL_DEP_ID => $deptId,

				PJC::COL_TAG_ID => $tagIdLegacy,
				'tags' => $tagsArr,
				'attachments' => $attachments,

				PJC::COL_IS_BLB => $isBillable,
				PJC::COL_BLB_HRS => $billableHours,
				PJC::COL_HRS_WTT_TIMER => $hoursWithoutTimer,
				PJC::COL_HR_PRC => $hourlyPrice,
				'currency' => $currency ?? SC::DEF_SITE_CURRENCY_ID,

				AC::COL_ST_TIME => $start,
				AC::COL_E_TIME => $end,
				AC::COL_TTL_TIME => $ttlSeed,
				AC::COL_IA => $isActive,
			]);
			$out->writeln("<info>[TimeTrackerSeeder]</info> creating time tracker: user_id={$userId} project_id={$projectId} task_id={$taskId} start=" . ($start !== null ? $start->toDateTimeString() : 'null') . " end=" . ($end !== null ? $end->toDateTimeString() : 'null') . " total_hours={$totalHours} billable_hours={$billableHours} is_billable=" . ($isBillable ? '1' : '0'));
			$m->save();

			$created++;

			if (($created % 64) === 0) {
				$out->writeln("<info>[TimeTrackerSeeder]</info> created={$created}/{$target}");
			}
		}

		$out->writeln("<info>[TimeTrackerSeeder]</info> done created={$created}");
	}

	private function output(): ConsoleOutput|\Symfony\Component\Console\Output\OutputInterface
	{
		if ($this->command instanceof \Illuminate\Console\Command && method_exists($this->command, 'getOutput')) {
			return $this->command->getOutput();
		}
		return new ConsoleOutput();
	}

	/**
	 * Base entity for TimeTrackers:
	 * prefer project_tasks; else users; else projects; else 1
	 * (raw SQL, defensive).
	 */
	private function inferBaseEntityCount(): int
	{
		$taskCount = $this->countRowsRaw(DC::TABLE_PROJ_TSKS);
		if ($taskCount > 0) return $taskCount;

		$userCount = $this->countRowsRaw(DC::TABLE_USERS);
		if ($userCount > 0) return $userCount;

		$projectCount = $this->countRowsRaw(DC::TABLE_PROJECTS);
		if ($projectCount > 0) return $projectCount;

		return 1;
	}

	private function inferTargetCount(int $baseN): int
	{
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) return $opt;
		}

		$n = max(1, $baseN);
		return 16 * $n;
	}

	private function roundUp64(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}

	private function countRowsRaw(string $table): int
	{
		if (!Schema::hasTable($table)) return 0;

		try {
			$row = DB::selectOne('select count(*) as c from ' . $table);
			return (int) ($row->c ?? 0);
		} catch (\Throwable) {
			return 0;
		}
	}

	/**
	 * @return array<int, string> UUIDs
	 */
	private function loadIdPool(string $table, int $limit): array
	{
		if (!Schema::hasTable($table)) return [];

		try {
			$rows = DB::select(
				'select id from ' . $table . ' order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit
			);
		} catch (\Throwable) {
			return [];
		}

		$ids = [];
		foreach ($rows as $r) {
			if (is_scalar($r->id ?? null)) $ids[] = (string) $r->id;
		}

		// keep shape consistent; no array_filter for null-removal purposes
		return array_values(array_unique($ids));
	}

	/**
	 * @return array<int, array{id:string, project_id?:string}>
	 */
	private function loadTaskPool(string $table, int $limit): array
	{
		if (!Schema::hasTable($table)) return [];

		$cols = ['id'];
		if (Schema::hasColumn($table, PJC::COL_PJ_ID)) {
			$cols[] = PJC::COL_PJ_ID;
		}

		try {
			$rows = DB::select(
				'select ' . implode(',', $cols) . ' from ' . $table . ' order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit
			);
		} catch (\Throwable) {
			return [];
		}

		$out = [];
		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
			if ($id === '') continue;

			$row = ['id' => $id];

			if (in_array(PJC::COL_PJ_ID, $cols, true) && is_scalar($r->{PJC::COL_PJ_ID} ?? null)) {
				$pid = (string) $r->{PJC::COL_PJ_ID};
				if ($pid !== '') $row[PJC::COL_PJ_ID] = $pid;
			}

			$out[] = $row;
		}

		return $out;
	}

	/**
	 * @return array<int, array{id:string, department_id?:string|null}>
	 */
	private function loadUserPool(string $table, int $limit): array
	{
		if (!Schema::hasTable($table)) return [];

		$cols = ['id'];
		if (Schema::hasColumn($table, CC::COL_DEP_ID)) {
			$cols[] = CC::COL_DEP_ID;
		}

		try {
			$rows = DB::select(
				'select ' . implode(',', $cols) . ' from ' . $table . ' order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit
			);
		} catch (\Throwable) {
			return [];
		}

		$out = [];
		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
			if ($id === '') continue;

			$row = ['id' => $id];

			if (in_array(CC::COL_DEP_ID, $cols, true)) {
				$dep = $r->{CC::COL_DEP_ID} ?? null;
				if (is_scalar($dep)) {
					$dep = (string) $dep;
					$row[CC::COL_DEP_ID] = ($dep !== '') ? $dep : null;
				} else {
					$row[CC::COL_DEP_ID] = null;
				}
			}

			$out[] = $row;
		}

		return $out;
	}

	private function randomOrderSql(): string
	{
		return match (DB::getDriverName()) {
			'pgsql', 'sqlite' => 'RANDOM()',
			default => 'RAND()',
		};
	}

	/**
	 * @param array<int, mixed> $pool
	 */
	private function maybePick(array $pool, int $chancePct): mixed
	{
		if (empty($pool)) return null;
		if (random_int(1, 100) > $chancePct) return null;
		return $pool[array_rand($pool)];
	}

	/**
	 * @return array{0:Carbon,1:Carbon,2:int}
	 */
	private function randomStartEnd(Faker $faker): array
	{
		$daysBack = random_int(0, 120);
		$date = Carbon::now()->subDays($daysBack)->startOfDay();

		$startHour = random_int(6, 18);
		$startMin = [0, 5, 10, 15, 20, 30, 40, 45, 50][array_rand([0, 1, 2, 3, 4, 5, 6, 7, 8])];

		$start = (clone $date)->setTime($startHour, $startMin, 0);

		$durationMin = random_int(5, 600); // 5 min .. 10h
		$end = (clone $start)->addMinutes($durationMin);

		$diffSeconds = $start->diffInSeconds($end, false);
		if ($diffSeconds < 0) $diffSeconds = 0;

		return [$start, $end, $diffSeconds];
	}

	private function pickCurrency(): string
	{
		// Keep small set; model will default to SC::DEF_SITE_CURRENCY_ID if blank and hourly_price > 0
		$list = ['BRL', 'USD', 'EUR', 'GBP', 'CAD'];
		return $list[array_rand($list)];
	}

	private function money2(float $v): string
	{
		$v = max(0.0, $v);
		return number_format($v, 2, '.', '');
	}

	/**
	 * @return array<int, string>
	 */
	private function randomTags(Faker $faker): array
	{
		$n = random_int(0, 5);
		if ($n === 0) return [];

		$tags = [];
		for ($i = 0; $i < $n; $i++) {
			$w = (string) $faker->word();
			$w = Str::ascii(mb_strtolower(trim($w)));
			if ($w !== '') $tags[] = $w;
		}

		$tags = array_values(array_unique($tags));
		return $tags;
	}

	/**
	 * Legacy tag_id (text) – keep simple and deterministic from tags array.
	 * @param array<int, string> $tags
	 */
	private function tagIdLegacyString(array $tags): ?string
	{
		if (!$tags) return null;
		$s = implode(',', $tags);
		return $s !== '' ? $s : null;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function randomAttachments(Faker $faker): array
	{
		$n = random_int(0, 3);
		if ($n === 0) return [];

		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$ext = ['pdf', 'png', 'jpg', 'xlsx', 'csv'][array_rand(['pdf', 'png', 'jpg', 'xlsx', 'csv'])];
			$name = Str::slug((string) $faker->words(random_int(2, 4), true)) . '.' . $ext;

			$out[] = [
				'file_name' => $name,
				'file_path' => 'uploads/time_trackers/' . Carbon::now()->format('Y/m') . '/' . $name,
				'mime_type' => match ($ext) {
					'pdf' => 'application/pdf',
					'png' => 'image/png',
					'jpg' => 'image/jpeg',
					'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'csv' => 'text/csv',
					default => 'application/octet-stream',
				},
				'file_size' => random_int(10_000, 5_000_000),
			];
		}

		return $out;
	}

	private function makeName(Faker $faker, ?string $projectId, ?string $taskId, ?string $userId): string
	{
		$base = Str::title((string) $faker->words(random_int(2, 5), true));
		$parts = [];

		if ($projectId !== null) $parts[] = 'PJ';
		if ($taskId !== null) $parts[] = 'TSK';
		if ($userId !== null) $parts[] = 'USR';

		$suffix = $parts ? ('[' . implode('-', $parts) . ']') : '[GEN]';
		$name = trim($base . ' ' . $suffix);

		if (mb_strlen($name) > 254) {
			$name = mb_substr($name, 0, 254);
		}

		return $name !== '' ? $name : 'Time Tracker';
	}
}
