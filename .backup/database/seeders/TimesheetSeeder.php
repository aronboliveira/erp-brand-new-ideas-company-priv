<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{EvaluationStatus, Visibility};
use App\Models\Timesheet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class TimesheetSeeder extends Seeder
{
	// private const HARD_CAP = 32000;
	private const HARD_CAP = 2;
	private const MULTIPLE = 64;

	private ConsoleOutput $out;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$table = (new Timesheet())->getTable();

		try {
			$projectIds = $this->fetchIds(DC::TABLE_PROJECTS);
			$taskIds    = $this->fetchIds(DC::TABLE_TASKS);

			if (!$projectIds && !$taskIds) {
				$this->out->writeln('<comment>[TimesheetSeeder]</comment> No projects/tasks found; skipping.');
				return;
			}

			$sampleProjects = $this->sampleList($projectIds, 0.2, minCount: 0);
			$sampleTasks    = $this->sampleList($taskIds, 0.2, minCount: 0);

			$rawTotal = count($sampleProjects) + count($sampleTasks);
			if ($rawTotal <= 0) {
				$this->out->writeln('<comment>[TimesheetSeeder]</comment> Sampling produced 0 rows; skipping.');
				return;
			}

			// Nearest 64-multiple upward: raw + (64 - raw%64) when remainder exists
			$target = $this->roundUpToMultiple(min($rawTotal, self::HARD_CAP), self::MULTIPLE);
			if ($target > self::HARD_CAP) $target = self::roundDownToMultiple(self::HARD_CAP, self::MULTIPLE);

			$userIds = $this->fetchIds(DC::TABLE_USERS);
			$employeeIds = $this->fetchIds(DC::TABLE_EMPLOYEES);

			// ProjectTask table is referenced in multiple migrations; keep it simple and reuse DC::TABLE_PROJ_TSKS.
			$projectTaskIds = $this->fetchIds(DC::TABLE_PROJ_TSKS);

			$projectsById = $this->fetchProjectsBoundsById($projectIds);

			// Optional: map project_id => project_task_ids (raw DB, cached)
			$projTaskByProject = $this->fetchProjectTaskIdsByProject();

			$created = 0;

			// ============ Main first loop: foreach (project id * 0.2) ============
			foreach ($sampleProjects as $projectId) {
				if ($created >= $target) break;

				$payload = $this->buildTimesheetPayload(
					table: $table,
					projectId: $projectId,
					taskId: null,
					projectTaskId: $this->pickProjectTaskForProject($projTaskByProject, $projectId),
					userIds: $userIds,
					employeeIds: $employeeIds,
					projectBounds: $projectsById[$projectId] ?? null
				);

				$this->out->writeln($this->formatLine('project', $payload));
				$this->persist($payload);
				$created++;
			}

			// ============ Main second loop: foreach (task id * 0.2) ============
			// Tries to link project_id if task has one, otherwise leaves null.
			$tasksProjectMap = $this->fetchTaskProjectMap($sampleTasks);

			foreach ($sampleTasks as $taskId) {
				if ($created >= $target) break;

				$projectId = $tasksProjectMap[$taskId] ?? null;

				$payload = $this->buildTimesheetPayload(
					table: $table,
					projectId: $projectId,
					taskId: $taskId,
					projectTaskId: $projectId ? $this->pickProjectTaskForProject($projTaskByProject, $projectId) : null,
					userIds: $userIds,
					employeeIds: $employeeIds,
					projectBounds: $projectId ? ($projectsById[$projectId] ?? null) : null
				);

				$this->out->writeln($this->formatLine('task', $payload));
				$this->persist($payload);
				$created++;
			}

			// ============ Fill remainder to reach target (still respecting caps) ============
			$attempts = 0;
			$maxAttempts = max(5000, $target * 4);

			while ($created < $target && $attempts < $maxAttempts) {
				$attempts++;

				$useProject = (bool) random_int(0, 1);

				$projectId = $useProject && $projectIds ? $projectIds[array_rand($projectIds)] : null;
				$taskId = (!$useProject && $taskIds) ? $taskIds[array_rand($taskIds)] : null;

				if ($projectId === null && $taskId === null) continue;

				if ($projectId === null && $taskId !== null) {
					$projectId = $tasksProjectMap[$taskId] ?? $this->fetchSingleTaskProjectId($taskId);
				}

				$payload = $this->buildTimesheetPayload(
					table: $table,
					projectId: $projectId,
					taskId: $taskId,
					projectTaskId: $projectId ? $this->pickProjectTaskForProject($projTaskByProject, $projectId) : null,
					userIds: $userIds,
					employeeIds: $employeeIds,
					projectBounds: $projectId ? ($projectsById[$projectId] ?? null) : null
				);

				$this->out->writeln($this->formatLine('fill', $payload));
				$this->persist($payload);
				$created++;
			}

			if ($attempts >= $maxAttempts && $created < $target) {
				Log::warning('TimesheetSeeder: attempt limit hit before reaching target', [
					'table' => $table,
					'target' => $target,
					'created' => $created,
					'maxAttempts' => $maxAttempts,
				]);
			}

			$this->out->writeln("<info>[TimesheetSeeder]</info> Created {$created} timesheets (target={$target}, raw={$rawTotal}).");
		} catch (\Throwable $e) {
			Log::error('TimesheetSeeder failed', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			$this->out->writeln('<error>[TimesheetSeeder]</error> Failed: ' . $e->getMessage());
		}
	}

	/**
	 * Reads only: optimized raw SQL.
	 */
	private function fetchIds(string $table): array
	{
		try {
			$rows = DB::select("select id from {$table}");
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (is_string($id) && $id !== '') $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning("TimesheetSeeder: failed to fetch ids from {$table}", [
				'table' => $table,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function sampleList(array $ids, float $ratio, int $minCount = 0): array
	{
		$n = count($ids);
		if ($n <= 0) return [];
		$k = (int) floor($n * $ratio);
		if ($k < $minCount) $k = $minCount;
		if ($k <= 0) return [];

		shuffle($ids);
		return array_slice($ids, 0, min($k, $n));
	}

	private function roundUpToMultiple(int $n, int $multiple): int
	{
		$r = $n % $multiple;
		return $r === 0 ? $n : ($n + ($multiple - $r));
	}

	private function roundDownToMultiple(int $n, int $multiple): int
	{
		return $n - ($n % $multiple);
	}

	private function generateUniqueCode(string $table, int $maxAttempts = 20): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$code = 'TMS-' . strtoupper((string) Str::uuid());

			try {
				$exists = DB::table($table)->where('code', $code)->exists();
				if (!$exists) return $code;
			} catch (\Throwable $e) {
				Log::warning('TimesheetSeeder: failed checking code uniqueness', [
					'table' => $table,
					'code' => $code,
					'error' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
				// If uniqueness check fails, still return candidate after a few attempts to avoid stalling.
				if ($attempts >= 3) return $code;
			}
		} while ($attempts < $maxAttempts);

		return 'TMS-' . strtoupper((string) Str::uuid());
	}

	/**
	 * project_id => ['start' => ?string, 'end' => ?string, 'estimated_hrs' => ?string]
	 */
	private function fetchProjectsBoundsById(array $projectIds): array
	{
		if (!$projectIds) return [];

		try {
			// Pull minimal columns; note PJC::COL_S_DT and PJC::COL_E_DT are dates in projects table.
			$rows = DB::table(DC::TABLE_PROJECTS)
				->whereIn('id', $projectIds)
				->get(['id', PJC::COL_S_DT, PJC::COL_E_DT, PJC::COL_E_HRS]);

			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (!is_string($id) || $id === '') continue;

				$out[$id] = [
					'start' => $r->{PJC::COL_S_DT} ?? null,
					'end' => $r->{PJC::COL_E_DT} ?? null,
					'estimated_hrs' => $r->{PJC::COL_E_HRS} ?? null,
				];
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to fetch project bounds', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	/**
	 * project_id => [project_task_id, ...]
	 */
	private function fetchProjectTaskIdsByProject(): array
	{
		try {
			$rows = DB::select('select id, ' . PJC::COL_PJ_ID . ' as project_id from ' . DC::TABLE_PROJ_TSKS . ' where ' . PJC::COL_PJ_ID . ' is not null');
			$map = [];
			foreach ($rows as $r) {
				$pid = $r->project_id ?? null;
				$id = $r->id ?? null;
				if (!is_string($pid) || $pid === '') continue;
				if (!is_string($id) || $id === '') continue;
				$map[$pid] ??= [];
				$map[$pid][] = $id;
			}
			return $map;
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to fetch project_task ids by project', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function pickProjectTaskForProject(array $map, ?string $projectId): ?string
	{
		if (!$projectId) return null;
		$list = $map[$projectId] ?? null;
		if (!$list) return null;
		return $list[array_rand($list)];
	}

	/**
	 * task_id => project_id (for the sampled subset)
	 */
	private function fetchTaskProjectMap(array $taskIds): array
	{
		if (!$taskIds) return [];

		try {
			$rows = DB::table(DC::TABLE_TASKS)
				->whereIn('id', $taskIds)
				->get(['id', PJC::COL_PJ_ID]);

			$map = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (!is_string($id) || $id === '') continue;
				$pid = $r->{PJC::COL_PJ_ID} ?? null;
				$map[$id] = (is_string($pid) && $pid !== '') ? $pid : null;
			}
			return $map;
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to fetch task->project map', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function fetchSingleTaskProjectId(string $taskId): ?string
	{
		try {
			$row = DB::table(DC::TABLE_TASKS)->where('id', $taskId)->first([PJC::COL_PJ_ID]);
			$pid = $row?->{PJC::COL_PJ_ID} ?? null;
			return (is_string($pid) && $pid !== '') ? $pid : null;
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to fetch single task project id', [
				'task_id' => $taskId,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return null;
		}
	}

	/**
	 * Builds a Timesheet payload; uses model-level logic (casts/booted) by persisting through Eloquent.
	 * Note: the model should nullify inconsistent links (project/task bounds) if your boot logic enforces it.
	 */
	private function buildTimesheetPayload(
		string $table,
		?string $projectId,
		?string $taskId,
		?string $projectTaskId,
		array $userIds,
		array $employeeIds,
		?array $projectBounds
	): array {
		$code = $this->generateUniqueCode($table);

		[$date, $time] = $this->pickDueDateTimeWithinProjectBounds($projectBounds);

		// total_time (hours) and expense_duration (hours)
		$totalTime = $this->randDecimal(0.25, 12.00, 2);
		$expenseDuration = $this->clampExpenseDuration($this->randDecimal(0.25, 10.00, 2), $projectBounds);

		$visibility = $this->pickVisibility();

		// Status columns are dynamic (PlansByHierarchy); still set a baseline to satisfy nullability and reduce surprises.
		$status = EvaluationStatus::NotStarted->value;

		$employeeId = $employeeIds ? $employeeIds[array_rand($employeeIds)] : null;

		// Optional workflow columns (the trait will derive status)
		$workflow = $this->pickWorkflow($userIds);

		// attachments as array; model casts should handle encoding; keep shape stable.
		$attachments = $this->maybeAttachments();

		// Timesheets migration uses PJC::COL_S_DT date and PJC::COL_E_DT time (as written).
		$startDate = $projectBounds['start'] ?? null;
		$endTime = $this->maybeTime();

		return [
			'id' => (string) Str::uuid(),
			'code' => $code,
			PJC::COL_PJ_ID => $projectId,
			AC::COL_TSK_ID => $taskId,              // note: migration references DC::TABLE_PROJ_TSKS for this FK
			PJC::COL_PJ_TSK_ID => $projectTaskId,   // project task pointer
			PJC::COL_S_DT => $startDate,            // date column (nullable)
			PJC::COL_E_DT => $endTime,              // time column (nullable) per migration
			'status' => $status,
			'date' => $date,                        // required in migration (not nullable)
			'time' => $time,                        // required in migration (not nullable)
			AC::COL_TTL_TIME => $totalTime,
			PJC::COL_EXP_DR => $expenseDuration,
			PJC::COL_SBM_BY => $workflow['submitted_by'],
			PJC::COL_SBM_AT => $workflow['submitted_at'],
			PJC::COL_APV_BY => $workflow['approved_by'],
			PJC::COL_APV_AT => $workflow['approved_at'],
			PJC::COL_REJ_BY => $workflow['rejected_by'],
			PJC::COL_REJ_AT => $workflow['rejected_at'],
			'visibility' => $visibility,
			'description' => $this->maybeDescription($projectId, $taskId),
			UC::COL_EMP_ID => $employeeId,
			'attachments' => $attachments,
			// audit columns are nullable in migration (HasNullableAuditColumns); model/traits may auto-assign.
			DC::COL_TABLE_CREATOR => $userIds ? $userIds[array_rand($userIds)] : null,
			DC::COL_TABLE_UPDATER => null,
		];
	}

	private function pickDueDateTimeWithinProjectBounds(?array $projectBounds): array
	{
		$start = $projectBounds['start'] ?? null;
		$end = $projectBounds['end'] ?? null;

		try {
			$startTs = is_string($start) && $start !== '' ? strtotime($start) : null;
			$endTs = is_string($end) && $end !== '' ? strtotime($end) : null;

			$min = $startTs ?: strtotime('-60 days');
			$max = $endTs ?: strtotime('+30 days');

			if ($max < $min) {
				$tmp = $min;
				$min = $max;
				$max = $tmp;
			}

			$randTs = random_int($min, $max);
			$date = date('Y-m-d', $randTs);

			$hh = str_pad((string) random_int(8, 20), 2, '0', STR_PAD_LEFT);
			$mm = str_pad((string) ([0, 15, 30, 45][array_rand([0, 15, 30, 45])]), 2, '0', STR_PAD_LEFT);
			$time = "{$hh}:{$mm}:00";

			return [$date, $time];
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to pick due datetime', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);

			return [date('Y-m-d'), '10:00:00'];
		}
	}

	private function clampExpenseDuration(float $value, ?array $projectBounds): float
	{
		$max = null;

		try {
			$est = $projectBounds['estimated_hrs'] ?? null;
			if (is_numeric($est)) $max = (float) $est;

			$start = $projectBounds['start'] ?? null;
			$end = $projectBounds['end'] ?? null;

			if ($max === null && is_string($start) && is_string($end) && $start !== '' && $end !== '') {
				$startTs = strtotime($start);
				$endTs = strtotime($end);
				if ($startTs && $endTs && $endTs >= $startTs) {
					$days = (float) (($endTs - $startTs) / 86400);
					$max = $days * 24.0;
				}
			}
		} catch (\Throwable $e) {
			Log::warning('TimesheetSeeder: failed to compute max expense duration', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}

		if ($max !== null && $max > 0 && $value > $max) $value = $max;
		if ($value < 0) $value = 0.0;

		return round($value, 2);
	}

	private function pickVisibility(): string
	{
		$cases = array_column(Visibility::cases(), 'value');
		return $cases ? $cases[array_rand($cases)] : Visibility::Private->value;
	}

	private function pickWorkflow(array $userIds): array
	{
		// Priority: rejected > approved > submitted > none
		$mode = random_int(0, 3);

		$now = now();
		$submittedBy = null;
		$approvedBy = null;
		$rejectedBy = null;

		$submittedAt = null;
		$approvedAt = null;
		$rejectedAt = null;

		if (!$userIds) return compact('submittedBy', 'submittedAt', 'approvedBy', 'approvedAt', 'rejectedBy', 'rejectedAt');

		if ($mode === 1 || $mode === 2 || $mode === 3) {
			$submittedBy = $userIds[array_rand($userIds)];
			$submittedAt = $now->copy()->subDays(random_int(0, 30));
		}

		if ($mode === 2) {
			$approvedBy = $userIds[array_rand($userIds)];
			$approvedAt = $now->copy()->subDays(random_int(0, 15));
		}

		if ($mode === 3) {
			$rejectedBy = $userIds[array_rand($userIds)];
			$rejectedAt = $now->copy()->subDays(random_int(0, 15));
		}

		return [
			'submitted_by' => $submittedBy,
			'submitted_at' => $submittedAt,
			'approved_by' => $approvedBy,
			'approved_at' => $approvedAt,
			'rejected_by' => $rejectedBy,
			'rejected_at' => $rejectedAt,
		];
	}

	private function maybeAttachments(): ?array
	{
		if (random_int(0, 100) < 60) return null;

		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = 'timesheets/mock/' . Str::random(12) . '.pdf';
		}
		return $out;
	}

	private function maybeTime(): ?string
	{
		if (random_int(0, 100) < 65) return null;
		$hh = str_pad((string) random_int(7, 22), 2, '0', STR_PAD_LEFT);
		$mm = str_pad((string) ([0, 15, 30, 45][array_rand([0, 15, 30, 45])]), 2, '0', STR_PAD_LEFT);
		return "{$hh}:{$mm}:00";
	}

	private function maybeDescription(?string $projectId, ?string $taskId): ?string
	{
		if (random_int(0, 100) < 25) return null;

		$ctx = [];
		$projectId && $ctx[] = "project={$projectId}";
		$taskId && $ctx[] = "task={$taskId}";
		$suffix = $ctx ? (' [' . implode(', ', $ctx) . ']') : '';

		return 'Timesheet entry (mock)' . $suffix;
	}

	private function randDecimal(float $min, float $max, int $places): float
	{
		$scale = 10 ** $places;
		$imin = (int) round($min * $scale);
		$imax = (int) round($max * $scale);
		if ($imax < $imin) {
			$tmp = $imin;
			$imin = $imax;
			$imax = $tmp;
		}
		return random_int($imin, $imax) / $scale;
	}

	private function formatLine(string $mode, array $p): string
	{
		$pid = $p[PJC::COL_PJ_ID] ?? null;
		$tid = $p[AC::COL_TSK_ID] ?? null;
		$dt = ($p['date'] ?? '') . ' ' . ($p['time'] ?? '');
		$code = $p['code'] ?? '';
		$vis = $p['visibility'] ?? '';
		return "<info>[TimesheetSeeder]</info> mode={$mode} code={$code} due={$dt} project=" . ($pid ?: 'null') . " task=" . ($tid ?: 'null') . " visibility={$vis}";
	}

	private function persist(array $payload): void
	{
		try {
			$m = new Timesheet();

			// Respect your rule: inside model scope use getAttribute/setAttribute; here we are in seeder,
			// still we set attributes through Eloquent to trigger casts/booted.
			foreach ($payload as $k => $v) {
				$m->setAttribute($k, $v);
			}

			$m->save();
		} catch (\Throwable $e) {
			Log::error('TimesheetSeeder: failed to persist timesheet', [
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'code' => $payload['code'] ?? null,
				'id' => $payload['id'] ?? null,
			]);
		}
	}
}
