<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\ActivityType;
use App\Models\ActivityLog;
use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ActivityLogSeeder extends Seeder
{
	private const MAX_LOGS_PER_USER = 16;
	private const MIN_LOGS_PER_USER = 1;

	private const MIN_USERS_WITH_LOGS_PCT = 0.25;
	private const MIN_PROJECT_LINK_PCT = 0.05;
	private const MIN_TASK_LINK_PCT = 0.20;
	private const MIN_DEAL_LINK_PCT = 0.02;
	private const MIN_LEAD_LINK_PCT = 0.03;

	private const MIN_PER_ENUM_CASE = 2;

	private const MAX_ATTEMPTS = 25_000;

	private const HARD_CAP_ROWS = 128_000;

	private ConsoleOutput $consoleOut;

	public function run(): void
	{
		$io = $this->makeIo();
		$io->title('Seeding Activity Logs (mock)');

		DB::disableQueryLog();

		$userIds = $this->fetchIds(DC::TABLE_USERS);
		if (!$userIds) {
			$io->warning('No users found. Aborting ActivityLogSeeder.');
			return;
		}

		$projectIds  = $this->fetchIds(DC::TABLE_PROJECTS);
		$taskIds     = $this->fetchIds(DC::TABLE_TASKS);
		$dealIds     = $this->fetchIds(DC::TABLE_DEALS);
		$leadIds     = $this->fetchIds(DC::TABLE_LEADS);
		$contractIds = $this->fetchIds(DC::TABLE_CONTRACTS);
		$docIds      = $this->fetchIds(DC::TABLE_DOCS);

		$io->section('Dependencies snapshot');
		$io->text([
			'users: ' . count($userIds),
			'projects: ' . count($projectIds),
			'tasks: ' . count($taskIds),
			'deals: ' . count($dealIds),
			'leads: ' . count($leadIds),
			'contracts: ' . count($contractIds),
			'documents: ' . count($docIds),
		]);

		$enumCases = ActivityType::cases();
		$forcedLogsByEnum = count($enumCases) * self::MIN_PER_ENUM_CASE;

		if ($forcedLogsByEnum > self::HARD_CAP_ROWS) {
			$io->error('Enum minimum occurrences exceed hard cap of ' . self::HARD_CAP_ROWS . ' rows.');
			return;
		}

		$minUsersWithLogs = (int) ceil(count($userIds) * self::MIN_USERS_WITH_LOGS_PCT);
		$usersNeededForForced = (int) ceil($forcedLogsByEnum / self::MAX_LOGS_PER_USER);

		$seedUsersCount = max($minUsersWithLogs, $usersNeededForForced);
		if ($seedUsersCount > count($userIds))
			$seedUsersCount = count($userIds);

		$seedUserIds = $this->pickRandomDistinct($userIds, $seedUsersCount);

		$perUserCounts = [];
		$rawTotal = 0;

		foreach ($seedUserIds as $uid) {
			$c = $this->sampleInvertedQuadraticCount(self::MIN_LOGS_PER_USER, self::MAX_LOGS_PER_USER);
			$perUserCounts[$uid] = $c;
			$rawTotal += $c;
		}

		$rawTotal = max($rawTotal, $forcedLogsByEnum);
		$targetTotal = $this->roundUpToMultipleOf64($rawTotal);

		if ($targetTotal > self::HARD_CAP_ROWS)
			$targetTotal = self::HARD_CAP_ROWS;

		$currentSum = array_sum($perUserCounts);

		if ($currentSum < $targetTotal)
			$perUserCounts = $this->inflateCountsToTargetTotal($perUserCounts, $targetTotal, $userIds, $io);
		elseif ($currentSum > $targetTotal)
			$perUserCounts = $this->deflateCountsToTargetTotal($perUserCounts, $targetTotal, $io);

		$currentSum = array_sum($perUserCounts);

		if ($currentSum > self::HARD_CAP_ROWS)
			$perUserCounts = $this->deflateCountsToTargetTotal($perUserCounts, self::HARD_CAP_ROWS, $io);

		$currentSum = array_sum($perUserCounts);
		$roundedSum = $currentSum - ($currentSum % 64);

		if ($roundedSum < $forcedLogsByEnum)
			$roundedSum = $this->roundUpToMultipleOf64($forcedLogsByEnum);

		if ($roundedSum > self::HARD_CAP_ROWS)
			$roundedSum = self::HARD_CAP_ROWS;

		if ($roundedSum !== $currentSum) {
			if ($currentSum < $roundedSum)
				$perUserCounts = $this->inflateCountsToTargetTotal($perUserCounts, $roundedSum, $userIds, $io);
			else
				$perUserCounts = $this->deflateCountsToTargetTotal($perUserCounts, $roundedSum, $io);
		}

		$targetTotal = array_sum($perUserCounts);

		if ($targetTotal % 64 !== 0) {
			$targetTotal = $targetTotal - ($targetTotal % 64);
			$perUserCounts = $this->deflateCountsToTargetTotal($perUserCounts, $targetTotal, $io);
			$targetTotal = array_sum($perUserCounts);
		}

		if ($targetTotal > self::HARD_CAP_ROWS) {
			$perUserCounts = $this->deflateCountsToTargetTotal($perUserCounts, self::HARD_CAP_ROWS, $io);
			$targetTotal = array_sum($perUserCounts);
		}

		if ($targetTotal < $forcedLogsByEnum) {
			$io->error("Cannot satisfy: total logs {$targetTotal} < forced logs by enum {$forcedLogsByEnum}. Aborting.");
			return;
		}

		$io->section('Plan');
		$io->text([
			'users with logs: ' . count($perUserCounts) . ' / ' . count($userIds) . ' (min 25% = ' . $minUsersWithLogs . ')',
			'forced logs by enum: ' . $forcedLogsByEnum . ' (' . self::MIN_PER_ENUM_CASE . ' each case)',
			'total logs (final): ' . $targetTotal . ' (must be 64×N, hard cap ' . self::HARD_CAP_ROWS . ')',
			'avg logs/user: ' . number_format($targetTotal / max(1, count($perUserCounts)), 2),
		]);

		$needProject = (int) ceil($targetTotal * self::MIN_PROJECT_LINK_PCT);
		$needTask    = (int) ceil($targetTotal * self::MIN_TASK_LINK_PCT);
		$needDeal    = (int) ceil($targetTotal * self::MIN_DEAL_LINK_PCT);
		$needLead    = (int) ceil($targetTotal * self::MIN_LEAD_LINK_PCT);

		if (!$projectIds && $needProject > 0) $io->warning('No projects found; cannot meet minimum project_id linking %.');
		if (!$taskIds && $needTask > 0) $io->warning('No tasks found; cannot meet minimum task_id linking %.');
		if (!$dealIds && $needDeal > 0) $io->warning('No deals found; cannot meet minimum deal_id linking %.');
		if (!$leadIds && $needLead > 0) $io->warning('No leads found; cannot meet minimum lead_id linking %.');

		$projectIdx = $projectIds ? $this->pickIndexSet($targetTotal, $needProject) : [];
		$taskIdx    = $taskIds ? $this->pickIndexSet($targetTotal, $needTask) : [];
		$dealIdx    = $dealIds ? $this->pickIndexSet($targetTotal, $needDeal) : [];
		$leadIdx    = $leadIds ? $this->pickIndexSet($targetTotal, $needLead) : [];

		$requiredTypes = [];
		foreach ($enumCases as $case) {
			for ($i = 0; $i < self::MIN_PER_ENUM_CASE; $i++)
				$requiredTypes[] = $case->value;
		}
		shuffle($requiredTypes);

		$allTypes = array_values(array_map(static fn(ActivityType $c) => $c->value, $enumCases));

		$faker = FakerFactory::create();
		$now = CarbonImmutable::now();

		$this->safeClearActivityLogs($io);

		$io->section('Seeding');
		$io->progressStart($targetTotal);

		$stats = [
			'created' => 0,
			'project_linked' => 0,
			'task_linked' => 0,
			'deal_linked' => 0,
			'lead_linked' => 0,
			'per_type' => [],
		];

		$globalIndex = 0;

		foreach ($perUserCounts as $userId => $count) {
			for ($i = 0; $i < $count; $i++) {
				$rawType = $requiredTypes ? array_pop($requiredTypes) : $allTypes[random_int(0, count($allTypes) - 1)];
				$type = ActivityType::normalize($rawType) ?? ActivityType::Other;

				$attrs = $this->buildActivityLogAttributes(
					userId: (string) $userId,
					type: $type,
					faker: $faker,
					now: $now,
					globalIndex: $globalIndex,
					projectIdx: $projectIdx,
					taskIdx: $taskIdx,
					dealIdx: $dealIdx,
					leadIdx: $leadIdx,
					projectIds: $projectIds,
					taskIds: $taskIds,
					dealIds: $dealIds,
					leadIds: $leadIds,
					contractIds: $contractIds,
					docIds: $docIds
				);

				$created = $this->createActivityLogWithRetry($attrs, $io);
				if ($created) {
					$stats['created']++;

					$stats['per_type'][$type->value] = (int) ($stats['per_type'][$type->value] ?? 0) + 1;

					if (!is_null($attrs[PJC::COL_PJ_ID] ?? null)) $stats['project_linked']++;
					if (!is_null($attrs[AC::COL_TSK_ID] ?? null)) $stats['task_linked']++;
					if (!is_null($attrs[AC::COL_DL] ?? null)) $stats['deal_linked']++;
					if (!is_null($attrs[PJC::COL_LD_ID] ?? null)) $stats['lead_linked']++;
				}

				$globalIndex++;
				$io->progressAdvance();
			}
		}

		$io->progressFinish();

		$io->section('Validation');
		$missingTypes = [];
		foreach ($enumCases as $case) {
			$c = (int) ($stats['per_type'][$case->value] ?? 0);
			if ($c < self::MIN_PER_ENUM_CASE)
				$missingTypes[] = $case->value . " ({$c})";
		}

		$io->text([
			'created: ' . $stats['created'] . ' (planned ' . $targetTotal . ')',
			'project linked: ' . $stats['project_linked'] . ' (min ' . ($projectIds ? $needProject : 0) . ')',
			'task linked: ' . $stats['task_linked'] . ' (min ' . ($taskIds ? $needTask : 0) . ')',
			'deal linked: ' . $stats['deal_linked'] . ' (min ' . ($dealIds ? $needDeal : 0) . ')',
			'lead linked: ' . $stats['lead_linked'] . ' (min ' . ($leadIds ? $needLead : 0) . ')',
		]);

		if ($missingTypes)
			$io->warning('Some ActivityType cases did not reach the minimum occurrences (likely due to persistent create failures): ' . implode(', ', $missingTypes));
		else
			$io->success('Activity logs seeded successfully.');
	}

	private function makeIo(): SymfonyStyle
	{
		$this->consoleOut = new ConsoleOutput();
		return new SymfonyStyle(new ArgvInput(), $this->consoleOut);
	}

	private function safeClearActivityLogs(SymfonyStyle $io): void
	{
		try {
			DB::table(DC::TABLE_ACT_LOG)->delete();
			$io->text('Cleared existing activity logs.');
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed to clear activity_logs: ' . $e->getMessage());
			$io->warning('Failed to clear existing activity logs (continuing).');
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

	private function sampleInvertedQuadraticCount(int $min, int $max): int
	{
		$u = lcg_value();
		$skew = $u * $u;
		$span = $max - $min;
		$v = $min + (int) floor($skew * ($span + 1));
		if ($v < $min) return $min;
		if ($v > $max) return $max;
		return $v;
	}

	private function roundUpToMultipleOf64(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}

	private function pickRandomDistinct(array $values, int $count): array
	{
		$values = array_values($values);
		if ($count <= 0) return [];
		if ($count >= count($values)) return $values;

		shuffle($values);
		return array_slice($values, 0, $count);
	}

	private function inflateCountsToTargetTotal(array $counts, int $target, array $allUserIds, SymfonyStyle $io): array
	{
		$sum = array_sum($counts);
		if ($sum >= $target) return $counts;

		$allUserIds = array_values($allUserIds);
		$remainingUsers = array_values(array_diff($allUserIds, array_keys($counts)));

		$attempts = 0;

		while ($sum < $target && $attempts < self::MAX_ATTEMPTS) {
			$attempts++;

			$eligible = [];
			foreach ($counts as $uid => $c)
				if ($c < self::MAX_LOGS_PER_USER)
					$eligible[] = $uid;

			if ($eligible) {
				$uid = $eligible[random_int(0, count($eligible) - 1)];
				$counts[$uid] = (int) $counts[$uid] + 1;
				$sum++;
				continue;
			}

			if ($remainingUsers) {
				$uid = array_pop($remainingUsers);
				$counts[$uid] = self::MIN_LOGS_PER_USER;
				$sum++;
				continue;
			}

			$io->warning('Cannot inflate counts further: no remaining users and all users reached max logs.');
			break;
		}

		if ($attempts >= self::MAX_ATTEMPTS)
			$io->warning('Inflation loop hit MAX_ATTEMPTS; totals may be below target.');

		return $counts;
	}

	private function deflateCountsToTargetTotal(array $counts, int $target, SymfonyStyle $io): array
	{
		$sum = array_sum($counts);
		if ($sum <= $target) return $counts;

		$attempts = 0;

		while ($sum > $target && $attempts < self::MAX_ATTEMPTS) {
			$attempts++;

			$eligible = [];
			foreach ($counts as $uid => $c)
				if ($c > self::MIN_LOGS_PER_USER)
					$eligible[] = $uid;

			if (!$eligible) {
				$io->warning('Cannot deflate counts further: all users are at MIN_LOGS_PER_USER.');
				break;
			}

			$uid = $eligible[random_int(0, count($eligible) - 1)];
			$counts[$uid] = (int) $counts[$uid] - 1;
			$sum--;
		}

		if ($attempts >= self::MAX_ATTEMPTS)
			$io->warning('Deflation loop hit MAX_ATTEMPTS; totals may be above target.');

		return $counts;
	}

	private function pickIndexSet(int $total, int $needed): array
	{
		if ($needed <= 0 || $total <= 0) return [];
		if ($needed > $total) $needed = $total;

		$idx = range(0, $total - 1);
		shuffle($idx);
		$pick = array_slice($idx, 0, $needed);

		$out = [];
		foreach ($pick as $i)
			$out[(int) $i] = true;

		return $out;
	}

	private function pickRandomId(array $ids): ?string
	{
		if (!$ids) return null;
		return (string) $ids[random_int(0, count($ids) - 1)];
	}

	private function buildActivityLogAttributes(
		string $userId,
		ActivityType $type,
		Faker $faker,
		CarbonImmutable $now,
		int $globalIndex,
		array $projectIdx,
		array $taskIdx,
		array $dealIdx,
		array $leadIdx,
		array $projectIds,
		array $taskIds,
		array $dealIds,
		array $leadIds,
		array $contractIds,
		array $docIds
	): array {
		$projectId = array_key_exists($globalIndex, $projectIdx) ? $this->pickRandomId($projectIds) : null;
		$taskId    = array_key_exists($globalIndex, $taskIdx) ? $this->pickRandomId($taskIds) : null;
		$dealId    = array_key_exists($globalIndex, $dealIdx) ? $this->pickRandomId($dealIds) : null;
		$leadId    = array_key_exists($globalIndex, $leadIdx) ? $this->pickRandomId($leadIds) : null;

		$entity = (string) ($type->getEntity() ?? '');
		if ($entity !== '' && $taskIds && str_contains($entity, 'task') && $taskId === null && random_int(1, 100) <= 45)
			$taskId = $this->pickRandomId($taskIds);
		if ($entity !== '' && $dealIds && str_contains($entity, 'deal') && $dealId === null && random_int(1, 100) <= 25)
			$dealId = $this->pickRandomId($dealIds);
		if ($entity !== '' && $leadIds && str_contains($entity, 'lead') && $leadId === null && random_int(1, 100) <= 25)
			$leadId = $this->pickRandomId($leadIds);
		if ($entity !== '' && $projectIds && str_contains($entity, 'project') && $projectId === null && random_int(1, 100) <= 30)
			$projectId = $this->pickRandomId($projectIds);

		$contractId = null;
		if ($contractIds && random_int(1, 100) <= 5)
			$contractId = $this->pickRandomId($contractIds);

		$documentId = null;
		if ($docIds && random_int(1, 100) <= 6)
			$documentId = $this->pickRandomId($docIds);

		$taskFile = null;
		$leadFile = null;
		$dealFile = null;

		if ($type->getAction() === 'upload' || str_contains($type->value, '_attachment') || str_contains($type->value, '_file')) {
			if ($docIds && random_int(1, 100) <= 55) {
				$docPick = $this->pickRandomId($docIds);
				if ($docPick !== null) {
					if ($taskId !== null) $taskFile = $docPick;
					elseif ($leadId !== null) $leadFile = $docPick;
					elseif ($dealId !== null) $dealFile = $docPick;
				}
			} else {
				if ($taskId !== null) $taskFile = (string) Str::uuid();
				elseif ($leadId !== null) $leadFile = (string) Str::uuid();
				elseif ($dealId !== null) $dealFile = (string) Str::uuid();
			}
		}

		$metadata = $this->buildMetadata($type, $faker);
		$remarkFallback = Str::headline($type->value);

		$timestamp = random_int(1, 100) <= 92
			? $now->subMinutes(random_int(0, 60 * 24 * 30))
			: null;

		$failedAt = null;
		$failedReason = null;
		$retryCount = 0;
		$lastRetryAt = null;
		$errorLog = null;

		if (random_int(1, 100) <= 3) {
			$failedAt = $now->subMinutes(random_int(0, 60 * 24 * 10));
			$failedReason = $faker->sentence(8);
			$retryCount = random_int(0, 5);
			$lastRetryAt = $retryCount > 0 ? $now->subMinutes(random_int(0, 60 * 24 * 2)) : null;
			$errorLog = [
				'code' => 'MOCK_ERR',
				'message' => $faker->sentence(10),
				'at' => $failedAt?->toIso8601String(),
			];
		}

		return [
			UC::COL_USER_ID => $userId,

			PJC::COL_PJ_ID => $projectId,
			PJC::COL_CTC_ID => $contractId,

			PJC::COL_LD_ID => $leadId,
			AC::COL_TSK_ID => $taskId,
			AC::COL_DL => $dealId,

			'document' => $documentId,
			AC::COL_TSK_FL => $taskFile,
			AC::COL_LD_FL => $leadFile,
			AC::COL_DL_FL => $dealFile,

			AC::COL_LOG_TP => $type->value,
			'remark' => $remarkFallback,
			'timestamp' => $timestamp,
			'metadata' => $metadata,

			DC::COL_FL_AT => $failedAt,
			DC::COL_FLD_RS => $failedReason,
			DC::COL_RTR_CT => $retryCount,
			DC::COL_LST_RTR_AT => $lastRetryAt,
			DC::COL_ER_LG => $errorLog,
		];
	}

	private function buildMetadata(ActivityType $type, Faker $faker): array
	{
		$stages = ['backlog', 'todo', 'in_progress', 'review', 'done', 'blocked', 'on_hold'];
		$statuses = ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost', 'archived'];

		$title = Str::headline($faker->words(random_int(2, 4), true));

		return match ($type) {
			ActivityType::InviteUser => [
				'action' => $type->getAction(),
				'entity' => $type->getEntity(),
				'title' => $faker->name(),
			],

			ActivityType::UserAssignedToTask,
			ActivityType::UserRemovedFromTask => [
				'action' => $type->getAction(),
				'entity' => $type->getEntity(),
				'task_name' => Str::headline($faker->words(random_int(2, 4), true)),
				'member_name' => $faker->name(),
			],

			ActivityType::MoveTask,
			ActivityType::MoveLeadStage => [
				'action' => $type->getAction(),
				'entity' => $type->getEntity(),
				'title' => $title,
				'old_stage' => $stages[random_int(0, count($stages) - 1)],
				'new_stage' => $stages[random_int(0, count($stages) - 1)],
			],

			default => (static function () use ($type, $faker, $title, $statuses) {
				$action = (string) ($type->getAction() ?? '');
				$entity = (string) ($type->getEntity() ?? '');

				$payload = [
					'action' => $action,
					'entity' => $entity,
					'title' => $title,
					'note' => $faker->sentence(10),
				];

				if ($action === 'upload' || str_contains($type->value, '_file') || str_contains($type->value, '_attachment'))
					$payload['file_name'] = Str::headline($faker->words(random_int(2, 4), true)) . '.' . $faker->fileExtension();

				if ($action === 'move') {
					$payload['old_status'] = $statuses[random_int(0, count($statuses) - 1)];
					$payload['new_status'] = $statuses[random_int(0, count($statuses) - 1)];
				}

				return $payload;
			})(),
		};
	}

	private function renderVariationLine(array $attrs): string
	{
		$userId = (string) ($attrs[UC::COL_USER_ID] ?? '');
		$type = (string) ($attrs[AC::COL_LOG_TP] ?? '');

		$shortUser = $userId !== '' ? (Str::length($userId) > 10 ? (Str::substr($userId, 0, 8) . '…') : $userId) : '-';

		$pj = !is_null($attrs[PJC::COL_PJ_ID] ?? null) ? 'PJ' : '-';
		$ctc = !is_null($attrs[PJC::COL_CTC_ID] ?? null) ? 'CTC' : '-';
		$ld = !is_null($attrs[PJC::COL_LD_ID] ?? null) ? 'LD' : '-';
		$tsk = !is_null($attrs[AC::COL_TSK_ID] ?? null) ? 'TSK' : '-';
		$dl = !is_null($attrs[AC::COL_DL] ?? null) ? 'DL' : '-';
		$doc = !is_null($attrs['document'] ?? null) ? 'DOC' : '-';

		$flags = implode(',', [$pj, $ctc, $ld, $tsk, $dl, $doc]);

		return "[actlog] user={$shortUser} type={$type} links={$flags}";
	}

	private function createActivityLogWithRetry(array $attrs, SymfonyStyle $io): bool
	{
		$attempts = 0;

		while ($attempts < 3) {
			$attempts++;

			try {
				$log = new ActivityLog();
				$log->forceFill($attrs);

				$this->consoleOut->writeln(
					$this->renderVariationLine($attrs),
					OutputInterface::VERBOSITY_VERBOSE
				);

				$log->save();
				return true;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' ActivityLog create failed (attempt ' . $attempts . '): ' . $e->getMessage(), [
					'user_id' => (string) ($attrs[UC::COL_USER_ID] ?? ''),
					'log_type' => (string) ($attrs[AC::COL_LOG_TP] ?? ''),
				]);

				if ($attempts === 1) {
					$attrs[PJC::COL_PJ_ID] = null;
					$attrs[PJC::COL_CTC_ID] = null;
					$attrs[PJC::COL_LD_ID] = null;
					$attrs[AC::COL_TSK_ID] = null;
					$attrs[AC::COL_DL] = null;
					$attrs['document'] = null;
					$attrs[AC::COL_TSK_FL] = null;
					$attrs[AC::COL_LD_FL] = null;
					$attrs[AC::COL_DL_FL] = null;
					continue;
				}

				if ($attempts === 2) {
					$attrs['metadata'] = [
						'action' => 'other',
						'entity' => '',
						'title' => 'Fallback',
						'note' => 'Seeder fallback after create failure',
					];
					$attrs['remark'] = (string) ($attrs['remark'] ?? 'Fallback');
					continue;
				}
			}
		}

		$io->warning('Persistent ActivityLog create failure; see logs.');
		return false;
	}
}
