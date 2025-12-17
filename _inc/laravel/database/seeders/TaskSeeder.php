<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\AppModuleType;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class TaskSeeder extends Seeder
{
	private ?ConsoleOutput $output = null;

	/** @var array<string, array<int, string|null>> */
	private array $milestoneSamplesByProject = [];

	/** @var array<string, array<int, string>> */
	private array $milestoneIdsByProject = [];

	public function run(): void
	{
		$this->seedTasks();
	}

	protected function seedTasks(): void
	{
		try {
			$this->out()->writeln('<info>[TaskSeeder]</info> Starting seeding tasks...');

			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();
			if (empty($userIds)) {
				Log::warning(static::class . ' skipping: no users found for tasks');
				$this->out()->writeln('<comment>[TaskSeeder]</comment> Skipping: no users found.');
				return;
			}

			$projects = DB::table(DC::TABLE_PROJECTS)->pluck('id')->all();
			if (empty($projects)) {
				Log::warning(static::class . ' seeding with null projects: no projects found');
				$this->out()->writeln('<comment>[TaskSeeder]</comment> No projects found; tasks will be created with project_id = null.');
				$projects = [null];
			}

			$managerEmployees = $this->fetchManagerEmployeesSafe();
			if (empty($managerEmployees)) {
				Log::warning(static::class . ' skipping: no employees found (agent_or_manager_id is required)');
				$this->out()->writeln('<comment>[TaskSeeder]</comment> Skipping: no employees found for agent_or_manager_id.');
				return;
			}

			$stageIds = [];
			try {
				$stageIds = DB::table(DC::TABLE_TSK_STGS)->pluck('id')->all();
			} catch (\Throwable $e) {
				Log::debug(static::class . ' cannot read task stages ids', ['error' => $e->getMessage()]);
			}

			$cap = $this->resolveCap();
			$created = 0;

			$moduleCases = AppModuleType::cases();
			$moduleIndexMap = $this->buildModuleIndexMap($moduleCases);

			foreach ($moduleCases as $moduleEnum) {
				$moduleTaskCount = random_int(2, 64);

				$projectPickCount = $this->randBounded(2, 16, count($projects));
				$projectPool = $this->sampleArray($projects, $projectPickCount);

				for ($i = 0; $i < $moduleTaskCount; $i++) {
					if ($cap > 0 && $created >= $cap) break 2;

					$projectId = $projectPool !== [] ? $projectPool[array_rand($projectPool)] : null;
					$milestoneId = $this->pickMilestoneForProject($projectId);

					$assigneeId = (random_int(0, 100) < 70) ? (string) $userIds[array_rand($userIds)] : null;

					$creatorId = (string) $userIds[array_rand($userIds)];
					$aom = $managerEmployees[array_rand($managerEmployees)];
					$aomId = (string) ($aom['id'] ?? '');
					$aomName = trim((string) ($aom['name'] ?? ''));

					if ($aomId === '') {
						Log::warning(static::class . ' skipping row: empty agent_or_manager_id');
						continue;
					}
					if ($aomName === '') $aomName = 'N/A';

					$date = Carbon::now()->addDays(random_int(-30, 30))->toDateString();
					$time = sprintf('%02d:%02d:%02d', random_int(8, 19), random_int(0, 59), 0);

					$moduleIndex = $moduleIndexMap[$moduleEnum->value] ?? '0';

					$attrs = [
						'title' => $this->makeTitle($moduleEnum),
						AC::COL_A_O_M => $aomName,
						PJC::COL_AOM_ID => $aomId,
						'date' => $date,
						'time' => $time,
						'description' => $this->maybeDescription($moduleEnum),

						AC::COL_MT => $moduleEnum,      // ✅ enum direto
						AC::COL_MI => $moduleIndex,     // “case index stringified”

						PJC::COL_ASGN => $assigneeId,
						PJC::COL_PJ_ID => $projectId ? (string) $projectId : null,
						PJC::COL_ML_ID => $milestoneId ? (string) $milestoneId : null,

						'stages' => $this->pickStageIds($stageIds),
						'attachments' => $this->pickAttachments(),
						'involved' => $this->buildInvolved($userIds, $assigneeId),
						'tags' => $this->pickTags($moduleEnum),
						'metadata' => $this->buildMetadata($moduleEnum, $projectId, $milestoneId, $assigneeId),

						DC::COL_TABLE_CREATOR => $creatorId,
						DC::COL_TABLE_UPDATER => $creatorId,
					];

					$this->writelnCreate($moduleEnum, $projectId, $milestoneId, $assigneeId, $aomName, $date, $time);

					try {
						Model::unguarded(function () use ($attrs): void {
							Task::query()->create($attrs);
						});
						$created++;
					} catch (\Throwable $e) {
						Log::warning(static::class . ' create failed', [
							'module' => $moduleEnum->value,
							'project_id' => is_scalar($projectId) ? (string) $projectId : null,
							'milestone_id' => is_scalar($milestoneId) ? (string) $milestoneId : null,
							'error' => $e->getMessage(),
						]);
					}
				}
			}

			Log::info(static::class . ' seeded tasks', ['created' => $created, 'cap' => $cap]);
			$this->out()->writeln("<info>[TaskSeeder]</info> Done. Created: {$created}" . ($cap > 0 ? " (cap={$cap})" : ''));
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed seeding tasks', ['error' => $e->getMessage()]);
			$this->out()->writeln('<error>[TaskSeeder]</error> Failed: ' . $e->getMessage());
		}
	}

	private function out(): ConsoleOutput
	{
		return $this->output ??= new ConsoleOutput();
	}

	private function writelnCreate(
		AppModuleType $module,
		mixed $projectId,
		mixed $milestoneId,
		?string $assigneeId,
		string $aomName,
		string $date,
		string $time
	): void {
		$p = is_scalar($projectId) ? (string) $projectId : 'null';
		$m = is_scalar($milestoneId) ? (string) $milestoneId : 'null';
		$a = $assigneeId ? $assigneeId : 'null';

		$this->out()->writeln(
			"<comment>[TaskSeeder]</comment> Creating task"
				. " module={$module->value}"
				. " project={$p}"
				. " milestone={$m}"
				. " assignee={$a}"
				. " aom=\"{$aomName}\""
				. " at={$date} {$time}"
		);
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

	/**
	 * @return array<int, array{id:string,name:string}>
	 */
	private function fetchManagerEmployeesSafe(): array
	{
		try {
			if (Schema::hasColumn(DC::TABLE_EMPLOYEES, 'manager')) {
				$rows = DB::table(DC::TABLE_EMPLOYEES)
					->where('manager', 1)
					->select(['id', 'name'])
					->get();
				$list = [];
				foreach ($rows as $r) {
					$id = (string) ($r->id ?? '');
					if ($id === '') continue;
					$list[] = ['id' => $id, 'name' => (string) ($r->name ?? '')];
				}
				if ($list !== []) return $list;
			}
		} catch (\Throwable $e) {
			Log::debug(static::class . ' manager filter unavailable', ['error' => $e->getMessage()]);
		}

		try {
			$rows = DB::table(DC::TABLE_EMPLOYEES)->select(['id', 'name'])->get();
			$list = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id === '') continue;
				$list[] = ['id' => $id, 'name' => (string) ($r->name ?? '')];
			}
			return $list;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' cannot read employees', ['error' => $e->getMessage()]);
			return [];
		}
	}

	/**
	 * @param array<int, AppModuleType> $cases
	 * @return array<string, string> map[value] => "index"
	 */
	private function buildModuleIndexMap(array $cases): array
	{
		$map = [];
		foreach ($cases as $i => $case) {
			$map[$case->value] = (string) $i;
		}
		return $map;
	}

	private function makeTitle(AppModuleType $module): string
	{
		$suffix = strtoupper(Str::random(6));
		return $module->label() . ' Task #' . $suffix;
	}

	private function maybeDescription(AppModuleType $module): ?string
	{
		if (random_int(0, 100) < 35) return null;
		return 'Auto-generated task for module: ' . $module->value . '.';
	}

	/**
	 * @param array<int, string> $stageIds
	 * @return array<int, string>
	 */
	private function pickStageIds(array $stageIds): array
	{
		if (empty($stageIds) || random_int(0, 100) < 40) return [];

		$take = $this->randBounded(1, 3, count($stageIds));
		return $this->sampleArray($stageIds, $take);
	}

	/**
	 * @return array<int, string>
	 */
	private function pickAttachments(): array
	{
		if (random_int(0, 100) < 60) return [];
		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$out[] = 'uploads/tasks/' . date('Y/m') . '/file_' . Str::random(10) . '.pdf';
		}
		return array_values(array_unique($out));
	}

	/**
	 * @param array<int, string> $userIds
	 * @return array<int, string>
	 */
	private function buildInvolved(array $userIds, ?string $assigneeId): array
	{
		$out = [];
		if ($assigneeId) $out[] = $assigneeId;

		if ($userIds !== []) {
			$take = $this->randBounded(1, 5, count($userIds));
			$out = array_merge($out, $this->sampleArray($userIds, $take));
		}

		$out = array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
		return array_slice($out, 0, 64);
	}

	/**
	 * @return array<int, string>
	 */
	private function pickTags(AppModuleType $module): array
	{
		$base = [
			'planning',
			'meeting',
			'delivery',
			'follow-up',
			'backend',
			'frontend',
			'infra',
			'support',
			'urgent',
			'low_priority',
			'customer',
			'internal',
		];

		$base[] = 'module:' . $module->value;

		$take = random_int(1, 4);
		return $this->sampleArray($base, min($take, count($base)));
	}

	private function buildMetadata(
		AppModuleType $module,
		mixed $projectId,
		mixed $milestoneId,
		?string $assigneeId
	): array {
		return [
			'seeded' => true,
			'seeded_at' => Carbon::now()->toIso8601String(),
			'module' => $module->value,
			'module_category' => $module->getCategory(),
			'project_id' => is_scalar($projectId) ? (string) $projectId : null,
			'milestone_id' => is_scalar($milestoneId) ? (string) $milestoneId : null,
			'assignee_id' => $assigneeId,
		];
	}

	private function pickMilestoneForProject(mixed $projectId): ?string
	{
		$pid = is_scalar($projectId) ? trim((string) $projectId) : '';
		if ($pid === '') return null;

		if (!array_key_exists($pid, $this->milestoneSamplesByProject)) {
			$ids = $this->fetchMilestoneIdsForProject($pid);

			if (empty($ids)) {
				$this->milestoneSamplesByProject[$pid] = [null];
			} else {
				$take = $this->randBounded(2, 8, count($ids));
				$this->milestoneSamplesByProject[$pid] = $this->sampleArray($ids, $take);
			}
		}

		$pool = $this->milestoneSamplesByProject[$pid] ?? [null];
		$pick = $pool[array_rand($pool)] ?? null;

		return is_string($pick) && $pick !== '' ? $pick : null;
	}

	/**
	 * @return array<int, string>
	 */
	private function fetchMilestoneIdsForProject(string $projectId): array
	{
		if (array_key_exists($projectId, $this->milestoneIdsByProject))
			return $this->milestoneIdsByProject[$projectId];

		try {
			$ids = DB::table(DC::TABLE_MSS)
				->where(PJC::COL_PJ_ID, $projectId)
				->pluck('id')
				->all();

			$ids = array_values(array_filter(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $ids)));
			$this->milestoneIdsByProject[$projectId] = $ids;

			return $ids;
		} catch (\Throwable $e) {
			Log::debug(static::class . ' cannot read milestones for project', [
				'project_id' => $projectId,
				'error' => $e->getMessage(),
			]);
			$this->milestoneIdsByProject[$projectId] = [];
			return [];
		}
	}

	private function randBounded(int $minWanted, int $maxWanted, int $available): int
	{
		if ($available <= 0) return 0;
		$min = min($minWanted, $available);
		$max = min($maxWanted, $available);
		if ($min > $max) $min = $max;
		return random_int($min, $max);
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
		foreach ($keys as $k) {
			if (array_key_exists($k, $list)) $out[] = $list[$k];
		}

		return array_values(array_unique($out, SORT_REGULAR));
	}
}
