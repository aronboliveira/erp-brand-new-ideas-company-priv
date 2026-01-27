<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\UserType;
use App\Models\TaskChecklist;
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

final class TaskChecklistSeeder extends Seeder
{
	private const MAX_PER_TASK = 8;
	private const STAGE_ATTACH_CHANCE = 55; // %
	private const UNIQUE_NAME_MAX_ATTEMPTS = 10;
	private const PAD_MAX_ATTEMPTS = 25; // break-out strategy
	private const DEFAULT_MIN_PER_USER_TYPE = 4;

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_TSK_CHKL) || !Schema::hasTable(DC::TABLE_TASKS)) {
			return;
		}

		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$io = $this->makeIo();
		$faker = FakerFactory::create('pt_BR');

		$table = DC::TABLE_TSK_CHKL;

		$tasks = $this->loadTasksPoolRaw();
		if ($tasks->isEmpty()) {
			$io->warning('No tasks found; skipping TaskChecklistSeeder.');
			return;
		}

		$stages = $this->loadStagesPoolRaw();
		$stagesByTask = $this->indexStagesByTask($stages);

		$existingStageCoverage = $this->loadExistingStageCoverageRaw($table);
		$missingStages = $this->missingStages($stages, $existingStageCoverage, $tasks);

		$existingTypeCounts = $this->loadExistingUserTypeCountsRaw($table);

		$taskExistingCounts = $this->loadExistingCountsByTaskRaw($table);
		$taskCounters = $taskExistingCounts->map(fn(int $c): int => max(0, min(255, $c)));

		$notifications = DB::table(DC::TABLE_NTF)->pluck('id')->toArray();

		$requestedCount = $this->readCountOption();
		$io->section('TaskChecklistSeeder plan');
		$io->text([
			'Tasks: ' . $tasks->count(),
			'Stages: ' . $stages->count(),
			'Missing stages to cover: ' . $missingStages->count(),
			'Requested count (--count): ' . ($requestedCount !== null ? (string)$requestedCount : 'n/a'),
		]);

		$plan = collect();

		// 1) Ensure: each stage has at least 1 checklist (respecting stage->task constraint)
		$plan = $plan->concat(
			$missingStages->map(function (array $st): array {
				return [
					'task_id' => (string)$st[AC::COL_TSK_ID],
					'stage_id' => (string)$st['id'],
					'user_type' => $this->randomUserType(),
				];
			})
		);

		// 2) For each task: generate 0..8 with inverted-quadratic bias towards lower values
		foreach ($tasks->keys() as $taskId) {
			$n = $this->sampleInvertedQuadratic(0, self::MAX_PER_TASK);

			for ($i = 0; $i < $n; $i++) {
				$stageId = null;
				$candidateStages = $stagesByTask->get($taskId, collect());
				if ($candidateStages->isNotEmpty() && random_int(1, 100) <= self::STAGE_ATTACH_CHANCE) {
					$stageId = (string)$candidateStages->random();
				}

				$plan->push([
					'task_id' => (string)$taskId,
					'stage_id' => $stageId,
					'user_type' => $this->randomUserType(),
				]);
			}
		}

		// 3) Ensure: each user type has at least 4 checklists (total after this run)
		$plannedTypeCounts = $this->countPlanUserTypes($plan);
		$typeDeficits = $this->computeTypeDeficits($existingTypeCounts, $plannedTypeCounts);

		foreach ($typeDeficits as $utValue => $need) {
			for ($i = 0; $i < $need; $i++) {
				$taskId = (string)$tasks->keys()->random();
				$stageId = null;
				$candidateStages = $stagesByTask->get($taskId, collect());
				if ($candidateStages->isNotEmpty()) {
					$stageId = (string)$candidateStages->random();
				}

				$plan->push([
					'task_id' => $taskId,
					'stage_id' => $stageId,
					'user_type' => $utValue,
				]);
			}
		}

		$rawTotal = $plan->count();

		// 4) Always create a multiple of 64 (optionally honoring --count as a minimum)
		$target = $this->adjustToMultipleOf64($rawTotal);
		if ($requestedCount !== null) {
			$target = max($target, $this->adjustToMultipleOf64($requestedCount));
		}
		if ($target < $rawTotal) {
			$target = $this->adjustToMultipleOf64($rawTotal);
		}

		$pad = $target - $rawTotal;
		if ($pad > 0) {
			$padPlans = $this->buildPaddingPlan($tasks, $stagesByTask, $pad);
			$plan = $plan->concat($padPlans);
		}

		$io->text([
			'Raw total planned: ' . $rawTotal,
			'Adjusted (multiple of 64): ' . $plan->count(),
			'Padding added: ' . max(0, $plan->count() - $rawTotal),
		]);

		// 5) Persist with Model::create to respect $casts + booted() rules (no raw DB insert)
		$localUsedNames = [];

		$created = 0;
		$byTypeCreated = [];

		$cap = 512;
		foreach ($plan as $p) {
			if (!$cap || $cap <= 0) break;
			$cap--;
			$taskId = (string)($p['task_id'] ?? '');
			if ($taskId === '') {
				continue;
			}

			$taskInfo = $tasks->get($taskId, null);
			if (!is_array($taskInfo)) {
				continue;
			}

			// If $p is an array
			$userTypeValue = '';
			if (isset($p['user_type'])) {
				$userTypeValue = $p['user_type'] instanceof UserType
					? $p['user_type']->value
					: (string)$p['user_type'];
			}

			if ($userTypeValue === '') {
				$ut = $this->randomUserType();
				$userTypeValue = $this->userTypeDbValue($ut);
			}

			$name = $this->generateUniqueNameWithExists($table, $localUsedNames);
			$notification = $notifications !== [] ? $notifications[array_rand($notifications)] : null;
			$row = $this->makeChecklistAttributes(
				$faker,
				$taskId,
				is_string($p['stage_id'] ?? null) ? (string)$p['stage_id'] : null,
				$userTypeValue,
				$taskInfo,
				$taskCounters
			);

			// important: do NOT strip nulls; let Eloquent handle nullable attributes naturally
			$row['name'] = $name;
			$row['notification'] = $notification;
			$output->writeln('Creating TaskChecklist for task ' . $taskId . ' with name ' . $name);
			TaskChecklist::query()->create($row);

			$created++;
			$byTypeCreated[$userTypeValue] = (int)($byTypeCreated[$userTypeValue] ?? 0) + 1;
		}

		$io->section('TaskChecklistSeeder result');
		$io->text([
			'Created: ' . $created,
			'Created per user_type: ' . json_encode($byTypeCreated, JSON_UNESCAPED_UNICODE),
		]);
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
		if (!($cmd instanceof Command)) {
			return null;
		}

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
	 * @return Collection<string, array{date:?Carbon,involved:array<int,string>}>
	 */
	private function loadTasksPoolRaw(): Collection
	{
		$table = DC::TABLE_TASKS;

		$select = ['id'];
		if (Schema::hasColumn($table, 'date')) $select[] = 'date';
		if (Schema::hasColumn($table, 'involved')) $select[] = 'involved';

		$sql = 'select ' . implode(', ', $select) . ' from ' . $table;
		$rows = DB::select($sql);

		$out = collect();
		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string)$r->id : '';
			if ($id === '') continue;

			$dt = null;
			if (property_exists($r, 'date') && $r->date !== null && (string)$r->date !== '') {
				try {
					$dt = Carbon::parse((string)$r->date)->startOfDay();
				} catch (\Throwable) {
					$dt = null;
				}
			}

			$involved = [];
			if (property_exists($r, 'involved')) {
				$involved = $this->decodeStringList($r->involved);
			}

			$out->put($id, ['date' => $dt, 'involved' => $involved]);
		}

		return $out;
	}

	/**
	 * @return Collection<int, array{id:string, task_id?:string}>
	 */
	private function loadStagesPoolRaw(): Collection
	{
		if (!Schema::hasTable(DC::TABLE_TSK_STGS)) {
			return collect();
		}

		$table = DC::TABLE_TSK_STGS;
		$cols = Schema::getColumnListing($table);

		$select = ['id'];
		if (in_array(AC::COL_TSK_ID, $cols, true)) $select[] = AC::COL_TSK_ID;

		$sql = 'select ' . implode(', ', $select) . ' from ' . $table;
		$rows = DB::select($sql);

		$out = collect();
		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string)$r->id : '';
			if ($id === '') continue;

			$tid = null;
			if (property_exists($r, AC::COL_TSK_ID) && is_scalar($r->{AC::COL_TSK_ID} ?? null)) {
				$s = (string)$r->{AC::COL_TSK_ID};
				$tid = $s !== '' ? $s : null;
			}

			$row = ['id' => $id];
			if ($tid !== null) $row[AC::COL_TSK_ID] = $tid;

			$out->push($row);
		}

		return $out;
	}

	/**
	 * @param Collection<int, array{id:string, task_id?:string}> $stages
	 * @return Collection<string, Collection<int,string>>
	 */
	private function indexStagesByTask(Collection $stages): Collection
	{
		$out = collect();

		foreach ($stages as $st) {
			$tid = (string)($st[AC::COL_TSK_ID] ?? '');
			$sid = (string)($st['id'] ?? '');
			if ($tid === '' || $sid === '') continue;

			$out->put($tid, ($out->get($tid, collect()))->push($sid));
		}

		foreach ($out as $k => $list) {
			$out->put($k, $list->unique()->values());
		}

		return $out;
	}

	/**
	 * @return Collection<string,bool> stage_id => true
	 */
	private function loadExistingStageCoverageRaw(string $table): Collection
	{
		if (!Schema::hasColumn($table, 'stage')) {
			return collect();
		}

		$rows = DB::select('select stage from ' . $table . ' where stage is not null group by stage');

		$out = collect();
		foreach ($rows as $r) {
			$sid = is_scalar($r->stage ?? null) ? (string)$r->stage : '';
			if ($sid !== '') $out->put($sid, true);
		}

		return $out;
	}

	/**
	 * @param Collection<int, array{id:string, task_id?:string}> $stages
	 * @param Collection<string,bool> $covered
	 * @param Collection<string, array{date:?Carbon,involved:array<int,string>}> $tasks
	 * @return Collection<int, array{id:string, task_id:string}>
	 */
	private function missingStages(Collection $stages, Collection $covered, Collection $tasks): Collection
	{
		return $stages
			->filter(function (array $st) use ($covered, $tasks): bool {
				$sid = (string)($st['id'] ?? '');
				$tid = (string)($st[AC::COL_TSK_ID] ?? '');
				if ($sid === '' || $tid === '') return false;
				if ($covered->has($sid)) return false;
				return $tasks->has($tid);
			})
			->values();
	}

	/**
	 * @return Collection<string,int> user_type_value => count
	 */
	private function loadExistingUserTypeCountsRaw(string $table): Collection
	{
		if (!Schema::hasColumn($table, UC::COL_U_TP)) {
			return collect();
		}

		$rows = DB::select(
			'select ' . UC::COL_U_TP . ' as ut, count(*) as c from ' . $table . ' group by ' . UC::COL_U_TP
		);

		$out = collect();
		foreach ($rows as $r) {
			$ut = is_scalar($r->ut ?? null) ? (string)$r->ut : '';
			$c = is_numeric($r->c ?? null) ? (int)$r->c : 0;
			if ($ut !== '') $out->put($ut, max(0, $c));
		}

		return $out;
	}

	/**
	 * @return Collection<string,int> task_id => count
	 */
	private function loadExistingCountsByTaskRaw(string $table): Collection
	{
		if (!Schema::hasColumn($table, AC::COL_TSK_ID)) {
			return collect();
		}

		$rows = DB::select(
			'select ' . AC::COL_TSK_ID . ' as tid, count(*) as c from ' . $table . ' where ' . AC::COL_TSK_ID . ' is not null group by ' . AC::COL_TSK_ID
		);

		$out = collect();
		foreach ($rows as $r) {
			$tid = is_scalar($r->tid ?? null) ? (string)$r->tid : '';
			$c = is_numeric($r->c ?? null) ? (int)$r->c : 0;
			if ($tid !== '') $out->put($tid, max(0, $c));
		}

		return $out;
	}

	private function randomUserType(): UserType
	{
		$cases = UserType::cases();
		return $cases[array_rand($cases)];
	}

	private function userTypeDbValue(UserType $ut): string
	{
		return $ut->value;
	}

	/**
	 * @param Collection<int, array{task_id:string,stage_id:?string,user_type:mixed}> $plan
	 * @return Collection<string,int>
	 */
	private function countPlanUserTypes(Collection $plan): Collection
	{
		$out = collect();

		foreach ($plan as $p) {
			$ut = (string)($p['user_type'] instanceof UserType ? $this->userTypeDbValue($p['user_type']) : ($p['user_type'] ?? ''));
			if ($ut === '') continue;
			$out->put($ut, (int)($out->get($ut, 0)) + 1);
		}

		return $out;
	}

	/**
	 * @param Collection<string,int> $existing
	 * @param Collection<string,int> $planned
	 * @return array<string,int> user_type_value => deficit
	 */
	private function computeTypeDeficits(Collection $existing, Collection $planned): array
	{
		$out = [];
		foreach (UserType::cases() as $ut) {
			$k = $this->userTypeDbValue($ut);
			$have = (int)$existing->get($k, 0) + (int)$planned->get($k, 0);
			$need = max(0, self::DEFAULT_MIN_PER_USER_TYPE - $have);
			if ($need > 0) $out[$k] = $need;
		}
		return $out;
	}

	private function adjustToMultipleOf64(int $rawTotal): int
	{
		if ($rawTotal <= 0) return 64;
		$mod = $rawTotal % 64;
		return $mod === 0 ? $rawTotal : ($rawTotal + (64 - $mod));
	}

	/**
	 * @param Collection<string, array{date:?Carbon,involved:array<int,string>}> $tasks
	 * @param Collection<string, Collection<int,string>> $stagesByTask
	 * @return Collection<int, array{task_id:string,stage_id:?string,user_type:string}>
	 */
	private function buildPaddingPlan(Collection $tasks, Collection $stagesByTask, int $pad): Collection
	{
		$out = collect();
		$taskIds = $tasks->keys()->values();
		if ($taskIds->isEmpty()) return $out;

		for ($i = 0; $i < $pad; $i++) {
			$attempt = 0;
			$pickedTask = null;

			while ($attempt < self::PAD_MAX_ATTEMPTS && $pickedTask === null) {
				$attempt++;
				$pickedTask = (string)$taskIds->random();
				if ($pickedTask === '') $pickedTask = null;
			}

			if ($pickedTask === null) {
				break;
			}

			$stageId = null;
			$candidateStages = $stagesByTask->get($pickedTask, collect());
			if ($candidateStages->isNotEmpty() && random_int(1, 100) <= self::STAGE_ATTACH_CHANCE) {
				$stageId = (string)$candidateStages->random();
			}

			$out->push([
				'task_id' => $pickedTask,
				'stage_id' => $stageId,
				'user_type' => $this->userTypeDbValue($this->randomUserType()),
			]);
		}

		return $out;
	}

	/**
	 * @param array<string,bool> $localUsedNames
	 */
	private function generateUniqueNameWithExists(string $table, array &$localUsedNames): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$candidate = 'TSK-CHKL-' . (string)Str::uuid();

			if (isset($localUsedNames[$candidate])) {
				$exists = true;
			} else {
				$row = DB::selectOne('select 1 as x from ' . $table . ' where name = ? limit 1', [$candidate]);
				$exists = $row !== null;
			}

			if (!$exists) {
				$localUsedNames[$candidate] = true;
				return $candidate;
			}
		} while ($attempts < self::UNIQUE_NAME_MAX_ATTEMPTS);

		$fallback = 'TSK-CHKL-' . (string)Str::uuid();
		$localUsedNames[$fallback] = true;
		return $fallback;
	}

	/**
	 * @param array{date:?Carbon,involved:array<int,string>} $taskInfo
	 * @param Collection<string,int> $taskCounters
	 * @return array<string,mixed>
	 */
	private function makeChecklistAttributes(
		Faker $faker,
		string $taskId,
		?string $stageId,
		string $userTypeValue,
		array $taskInfo,
		Collection $taskCounters
	): array {
		$completed = random_int(1, 100) <= 35;
		$fav = random_int(1, 100) <= 12;

		$taskDate = $taskInfo['date'] ?? null;
		$due = null;

		if ($taskDate instanceof Carbon) {
			$offset = random_int(-30, 0);
			if (random_int(1, 100) <= 15) {
				$offset = random_int(1, 7);
			}

			$due = (clone $taskDate)->addDays($offset)->startOfDay();
			if ($due->greaterThan($taskDate)) {
				$due = (clone $taskDate)->startOfDay();
			}
		}

		$cmpAt = null;
		if ($completed) {
			$cmpAt = now()->subMinutes(random_int(0, 60 * 24 * 14));
		}

		$current = (int)$taskCounters->get($taskId, 0);
		$current = max(0, min(255, $current));
		$taskCounters->put($taskId, min(255, $current + 1));

		$involved = $this->subsetFromList((array)($taskInfo['involved'] ?? []), random_int(0, 4));
		$tags = $this->subsetFromList($this->randomTags($faker), random_int(0, 4));
		$attachments = $this->randomAttachments($faker);
		$positioning = $this->randomPositioning($faker);

		return [
			'description' => (random_int(1, 100) <= 70) ? rtrim((string)$faker->sentence(random_int(6, 14)), '.') : null,
			'url' => (random_int(1, 100) <= 35) ? (string)$faker->url() : null,

			'completed' => $completed,
			PJC::COL_CMP_AT => $cmpAt,
			PJC::COL_D_DATE => $due ? $due->toDateString() : null,

			AC::COL_IS_FV => $fav,
			AC::COL_TSK_ID => $taskId,
			UC::COL_U_TP => $userTypeValue,

			'status' => random_int(0, 5),
			'order' => $current,

			'stage' => $stageId,

			'involved' => $involved,
			'attachments' => $attachments,
			'tags' => $tags,
			'positioning' => $positioning,
		];
	}

	/**
	 * @return array<int,string>
	 */
	private function randomTags(Faker $faker): array
	{
		$n = random_int(0, 6);
		if ($n === 0) return [];

		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$w = trim(Str::ascii(mb_strtolower((string)$faker->word())));
			if ($w !== '') $out[] = $w;
		}

		return collect($out)->unique()->values()->all();
	}

	/**
	 * @return array<int, array<string,mixed>>
	 */
	private function randomAttachments(Faker $faker): array
	{
		$n = random_int(0, 2);
		if ($n === 0) return [];

		$exts = ['pdf', 'png', 'jpg', 'xlsx', 'csv'];
		$out = [];

		for ($i = 0; $i < $n; $i++) {
			$ext = (string)$exts[array_rand($exts)];
			$name = Str::slug((string)$faker->words(random_int(2, 4), true)) . '.' . $ext;

			$out[] = [
				'file_name' => $name,
				'file_path' => 'uploads/task_checklists/' . now()->format('Y/m') . '/' . $name,
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

	/**
	 * @return array<string,mixed>
	 */
	private function randomPositioning(Faker $faker): array
	{
		if (random_int(1, 100) <= 50) {
			return [];
		}

		return [
			'lane' => random_int(1, 6),
			'x' => random_int(0, 1200),
			'y' => random_int(0, 4000),
			'pin' => (random_int(1, 100) <= 10),
			'note' => (random_int(1, 100) <= 25) ? (string)$faker->word() : null,
		];
	}

	/**
	 * @param array<int,mixed> $list
	 * @return array<int,string>
	 */
	private function subsetFromList(array $list, int $maxTake): array
	{
		$clean = collect($list)
			->filter(fn($v) => is_scalar($v) && trim((string)$v) !== '')
			->map(fn($v) => trim((string)$v))
			->unique()
			->values();

		$take = min(max(0, $maxTake), $clean->count());
		if ($take <= 0) return [];

		$k = random_int(0, $take);
		if ($k <= 0) return [];

		return $clean->shuffle()->take($k)->values()->all();
	}

	/**
	 * @return array<int,string>
	 */
	private function decodeStringList(mixed $value): array
	{
		if ($value === null) return [];

		if (is_array($value)) {
			return collect($value)
				->filter(fn($v) => is_scalar($v) && trim((string)$v) !== '')
				->map(fn($v) => trim((string)$v))
				->unique()
				->values()
				->all();
		}

		$s = trim((string)$value);
		if ($s === '') return [];

		if (Str::startsWith($s, '[')) {
			try {
				$decoded = json_decode($s, true, 512, JSON_THROW_ON_ERROR);
				if (is_array($decoded)) {
					return collect($decoded)
						->filter(fn($v) => is_scalar($v) && trim((string)$v) !== '')
						->map(fn($v) => trim((string)$v))
						->unique()
						->values()
						->all();
				}
			} catch (\Throwable) {
				// no-op, fallback below
			}
		}

		$parts = collect(preg_split('/[,\n;]/', $s) ?: [])
			->map(fn($v) => trim((string)$v))
			->filter(fn($v) => $v !== '')
			->unique()
			->values()
			->all();

		return $parts;
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

		$r = random_int(1, max(1, $sum));
		$acc = 0;

		foreach ($weights as $k => $w) {
			$acc += $w;
			if ($r <= $acc) return (int)$k;
		}

		return $min;
	}
}
