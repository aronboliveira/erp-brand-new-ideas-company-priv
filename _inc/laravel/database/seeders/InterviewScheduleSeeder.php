<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\InterviewSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InterviewScheduleSeeder extends Seeder
{
	private const HARD_CAP_ROWS = 800;

	private const HR_PCT = 0.20;
	private const ADMIN_PCT = 0.10;

	private const MAX_ATTEMPTS = 2_500;

	private ConsoleOutput $consoleOut;

	public function run(): void
	{
		$io = $this->makeIo();
		$io->title('Seeding Interview Schedules (mock)');

		DB::disableQueryLog();

		$hrEmployeeUserIds = $this->fetchEmployeeEligibleUserIdsByType('hr');
		$adminEmployeeUserIds = $this->fetchEmployeeEligibleUserIdsByType('admin');

		if (!$hrEmployeeUserIds && !$adminEmployeeUserIds) {
			$io->warning('No eligible HR/Admin users with employee_id found. Aborting.');
			return;
		}

		$hrPick = (int) ceil(count($hrEmployeeUserIds) * self::HR_PCT);
		$adminPick = (int) ceil(count($adminEmployeeUserIds) * self::ADMIN_PCT);

		$employees = array_merge(
			$this->pickRandomDistinct($hrEmployeeUserIds, $hrPick),
			$this->pickRandomDistinct($adminEmployeeUserIds, $adminPick),
		);

		$employees = array_values(array_unique($employees));

		if (!$employees) {
			$io->warning('Selection resulted in 0 employees. Aborting.');
			return;
		}

		if (count($employees) > self::HARD_CAP_ROWS)
			$employees = array_slice($employees, 0, self::HARD_CAP_ROWS);

		$candidateIds = $this->fetchIds(DC::TABLE_JOB_APPS);
		if (!$candidateIds) {
			$io->warning('No job applications (candidates) found. Aborting.');
			return;
		}

		$projectIds = $this->fetchIds(DC::TABLE_PROJECTS);
		$taskIds = $this->fetchIds(DC::TABLE_TASKS);
		$docIds = $this->fetchIds(DC::TABLE_DOCS);

		$todoMap = $this->fetchTodoMapForUsers($employees);

		$io->section('Plan');
		$io->text([
			'eligible hr users: ' . count($hrEmployeeUserIds) . ' -> pick ' . $hrPick,
			'eligible admin users: ' . count($adminEmployeeUserIds) . ' -> pick ' . $adminPick,
			'employees selected (distinct): ' . count($employees) . ' (hard cap ' . self::HARD_CAP_ROWS . ')',
			'candidates available: ' . count($candidateIds),
			'projects/tasks/docs: ' . count($projectIds) . '/' . count($taskIds) . '/' . count($docIds),
			'todos mapped for employees: ' . count($todoMap),
		]);

		$io->section('Seeding');
		$io->progressStart(count($employees));

		$created = 0;
		$now = CarbonImmutable::now();

		foreach ($employees as $employeeId) {
			$attrs = $this->buildScheduleAttrs(
				employeeId: (string) $employeeId,
				candidateIds: $candidateIds,
				projectIds: $projectIds,
				taskIds: $taskIds,
				docIds: $docIds,
				todoMap: $todoMap,
				now: $now
			);

			$this->consoleOut->writeln(
				$this->renderVariationLine($attrs),
				OutputInterface::VERBOSITY_NORMAL
			);

			if ($this->createScheduleWithRetry($attrs))
				$created++;

			$io->progressAdvance();
		}

		$io->progressFinish();
		$io->success("Interview schedules created: {$created}");
	}

	private function makeIo(): SymfonyStyle
	{
		$this->consoleOut = new ConsoleOutput();
		return new SymfonyStyle(new ArgvInput(), $this->consoleOut);
	}

	private function fetchEmployeeEligibleUserIdsByType(string $type): array
	{
		$type = strtolower(trim($type));
		if ($type === '')
			return [];

		try {
			$sql = "select id
					from " . DC::TABLE_USERS . "
					where lower(" . UC::COL_TP . ") = ?
					  and " . UC::COL_EMP_ID . " is not null
					  and length(trim(" . UC::COL_EMP_ID . ")) > 0";

			$rows = DB::select($sql, [$type]);

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return array_values($out);
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchEmployeeEligibleUserIdsByType failed: ' . $e->getMessage(), [
				'type' => $type,
			]);
			return [];
		}
	}

	private function fetchIds(string $table): array
	{
		try {
			$rows = DB::select("select id from {$table}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return array_values($out);
		} catch (\Throwable $e) {
			Log::warning(self::class . " fetchIds failed for {$table}: " . $e->getMessage());
			return [];
		}
	}

	private function fetchTodoMapForUsers(array $userIds): array
	{
		$userIds = array_values(array_unique(array_values(array_filter($userIds, fn($v) => is_string($v) && trim($v) !== ''))));
		if (!$userIds)
			return [];

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
				if ($uid === '' || $tid === '')
					continue;
				$map[$uid] ??= [];
				$map[$uid][] = $tid;
			}

			foreach ($map as $uid => $list)
				$map[$uid] = array_values(array_unique($list));

			return $map;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' fetchTodoMapForUsers failed: ' . $e->getMessage(), [
				'users' => count($userIds),
			]);
			return [];
		}
	}

	private function pickRandomDistinct(array $values, int $count): array
	{
		$values = array_values($values);
		if ($count <= 0)
			return [];
		if ($count >= count($values))
			return $values;

		shuffle($values);
		return array_slice($values, 0, $count);
	}

	private function pickRandomId(array $ids): ?string
	{
		if (!$ids)
			return null;
		return (string) $ids[random_int(0, count($ids) - 1)];
	}

	private function buildScheduleAttrs(
		string $employeeId,
		array $candidateIds,
		array $projectIds,
		array $taskIds,
		array $docIds,
		array $todoMap,
		CarbonImmutable $now
	): array {
		$attempts = 0;

		$candidateId = null;
		$date = null;
		$time = null;

		do {
			$attempts++;

			$candidateId = $this->pickRandomId($candidateIds);
			$date = $now->addDays(random_int(0, 60))->toDateString();
			$time = $this->randomTimeString();

			$exists = $this->scheduleExists($employeeId, (string) $candidateId, $date, $time);
			if (!$exists)
				break;
		} while ($attempts < self::MAX_ATTEMPTS);

		if ($attempts >= self::MAX_ATTEMPTS)
			Log::warning(self::class . ' uniqueness loop hit MAX_ATTEMPTS; proceeding with last pick', [
				'employee' => $employeeId,
			]);

		$isOnline = random_int(1, 100) <= 55;

		$url = $isOnline ? $this->randomMeetingUrl() : null;
		$location = $isOnline ? null : $this->randomLocation();

		$roleTitle = $this->randomRoleTitle();
		$roleDesc = random_int(1, 100) <= 70 ? $this->randomRoleDescription($roleTitle) : null;

		$comment = random_int(1, 100) <= 60 ? $this->randomComment() : null;
		$employeeResponse = $this->randomEmployeeResponse();

		$notes = random_int(1, 100) <= 45 ? $this->randomNotes() : null;
		$feedback = random_int(1, 100) <= 35 ? $this->randomFeedback() : null;

		$taskId = $taskIds && random_int(1, 100) <= 22 ? $this->pickRandomId($taskIds) : null;
		$projectId = $projectIds && random_int(1, 100) <= 12 ? $this->pickRandomId($projectIds) : null;
		$documentId = $docIds && random_int(1, 100) <= 14 ? $this->pickRandomId($docIds) : null;

		$todoId = null;
		$todoList = $todoMap[$employeeId] ?? null;
		if (is_array($todoList) && $todoList && random_int(1, 100) <= 18)
			$todoId = (string) $todoList[random_int(0, count($todoList) - 1)];

		$steps = $this->randomSteps();
		$results = random_int(1, 100) <= 45 ? $this->randomResults() : null;
		$attachments = random_int(1, 100) <= 25 ? $this->randomAttachments($url) : null;

		$involved = [
			$employeeId,
		];

		if (random_int(1, 100) <= 20)
			$involved[] = (string) Str::uuid();

		return [
			'candidate' => (string) $candidateId,
			'employee' => $employeeId,
			'date' => $date,
			'time' => $time,
			'url' => $url,
			'location' => $location,
			AC::COL_RL_TTL => $roleTitle,
			AC::COL_RL_DSC => $roleDesc,
			'comment' => $comment,
			AC::COL_EMP_RES => $employeeResponse,
			'notes' => $notes,
			'feedback' => $feedback,
			AC::COL_TSK_ID => $taskId,
			PJC::COL_PJ_ID => $projectId,
			'document' => $documentId,
			'todo' => $todoId,
			'steps' => $steps,
			'results' => $results,
			'attachments' => $attachments,
			'involved' => $involved,
		];
	}

	private function scheduleExists(string $employeeId, string $candidateId, string $date, string $time): bool
	{
		if ($employeeId === '' || $candidateId === '' || $date === '' || $time === '')
			return false;

		try {
			$sql = "select 1
					from " . DC::TABLE_ITV_SCD . "
					where employee = ?
					  and candidate = ?
					  and date = ?
					  and time = ?
					limit 1";

			$row = DB::selectOne($sql, [$employeeId, $candidateId, $date, $time]);
			return (bool) $row;
		} catch (\Throwable $e) {
			Log::debug(self::class . ' scheduleExists failed: ' . $e->getMessage(), [
				'employee' => $employeeId,
			]);
			return false;
		}
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
		for ($i = 0; $i < $progress; $i++)
			$steps[$i]['status'] = 'done';

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

		if ($url)
			$attachments[] = [
				'type' => 'link',
				'label' => 'Meeting',
				'url' => $url,
			];

		return $attachments;
	}

	private function renderVariationLine(array $attrs): string
	{
		$emp = (string) ($attrs['employee'] ?? '');
		$cand = (string) ($attrs['candidate'] ?? '');
		$date = (string) ($attrs['date'] ?? '');
		$time = (string) ($attrs['time'] ?? '');

		$shortEmp = $emp !== '' ? (Str::length($emp) > 10 ? (Str::substr($emp, 0, 8) . '…') : $emp) : '-';
		$shortCand = $cand !== '' ? (Str::length($cand) > 10 ? (Str::substr($cand, 0, 8) . '…') : $cand) : '-';

		$flags = [
			!is_null($attrs[AC::COL_TSK_ID] ?? null) ? 'TSK' : '-',
			!is_null($attrs[PJC::COL_PJ_ID] ?? null) ? 'PJ' : '-',
			!is_null($attrs['document'] ?? null) ? 'DOC' : '-',
			!is_null($attrs['todo'] ?? null) ? 'TODO' : '-',
			trim((string) ($attrs['url'] ?? '')) !== '' ? 'URL' : '-',
			trim((string) ($attrs['location'] ?? '')) !== '' ? 'LOC' : '-',
		];

		return "[itv] emp={$shortEmp} cand={$shortCand} at={$date} {$time} links=" . implode(',', $flags);
	}

	private function createScheduleWithRetry(array $attrs): bool
	{
		$attempts = 0;

		while ($attempts < 3) {
			$attempts++;

			try {
				$model = new InterviewSchedule();
				$model->forceFill($attrs);
				$model->save();
				return true;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' InterviewSchedule create failed (attempt ' . $attempts . '): ' . $e->getMessage(), [
					'employee' => (string) ($attrs['employee'] ?? ''),
					'candidate' => (string) ($attrs['candidate'] ?? ''),
					'date' => (string) ($attrs['date'] ?? ''),
					'time' => (string) ($attrs['time'] ?? ''),
				]);

				if ($attempts === 1) {
					$attrs['todo'] = null;
					$attrs[AC::COL_TSK_ID] = null;
					continue;
				}

				if ($attempts === 2) {
					$attrs['steps'] = [
						['key' => 'screening', 'label' => 'Screening', 'status' => 'pending'],
					];
					$attrs['results'] = null;
					$attrs['attachments'] = null;
					$attrs['involved'] = [(string) ($attrs['employee'] ?? '')];
					continue;
				}
			}
		}

		return false;
	}
}
