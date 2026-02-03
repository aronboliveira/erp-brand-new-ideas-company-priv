<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\PriorityLevel;
use App\Models\UserToDo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserToDoSeeder extends Seeder
{
	private ?ConsoleOutput $output = null;

	/** @var array<int, string> */
	private array $allProjectIds = [];

	private ?string $projectInvolvedColumn = null;

	private bool $hasNotificationsTable = false;
	private bool $hasNotificationsUserCol = false;

	private const SECONDS_LIMIT = 2 * 10 ** 2; // 3 minutos
	private const HARD_CAP = 800;

	/**
	 * @var array{notifications_by_user: array<string, array<int, string>>}
	 */
	private static array $cache = [
		'notifications_by_user' => [],
	];


	public function run(): void
	{
		$this->seedUserToDos();
	}

	protected function seedUserToDos(): void
	{
		try {
			$clock = microtime(true);
			$counter = 0;
			$this->out()->writeln('<info>[UserToDoSeeder]</info> Starting seeding user_to_dos...');

			$this->projectInvolvedColumn = $this->resolveProjectInvolvedColumn();
			$this->allProjectIds = $this->fetchAllProjectIdsSafe();
			if (empty($this->allProjectIds)) {
				Log::warning(static::class . ' skipping: no projects found');
				$this->out()->writeln('<comment>[UserToDoSeeder]</comment> Skipping: no projects found.');
				return;
			}

			$this->hasNotificationsTable = $this->safeHasTable(DC::TABLE_NTF);
			$this->hasNotificationsUserCol = $this->hasNotificationsTable
				? $this->safeHasColumn(DC::TABLE_NTF, UC::COL_USER_ID)
				: false;

			$eligibleOwners = $this->fetchEligibleOwnerUserIds();
			$uniqueOwners = array_values(array_unique($eligibleOwners));
			if (empty($uniqueOwners)) {
				Log::warning(static::class . ' skipping: no eligible users found');
				$this->out()->writeln('<comment>[UserToDoSeeder]</comment> Skipping: no eligible users found.');
				return;
			}

			$ownerLoopCount = (int) floor(count($uniqueOwners) * 0.2);
			if ($ownerLoopCount <= 0) $ownerLoopCount = 1;

			$ownersSample = $this->sampleArray($uniqueOwners, min($ownerLoopCount, count($uniqueOwners)));

			$assignerIds = $this->fetchAssignableUserIds(); // assigned_by candidates
			$cap = $this->resolveCap();
			$created = 0;

			foreach ($ownersSample as $ownerUserId) {
				if (!is_string($ownerUserId) || trim($ownerUserId) === '') continue;
				if ($cap > 0 && $created >= $cap) break;

				$projectIdsForUser = $this->resolveProjectsForUser($ownerUserId);

				foreach ($projectIdsForUser as $projectId) {
					if ($cap > 0 && $created >= $cap) break 2;

					$taskIds = $this->fetchTaskIdsForProject($projectId);
					$milestoneIds = $this->fetchMilestoneIdsForProject($projectId);

					// foreach (task in the project); if none, then add more 2 to 8 iterations
					if (!empty($taskIds)) {
						foreach ($taskIds as $taskId) {
							if (microtime(true) - $clock > self::SECONDS_LIMIT) {
								$this->out()->writeln('[UserToDoSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
								$this->command?->warn('[UserToDoSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
								return;
							}
							if ($counter > self::HARD_CAP) break 3;
							$counter += 1;
							if ($cap > 0 && $created >= $cap) break 3;
							$this->createToDo(
								ownerUserId: $ownerUserId,
								projectId: $projectId,
								taskId: $taskId,
								milestoneId: !empty($milestoneIds) && random_int(0, 100) < 40
									? $milestoneIds[array_rand($milestoneIds)]
									: null,
								assignerIds: $assignerIds
							);
							$created++;
						}
					} else {
						$iters = random_int(0, 4);
						for ($i = 0; $i < $iters; $i++) {
							if ($counter > self::HARD_CAP) break 3;
							$counter += 1;
							if ($cap > 0 && $created >= $cap) break 3;
							$this->createToDo(
								ownerUserId: $ownerUserId,
								projectId: $projectId,
								taskId: null,
								milestoneId: !empty($milestoneIds) && random_int(0, 100) < 40
									? $milestoneIds[array_rand($milestoneIds)]
									: null,
								assignerIds: $assignerIds
							);
							$created++;
						}
					}

					// foreach (milestone in the project); if none, then add more 1 to 4 iterations
					if (!empty($milestoneIds)) {
						foreach ($milestoneIds as $milestoneId) {
							if ($cap > 0 && $created >= $cap) break 3;
							if ($counter > self::HARD_CAP) break 3;
							$counter += 1;
							$this->createToDo(
								ownerUserId: $ownerUserId,
								projectId: $projectId,
								taskId: !empty($taskIds) && random_int(0, 100) < 40
									? $taskIds[array_rand($taskIds)]
									: null,
								milestoneId: $milestoneId,
								assignerIds: $assignerIds
							);
							$created++;
						}
					} else {
						$iters = random_int(1, 4);
						for ($i = 0; $i < $iters; $i++) {
							if ($cap > 0 && $created >= $cap) break 3;
							if ($counter > self::HARD_CAP) break 3;
							$counter += 1;
							$this->createToDo(
								ownerUserId: $ownerUserId,
								projectId: $projectId,
								taskId: !empty($taskIds) && random_int(0, 100) < 40
									? $taskIds[array_rand($taskIds)]
									: null,
								milestoneId: null,
								assignerIds: $assignerIds
							);
							$created++;
						}
					}
				}
			}

			Log::info(static::class . ' seeded user_to_dos', [
				'created' => $created,
				'cap' => $cap,
				'owners_sampled' => count($ownersSample),
				'has_notifications' => $this->hasNotificationsUserCol,
			]);

			$this->out()->writeln("<info>[UserToDoSeeder]</info> Done. Created: {$created}" . ($cap > 0 ? " (cap={$cap})" : ''));
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed seeding user_to_dos', ['error' => $e->getMessage()]);
			$this->out()->writeln('<error>[UserToDoSeeder]</error> Failed: ' . $e->getMessage());
		}
	}

	private function out(): ConsoleOutput
	{
		return $this->output ??= new ConsoleOutput();
	}

	private function resolveCap(): int
	{
		$cmd = $this->command;
		if ($cmd && method_exists($cmd, 'hasOption') && $cmd->hasOption('count')) {
			$v = (int) $cmd->option('count');
			if ($v > 0) return $v;
		}
		return 0;
	}

	private function safeHasTable(string $table): bool
	{
		try {
			return Schema::hasTable($table);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' hasTable failed', ['table' => $table, 'error' => $e->getMessage()]);
			return false;
		}
	}

	private function safeHasColumn(string $table, string $col): bool
	{
		try {
			return Schema::hasColumn($table, $col);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' hasColumn failed', ['table' => $table, 'col' => $col, 'error' => $e->getMessage()]);
			return false;
		}
	}

	private function resolveProjectInvolvedColumn(): ?string
	{
		try {
			if (Schema::hasColumn(DC::TABLE_PROJECTS, 'involved')) return 'involved';
			if (Schema::hasColumn(DC::TABLE_PROJECTS, 'involded')) return 'involded'; // compat typo
		} catch (\Throwable $e) {
			Log::debug(static::class . ' resolveProjectInvolvedColumn failed', ['error' => $e->getMessage()]);
		}
		return null;
	}

	/** @return array<int, string> */
	private function fetchAllProjectIdsSafe(): array
	{
		try {
			return array_values(array_filter(array_map(
				fn($v) => is_scalar($v) ? (string) $v : '',
				DB::table(DC::TABLE_PROJECTS)->pluck('id')->all()
			)));
		} catch (\Throwable $e) {
			Log::warning(static::class . ' cannot read projects', ['error' => $e->getMessage()]);
			return [];
		}
	}

	/**
	 * Users elegíveis (para o primeiro loop):
	 * - type IN ['company','super admin','vendor'] OR
	 * - UC::COL_EMP_ID not null AND links to a valid employees row
	 *
	 * @return array<int, string>
	 */
	private function fetchEligibleOwnerUserIds(): array
	{
		$types = ['company', 'super admin', 'vendor'];

		try {
			$hasEmpCol = Schema::hasColumn(DC::TABLE_USERS, UC::COL_EMP_ID);

			$q = DB::table(DC::TABLE_USERS)->select([DC::TABLE_USERS . '.id']);
			$q->whereIn(DC::TABLE_USERS . '.type', $types);

			if ($hasEmpCol) {
				$q->orWhere(function ($qq): void {
					$qq->whereNotNull(DC::TABLE_USERS . '.' . UC::COL_EMP_ID)
						->whereIn(
							DC::TABLE_USERS . '.' . UC::COL_EMP_ID,
							DB::table(DC::TABLE_EMPLOYEES)->select('id')
						);
				});
			}

			$ids = $q->pluck('id')->all();
			return array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
		} catch (\Throwable $e) {
			Log::warning(static::class . ' fetchEligibleOwnerUserIds failed', ['error' => $e->getMessage()]);
			return [];
		}
	}

	/**
	 * assigned_by elegíveis (regra da migration, em modo "soft"):
	 * - type IN ['admin','super admin','company'] OR
	 * - UC::COL_EMP_ID not null AND employee.manager = true
	 *
	 * @return array<int, string>
	 */
	private function fetchAssignableUserIds(): array
	{
		$types = ['admin', 'super admin', 'company'];

		try {
			$hasEmpCol = Schema::hasColumn(DC::TABLE_USERS, UC::COL_EMP_ID);

			$q = DB::table(DC::TABLE_USERS)->select([DC::TABLE_USERS . '.id']);
			$q->whereIn(DC::TABLE_USERS . '.type', $types);

			if ($hasEmpCol && Schema::hasColumn(DC::TABLE_EMPLOYEES, 'manager')) {
				$q->orWhere(function ($qq): void {
					$qq->whereNotNull(DC::TABLE_USERS . '.' . UC::COL_EMP_ID)
						->whereIn(
							DC::TABLE_USERS . '.' . UC::COL_EMP_ID,
							DB::table(DC::TABLE_EMPLOYEES)->where('manager', true)->select('id')
						);
				});
			}

			$ids = $q->pluck('id')->all();
			return array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
		} catch (\Throwable $e) {
			Log::debug(static::class . ' fetchAssignableUserIds failed', ['error' => $e->getMessage()]);
			return [];
		}
	}

	/**
	 * foreach (project where user in json(involved)) OR fallback:
	 * pick 2..16 projects and update involved field to include user id (if possible).
	 *
	 * @return array<int, string>
	 */
	private function resolveProjectsForUser(string $userId): array
	{
		$userId = trim($userId);
		if ($userId === '') return [];

		$col = $this->projectInvolvedColumn;

		if ($col) {
			try {
				$ids = DB::table(DC::TABLE_PROJECTS)
					->whereJsonContains($col, $userId)
					->pluck('id')
					->all();

				$ids = array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
				if (!empty($ids)) return $ids;
			} catch (\Throwable $e) {
				Log::debug(static::class . ' whereJsonContains failed (projects)', [
					'col' => $col,
					'error' => $e->getMessage(),
				]);
			}
		}

		$pick = $this->sampleArray(
			$this->allProjectIds,
			random_int(2, min(16, max(2, count($this->allProjectIds))))
		);

		if ($col) {
			foreach ($pick as $pid) {
				$this->tryAppendUserToProjectInvolved($pid, $userId, $col);
			}
		}

		return $pick;
	}

	private function tryAppendUserToProjectInvolved(string $projectId, string $userId, string $col): void
	{
		$projectId = trim($projectId);
		$userId = trim($userId);
		if ($projectId === '' || $userId === '' || $col === '') return;

		try {
			$current = DB::table(DC::TABLE_PROJECTS)->where('id', $projectId)->value($col);
			$list = $this->normalizeArrayField($current);

			$list[] = $userId;
			$list = array_values(array_unique(array_filter($list, fn($v) => is_string($v) && trim($v) !== '')));

			DB::table(DC::TABLE_PROJECTS)->where('id', $projectId)->update([
				$col => json_encode(array_slice($list, 0, 2048), JSON_UNESCAPED_UNICODE),
				DC::COL_U_AT => now(),
			]);
		} catch (\Throwable $e) {
			Log::debug(static::class . ' failed updating project involved', [
				'project_id' => $projectId,
				'col' => $col,
				'error' => $e->getMessage(),
			]);
		}
	}

	/** @return array<int, string> */
	private function fetchTaskIdsForProject(string $projectId): array
	{
		$projectId = trim($projectId);
		if ($projectId === '') return [];

		try {
			$ids = DB::table(DC::TABLE_TASKS)
				->where(PJC::COL_PJ_ID, $projectId)
				->pluck('id')
				->all();

			return array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
		} catch (\Throwable $e) {
			Log::debug(static::class . ' fetchTaskIdsForProject failed', [
				'project_id' => $projectId,
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/** @return array<int, string> */
	private function fetchMilestoneIdsForProject(string $projectId): array
	{
		$projectId = trim($projectId);
		if ($projectId === '') return [];

		try {
			$ids = DB::table(DC::TABLE_MSS)
				->where(PJC::COL_PJ_ID, $projectId)
				->pluck('id')
				->all();

			return array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
		} catch (\Throwable $e) {
			Log::debug(static::class . ' fetchMilestoneIdsForProject failed', [
				'project_id' => $projectId,
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * notification: randomic any that has UC::COL_USER_ID == same user as the UserToDo.
	 */
	private function pickNotificationIdForUser(string $userId): ?string
	{
		$userId = trim($userId);
		if ($userId === '' || !$this->hasNotificationsUserCol) return null;

		if (!array_key_exists($userId, self::$cache['notifications_by_user'])) {
			try {
				$ids = DB::table(DC::TABLE_NTF)
					->where(UC::COL_USER_ID, $userId)
					->pluck('id')
					->all();

				$list = array_values(array_filter(array_map(
					fn($v) => is_scalar($v) ? trim((string) $v) : '',
					$ids
				)));

				self::$cache['notifications_by_user'][$userId] = $list;
			} catch (\Throwable $e) {
				Log::debug(static::class . ' pickNotificationIdForUser failed fetching', [
					'user_id' => $userId,
					'error'   => $e->getMessage(),
				]);
				self::$cache['notifications_by_user'][$userId] = [];
			}
		}

		$list = self::$cache['notifications_by_user'][$userId];

		// defesa extra: se alguém corromper o cache em runtime
		if (!is_array($list) || $list === []) return null;

		try {
			$idx = random_int(0, count($list) - 1);
		} catch (\Throwable) {
			return null;
		}

		$id = $list[$idx] ?? null;
		return (is_string($id) && $id !== '') ? $id : null;
	}

	/**
	 * Cria 1 UserToDo (sem buffer), emitindo writeln antes.
	 *
	 * @param array<int, string> $assignerIds
	 */
	private function createToDo(
		string $ownerUserId,
		string $projectId,
		?string $taskId,
		?string $milestoneId,
		array $assignerIds
	): void {
		$creatorId = $ownerUserId; // pragmático (pode ajustar conforme sua política)

		$assignedBy = null;
		if (!empty($assignerIds) && random_int(0, 100) < 55) {
			$assignedBy = $assignerIds[array_rand($assignerIds)] ?? null;
			if (!is_string($assignedBy) || trim($assignedBy) === '') $assignedBy = null;
		}

		$assignedAt = null;
		if ($assignedBy !== null && random_int(0, 100) < 80)
			$assignedAt = Carbon::now('America/Sao_Paulo')->subDays(random_int(0, 30));

		$notificationId = $this->pickNotificationIdForUser($ownerUserId);

		$priority = $this->randomPriority();

		$progressRaw = random_int(0, 10000) / 100; // 0.00..100.00
		$isComplete = random_int(0, 100) < 30;
		if ($isComplete) $progressRaw = 100.00;

		$dueDate = random_int(0, 100) < 70
			? Carbon::now('America/Sao_Paulo')->addDays(random_int(-30, 60))->toDateString()
			: null;

		$estimated = random_int(0, 100) < 60 ? $this->randomTimeString() : null;
		$isFav = random_int(0, 100) < 15;

		$title = $this->makeTitle($projectId, $taskId, $milestoneId);
		$desc = random_int(0, 100) < 50 ? $this->makeDescription($projectId, $taskId, $milestoneId) : null;

		$tags = $this->pickTags();
		$attachments = $this->pickAttachments();

		$this->out()->writeln(
			"<comment>[UserToDoSeeder]</comment> Creating todo"
				. " user={$ownerUserId}"
				. " project={$projectId}"
				. " task=" . ($taskId ?: 'null')
				. " milestone=" . ($milestoneId ?: 'null')
				. " notification=" . ($notificationId ?: 'null')
				. " priority={$priority}"
				. " progress=" . number_format((float) $progressRaw, 2, '.', '')
				. " complete=" . ($isComplete ? 'true' : 'false')
		);

		try {
			Model::unguarded(function () use (
				$title,
				$desc,
				$ownerUserId,
				$assignedBy,
				$assignedAt,
				$milestoneId,
				$projectId,
				$taskId,
				$notificationId,
				$priority,
				$progressRaw,
				$estimated,
				$dueDate,
				$isComplete,
				$isFav,
				$tags,
				$attachments,
				$creatorId
			): void {
				UserToDo::query()->create([
					AC::COL_TT => $title,
					'description' => $desc,

					UC::COL_USER_ID => $ownerUserId,

					PJC::COL_ASG_BY => $assignedBy,
					PJC::COL_ASG_AT => $assignedAt,

					'milestone' => $milestoneId,
					'project' => $projectId,
					'task' => $taskId,

					'notification' => $notificationId,

					'priority' => $priority,
					'progress' => $progressRaw,
					'order' => random_int(0, 200),

					PJC::COL_E_HRS => $estimated,
					PJC::COL_D_DATE => $dueDate,

					PJC::COL_IS_CP => $isComplete,
					PJC::COL_CMP_AT => $isComplete ? now('America/Sao_Paulo') : null,
					PJC::COL_IS_FV => $isFav,

					'tags' => $tags,
					'attachments' => $attachments,

					DC::COL_TABLE_CREATOR => $creatorId,
					DC::COL_TABLE_UPDATER => $creatorId,
				]);
			});
		} catch (\Throwable $e) {
			Log::warning(static::class . ' create failed', [
				'user_id' => $ownerUserId,
				'project_id' => $projectId,
				'task' => $taskId,
				'milestone' => $milestoneId,
				'notification' => $notificationId,
				'error' => $e->getMessage(),
			]);
		}
	}

	private function randomPriority(): string
	{
		$cases = PriorityLevel::cases();
		$c = $cases[array_rand($cases)] ?? PriorityLevel::Medium;
		return $c->value;
	}

	private function randomTimeString(): string
	{
		$h = random_int(0, 23);
		$m = random_int(0, 59);
		return sprintf('%02d:%02d:00', $h, $m);
	}

	private function makeTitle(string $projectId, ?string $taskId, ?string $milestoneId): string
	{
		$seed = strtoupper(Str::random(6));
		if ($taskId) return "To-do (Task) #{$seed}";
		if ($milestoneId) return "To-do (Milestone) #{$seed}";
		return "To-do (Project) #{$seed}";
	}

	private function makeDescription(string $projectId, ?string $taskId, ?string $milestoneId): string
	{
		$parts = ["Auto-generated to-do for project={$projectId}."];
		if ($taskId) $parts[] = "task={$taskId}.";
		if ($milestoneId) $parts[] = "milestone={$milestoneId}.";
		return implode(' ', $parts);
	}

	/** @return array<int, string> */
	private function pickTags(): array
	{
		$pool = ['todo', 'follow_up', 'internal', 'customer', 'urgent', 'low_priority', 'planning'];
		$take = random_int(0, 4);
		if ($take <= 0) return [];
		return $this->sampleArray($pool, min($take, count($pool)));
	}

	/** @return array<int, string> */
	private function pickAttachments(): array
	{
		if (random_int(0, 100) < 70) return [];
		$n = random_int(1, 2);
		$out = [];
		for ($i = 0; $i < $n; $i++)
			$out[] = 'uploads/todos/' . date('Y/m') . '/att_' . Str::random(10) . '.pdf';
		return array_values(array_unique($out));
	}

	private function normalizeArrayField(mixed $value): array
	{
		if ($value === null) return [];
		if (is_array($value)) return $value;

		if (is_string($value)) {
			$t = trim($value);
			if ($t === '') return [];
			try {
				$d = json_decode($t, true, 512, JSON_THROW_ON_ERROR);
				return is_array($d) ? $d : [];
			} catch (\Throwable) {
				return [];
			}
		}

		return (array) $value;
	}

	/**
	 * @template T
	 * @param array<int, T> $list
	 * @return array<int, T>
	 */
	private function sampleArray(array $list, int $count): array
	{
		$list = array_values($list);
		if ($count <= 0 || $list === []) return [];
		if ($count >= count($list)) return $list;

		$keys = array_rand($list, $count);
		$keys = is_array($keys) ? $keys : [$keys];

		$out = [];
		foreach ($keys as $k)
			if (array_key_exists($k, $list)) $out[] = $list[$k];

		return array_values(array_unique($out, SORT_REGULAR));
	}
}
