<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	FormsConstants as FC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\InterviewSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class InterviewScheduleSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const HARD_CAP = 2048;
	private const HARD_CAP = 2;
	private const MIN_ROWS = 64;

	private const MAX_PER_CANDIDATE = 8;
	private const MAX_PER_EMPLOYEE  = 32;

	/** Ensure at least 10% of *task IDs* are used at least once */
	private const TASK_IDS_MIN_PCT = 0.10;

	/** When not forced, probability that a schedule has a task_id */
	private const TASK_LINK_PCT = 0.22;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		DB::disableQueryLog();

		$candidateIds = $this->fetchIds(DC::TABLE_JOB_APPS);
		if (!$candidateIds) {
			$this->out->writeln('<comment>[InterviewScheduleSeeder]</comment> No candidates (job apps) found. Skipping.');
			return;
		}

		$userInterviewerIds = $this->fetchEligibleInterviewerUserIds();
		$employeeEntityIds  = $this->fetchEmployeeEntityIds();

		$interviewerIds = array_values(array_unique(array_merge($employeeEntityIds, $userInterviewerIds)));
		if (!$interviewerIds) {
			$this->out->writeln('<comment>[InterviewScheduleSeeder]</comment> No eligible interviewers found. Skipping.');
			return;
		}

		$userIdSet = array_fill_keys($userInterviewerIds, true);

		$projectIds = $this->fetchIdsIfTableExists(DC::TABLE_PROJECTS, 4096);
		$formIds    = $this->fetchIdsIfTableExists(DC::TABLE_FORM_BUILD, 4096);
		$taskIds    = $this->fetchIdsIfTableExists(DC::TABLE_TASKS, 8192);
		$docIds     = $this->fetchIdsIfTableExists(DC::TABLE_DOCS, 8192);

		$todoMap = $this->fetchTodoMapForUsers($userInterviewerIds);

		$plan = $this->buildPlan($candidateIds, $interviewerIds);

		// Ensure minimum rows (best-effort)
		while (count($plan) < min(self::MIN_ROWS, self::HARD_CAP)) {
			$plan[] = [
				$this->pickRandomId($candidateIds),
				$this->pickRandomId($interviewerIds),
			];
		}

		// Hard cap
		if (count($plan) > self::HARD_CAP) {
			$plan = array_slice($plan, 0, self::HARD_CAP);
		}

		// Task assignment: enforce >= 10% of task IDs used at least once
		$taskAssignments = $this->assignTasksToPlan($taskIds, count($plan));

		$hasCreator = $this->hasColumnSafe(DC::TABLE_ITV_SCD, DC::COL_TABLE_CREATOR);
		$hasUpdater = $this->hasColumnSafe(DC::TABLE_ITV_SCD, DC::COL_TABLE_UPDATER);

		$now = CarbonImmutable::now('America/Sao_Paulo');

		$created = 0;
		$usedSlots = []; // local uniqueness for (employee,candidate,date,time)

		$this->out->writeln(sprintf(
			'<info>[InterviewScheduleSeeder]</info> candidates=%d interviewers=%d plan=%d cap=%d tasks=%d',
			count($candidateIds),
			count($interviewerIds),
			count($plan),
			self::HARD_CAP,
			count($taskIds)
		));

		foreach ($plan as $i => $pair) {
			[$candidateId, $employeeId] = $pair;

			$candidateId = is_string($candidateId) ? trim($candidateId) : '';
			$employeeId  = is_string($employeeId) ? trim($employeeId) : '';

			if ($candidateId === '' || $employeeId === '') continue;

			$taskId = $taskAssignments[$i] ?? null;

			[$date, $time] = $this->pickUniqueDateTime($employeeId, $candidateId, $now, $usedSlots);

			$isOnline  = random_int(1, 100) <= 55;
			$url       = $isOnline ? $this->randomMeetingUrl() : null;
			$location  = $isOnline ? null : $this->randomLocation();

			$roleTitle = $this->randomRoleTitle();
			$roleDesc  = random_int(1, 100) <= 70 ? $this->randomRoleDescription($roleTitle) : null;

			$comment   = random_int(1, 100) <= 55 ? $this->randomComment() : null;
			$empResp   = $this->randomEmployeeResponse();
			$notes     = random_int(1, 100) <= 45 ? $this->randomNotes() : null;
			$feedback  = random_int(1, 100) <= 35 ? $this->randomFeedback() : null;

			$projectId = ($projectIds && random_int(1, 100) <= 12) ? $this->pickRandomId($projectIds) : null;
			$formId    = ($formIds && random_int(1, 100) <= 12) ? $this->pickRandomId($formIds) : null;
			$docId     = ($docIds && random_int(1, 100) <= 14) ? $this->pickRandomId($docIds) : null;

			$todoId = null;
			if (isset($userIdSet[$employeeId])) {
				$list = $todoMap[$employeeId] ?? null;
				if (is_array($list) && $list && random_int(1, 100) <= 18) {
					$todoId = (string) $list[random_int(0, count($list) - 1)];
				}
			}

			$steps = $this->randomSteps();
			$results = random_int(1, 100) <= 45 ? $this->randomResults() : null;
			$attachments = random_int(1, 100) <= 25 ? $this->randomAttachments($url) : null;

			$involved = [$employeeId];
			if (random_int(1, 100) <= 35) $involved[] = $candidateId;
			if (random_int(1, 100) <= 10) $involved[] = (string) Str::uuid();

			$attrs = [
				'candidate' => $candidateId,
				'employee'  => $employeeId,
				'date'      => $date,
				'time'      => $time,
				'url'       => $url,
				'location'  => $location,
				AC::COL_RL_TTL => $roleTitle,
				AC::COL_RL_DSC => $roleDesc,
				'comment'   => $comment,
				AC::COL_EMP_RES => $empResp,
				'notes'     => $notes,
				'feedback'  => $feedback,
				AC::COL_TSK_ID => $taskId,
				PJC::COL_PJ_ID => $projectId,
				FC::COL_FM_ID  => $formId,
				'document'  => $docId,
				'todo'      => $todoId,

				// safest across unknown casts: persist JSON as string
				'steps'       => json_encode($steps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'results'     => $results ? json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
				'attachments' => $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
				'involved'    => json_encode(array_values(array_unique($involved)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			];

			// Optional audit columns (avoid SQL error if schema differs)
			if ($hasCreator || $hasUpdater) {
				$creator = isset($userIdSet[$employeeId]) ? $employeeId : $this->pickRandomId($userInterviewerIds);
				$updater = (random_int(1, 100) <= 25) ? $creator : null;

				if ($hasCreator) $attrs[DC::COL_TABLE_CREATOR] = $creator;
				if ($hasUpdater) $attrs[DC::COL_TABLE_UPDATER] = $updater;
			}

			try {
				InterviewSchedule::query()->create($attrs);
				$created++;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' failed creating InterviewSchedule', [
					'error' => $e->getMessage(),
					'candidate' => $candidateId,
					'employee' => $employeeId,
				]);
				continue;
			}

			if ($created >= self::HARD_CAP) break;
		}

		$this->out->writeln(sprintf(
			'<comment>[InterviewScheduleSeeder]</comment> Done. created=%d (min=%d cap=%d)',
			$created,
			self::MIN_ROWS,
			self::HARD_CAP
		));
	}

	/**
	 * Plan rows:
	 * - For every job_app id: 1..8 schedules (best-effort; may truncate at hard cap)
	 * - For every employee/interviewer id: 1..32 schedules (best-effort; may truncate at hard cap)
	 */
	private function buildPlan(array $candidateIds, array $interviewerIds): array
	{
		$plan = [];

		// 1) Candidate-driven (prioritize covering candidates)
		foreach ($candidateIds as $cid) {
			$n = random_int(1, self::MAX_PER_CANDIDATE);
			for ($k = 0; $k < $n; $k++) {
				$plan[] = [(string) $cid, $this->pickRandomId($interviewerIds)];
				if (count($plan) >= self::HARD_CAP) return $plan;
			}
		}

		// 2) Employee-driven
		foreach ($interviewerIds as $eid) {
			$n = random_int(1, self::MAX_PER_EMPLOYEE);
			for ($k = 0; $k < $n; $k++) {
				$plan[] = [$this->pickRandomId($candidateIds), (string) $eid];
				if (count($plan) >= self::HARD_CAP) return $plan;
			}
		}

		return $plan;
	}

	/**
	 * Ensure:
	 * - At least ceil(task_ids * 0.10) distinct task IDs appear at least once (if tasks exist).
	 */
	private function assignTasksToPlan(array $taskIds, int $planSize): array
	{
		$assignments = array_fill(0, max(0, $planSize), null);
		if ($planSize <= 0 || !$taskIds) return $assignments;

		$taskIds = array_values(array_unique(array_values(array_filter($taskIds, fn($v) => is_string($v) && trim($v) !== ''))));
		if (!$taskIds) return $assignments;

		shuffle($taskIds);

		$needDistinct = (int) ceil(count($taskIds) * self::TASK_IDS_MIN_PCT);
		$needDistinct = max(1, $needDistinct);
		$needDistinct = min($needDistinct, $planSize);

		$forced = array_slice($taskIds, 0, $needDistinct);

		// Force distinct usage
		for ($i = 0; $i < $needDistinct; $i++) {
			$assignments[$i] = $forced[$i] ?? null;
		}

		// Opportunistic linking for the remaining rows
		for ($i = $needDistinct; $i < $planSize; $i++) {
			if (random_int(1, 100) <= (int) round(self::TASK_LINK_PCT * 100)) {
				$assignments[$i] = $taskIds[random_int(0, count($taskIds) - 1)] ?? null;
			}
		}

		return $assignments;
	}

	private function pickUniqueDateTime(string $employeeId, string $candidateId, CarbonImmutable $now, array &$usedSlots): array
	{
		for ($t = 0; $t < 24; $t++) {
			$date = $now->addDays(random_int(0, 60))->toDateString();
			$time = $this->randomTimeString();

			$key = $employeeId . '|' . $candidateId . '|' . $date . '|' . $time;
			if (!isset($usedSlots[$key])) {
				$usedSlots[$key] = true;
				return [$date, $time];
			}
		}

		// fallback: accept collision
		$date = $now->addDays(random_int(0, 60))->toDateString();
		$time = $this->randomTimeString();
		return [$date, $time];
	}

	private function fetchEligibleInterviewerUserIds(): array
	{
		// Values expected by UserType::Hr/Admin/SuperAdmin/Company (with tolerance)
		$types = ['hr', 'admin', 'super_admin', 'superadmin', 'super-admin', 'company', 'comapny'];

		try {
			$typeCol = UC::COL_TP;
			$empCol  = UC::COL_EMP_ID;

			$placeholders = implode(',', array_fill(0, count($types), '?'));

			// Prefer users that already have employee_id (matches older invariant), but tolerate if column absent.
			$hasEmpIdCol = $this->hasColumnSafe(DC::TABLE_USERS, $empCol);

			$sql = "select id from " . DC::TABLE_USERS
				. " where lower(" . $typeCol . ") in (" . $placeholders . ")";

			if ($hasEmpIdCol) {
				$sql .= " and " . $empCol . " is not null and length(trim(" . $empCol . ")) > 0";
			}

			$rows = DB::select($sql, array_map(fn($v) => strtolower(trim($v)), $types));

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchEligibleInterviewerUserIds failed: ' . $e->getMessage());
			return [];
		}
	}

	private function fetchEmployeeEntityIds(): array
	{
		// polymorphic tolerance: if employees table exists, include it
		if (!$this->hasTableSafe(DC::TABLE_EMPLOYEES)) return [];

		try {
			$rows = DB::select("select id from " . DC::TABLE_EMPLOYEES);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchEmployeeEntityIds failed: ' . $e->getMessage());
			return [];
		}
	}

	private function fetchIds(string $table, ?int $limit = null): array
	{
		try {
			$sql = "select id from {$table}";
			if (is_int($limit) && $limit > 0) $sql .= " limit {$limit}";

			$rows = DB::select($sql);

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::warning(self::class . " fetchIds failed for {$table}: " . $e->getMessage());
			return [];
		}
	}

	private function fetchIdsIfTableExists(string $table, ?int $limit = null): array
	{
		return $this->hasTableSafe($table) ? $this->fetchIds($table, $limit) : [];
	}

	private function fetchTodoMapForUsers(array $userIds): array
	{
		$userIds = array_values(array_unique(array_values(array_filter($userIds, fn($v) => is_string($v) && trim($v) !== ''))));
		if (!$userIds) return [];
		if (!$this->hasTableSafe(DC::TABLE_USR_TD)) return [];

		$placeholders = implode(',', array_fill(0, count($userIds), '?'));

		try {
			$sql = "select id, " . UC::COL_USER_ID . " as user_id
					from " . DC::TABLE_USR_TD . "
					where " . UC::COL_USER_ID . " in ({$placeholders})";

			$rows = DB::select($sql, $userIds);

			$map = [];
			foreach ($rows as $r) {
				$uid = (string) ($r->user_id ?? '');
				$tid = (string) ($r->id ?? '');
				if ($uid === '' || $tid === '') continue;

				$map[$uid] ??= [];
				$map[$uid][] = $tid;
			}

			foreach ($map as $uid => $list) $map[$uid] = array_values(array_unique($list));
			return $map;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchTodoMapForUsers failed: ' . $e->getMessage());
			return [];
		}
	}

	private function pickRandomId(array $ids): ?string
	{
		$ids = array_values(array_filter($ids, fn($v) => is_string($v) && trim($v) !== ''));
		if (!$ids) return null;
		return (string) $ids[random_int(0, count($ids) - 1)];
	}

	private function randomTimeString(): string
	{
		$hour = random_int(9, 17);
		$minuteOptions = [0, 15, 30, 45];
		$minute = $minuteOptions[random_int(0, count($minuteOptions) - 1)];
		return sprintf('%02d:%02d:00', $hour, $minute);
	}

	private function randomMeetingUrl(): string
	{
		$kind = random_int(1, 3);
		$token = Str::lower(Str::random(10));
		return match ($kind) {
			1 => "https://meet.google.com/{$token}",
			2 => "https://zoom.us/j/{$token}" . random_int(1000, 9999),
			default => "https://teams.microsoft.com/l/meetup-join/{$token}",
		};
	}

	private function randomLocation(): string
	{
		$loc = [
			'Office – Meeting Room A',
			'Office – Meeting Room B',
			'Remote (phone call)',
			'HQ – Reception',
			'Coworking – Room 3',
		];
		return $loc[random_int(0, count($loc) - 1)];
	}

	private function randomRoleTitle(): string
	{
		$roles = [
			'Backend Developer',
			'Frontend Developer',
			'Full Stack Developer',
			'QA Engineer',
			'Product Designer',
			'Project Manager',
			'HR Analyst',
			'DevOps Engineer',
		];
		return $roles[random_int(0, count($roles) - 1)];
	}

	private function randomRoleDescription(string $roleTitle): string
	{
		$desc = [
			"Interview for {$roleTitle}. Focus on experience, communication, and practical problem-solving.",
			"Discuss responsibilities, team fit, and timeline. Validate prior projects and technical fundamentals.",
			"Assess skill depth and work process. Collect expectations, availability, and compensation range.",
		];
		return $desc[random_int(0, count($desc) - 1)];
	}

	private function randomComment(): string
	{
		$c = [
			'Please arrive 10 minutes early.',
			'Bring portfolio or code samples if available.',
			'We will run a short live exercise.',
			'Schedule subject to confirmation.',
		];
		return $c[random_int(0, count($c) - 1)];
	}

	private function randomEmployeeResponse(): ?string
	{
		$r = ['pending', 'accepted', 'declined', 'reschedule_requested', null];
		return $r[random_int(0, count($r) - 1)];
	}

	private function randomNotes(): string
	{
		$n = [
			'Candidate has relevant experience; verify depth in core stack.',
			'Focus on communication and ownership examples.',
			'Validate availability for the next sprint cycle.',
		];
		return $n[random_int(0, count($n) - 1)];
	}

	private function randomFeedback(): string
	{
		$f = [
			'Strong fundamentals; proceed to next stage.',
			'Potential fit; request additional examples.',
			'Not a fit for current role; consider alternate position.',
		];
		return $f[random_int(0, count($f) - 1)];
	}

	private function randomSteps(): array
	{
		$steps = [
			['key' => 'screening', 'label' => 'Screening', 'status' => 'pending'],
			['key' => 'technical', 'label' => 'Technical', 'status' => 'pending'],
			['key' => 'culture', 'label' => 'Culture Fit', 'status' => 'pending'],
			['key' => 'decision', 'label' => 'Decision', 'status' => 'pending'],
		];

		$progress = random_int(0, count($steps) - 1);
		for ($i = 0; $i < $progress; $i++) $steps[$i]['status'] = 'done';

		if ($progress < count($steps))
			$steps[$progress]['status'] = random_int(1, 100) <= 70 ? 'in_progress' : 'pending';

		return $steps;
	}

	private function randomResults(): array
	{
		$score = random_int(40, 98);
		return [
			'score' => $score,
			'recommendation' => $score >= 75 ? 'proceed' : ($score >= 60 ? 'review' : 'reject'),
			'flags' => [
				'communication' => random_int(1, 100) <= 10,
				'portfolio_missing' => random_int(1, 100) <= 15,
				'needs_followup' => random_int(1, 100) <= 25,
			],
		];
	}

	private function randomAttachments(?string $url): array
	{
		$attachments = [
			[
				'type' => 'link',
				'label' => 'Portfolio',
				'url' => 'https://example.com/portfolio/' . Str::lower(Str::random(8)),
			],
			[
				'type' => 'link',
				'label' => 'GitHub',
				'url' => 'https://github.com/' . Str::lower(Str::random(10)),
			],
		];

		if ($url) {
			$attachments[] = [
				'type' => 'link',
				'label' => 'Meeting',
				'url' => $url,
			];
		}

		return $attachments;
	}

	private function hasTableSafe(string $table): bool
	{
		try {
			return Schema::hasTable($table);
		} catch (\Throwable $e) {
			return false;
		}
	}

	private function hasColumnSafe(string $table, string $column): bool
	{
		try {
			return Schema::hasColumn($table, $column);
		} catch (\Throwable $e) {
			return false;
		}
	}
}
