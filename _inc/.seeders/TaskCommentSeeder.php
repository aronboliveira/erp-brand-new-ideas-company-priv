<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\UserType;
use App\Models\TaskComment;
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

final class TaskCommentSeeder extends Seeder
{
	private const MAX_COMMENTS_PER_TASK = 16;

	// private const HARD_CAP = 6400;
	private const HARD_CAP = 2;

	private const AUTHOR_SAMPLE_RATIO = 0.05; // 5%

	private const UNIQUE_REFERENCE_MAX_ATTEMPTS = 12;
	private const PLAN_ADJUST_MAX_STEPS = 200000;

	public function run(): void
	{
		$io = $this->makeIo();
		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$faker = FakerFactory::create('pt_BR');

		if (!Schema::hasTable(DC::TABLE_TSK_CMT) || !Schema::hasTable(DC::TABLE_TASKS) || !Schema::hasTable(DC::TABLE_USERS)) {
			$io->warning('Missing required tables (task_comments/tasks/users). Skipping TaskCommentSeeder.');
			return;
		}

		$table = DC::TABLE_TSK_CMT;

		$tasks = $this->loadTaskIdsRaw();
		if ($tasks->isEmpty()) {
			$io->warning('No tasks found; skipping TaskCommentSeeder.');
			return;
		}

		$users = $this->loadUserPoolRaw();
		if ($users->isEmpty()) {
			$io->warning('No users found; skipping TaskCommentSeeder.');
			return;
		}

		$authors = $this->pickAuthorsSubset($users);
		if ($authors->isEmpty()) {
			$io->warning('Author subset is empty; skipping TaskCommentSeeder.');
			return;
		}

		// ---- Required columns/foreigns (defensive: fills any NOT NULL + no default columns) ----
		$required = $this->requiredColumnsRaw($table);              // col => ['type'=>..., 'nullable'=>..., 'default'=>...]
		$foreigns = $this->foreignKeyMapRaw($table);                // col => referenced_table

		// Preflight: ensure required FK pools exist
		$fkPools = [];
		foreach ($foreigns as $col => $refTable) {
			if (!isset($required[$col])) continue;
			if (!Schema::hasTable($refTable)) {
				$io->warning("Required FK column '{$col}' references missing table '{$refTable}'. Aborting seeder.");
				return;
			}
			$pool = $this->loadIdPoolRaw($refTable);
			if ($pool->isEmpty()) {
				$io->warning("Required FK column '{$col}' references '{$refTable}' but it has no rows. Aborting seeder.");
				return;
			}
			$fkPools[$refTable] = $pool;
		}

		// ---- Build per-task counts: 0..16 inverted quadratic (tends to low) ----
		$countsByTask = [];
		$rawTotal = 0;

		foreach ($tasks as $tid) {
			$n = $this->sampleInvertedQuadratic(0, self::MAX_COMMENTS_PER_TASK);
			$countsByTask[$tid] = $n;
			$rawTotal += $n;
		}

		// Default seeding rule (project): 64 * N, where N is the number of base entities (tasks), but capped.
		$baseMin = 64 * max(1, $tasks->count());
		$baseMin = min(self::HARD_CAP, $baseMin);

		// CLI override: --count is treated as minimum (still obey hard cap)
		$requested = $this->readCountOption();
		$requested = $requested !== null ? min(self::HARD_CAP, max(0, $requested)) : null;

		$target = max($rawTotal, $baseMin);
		if ($requested !== null) $target = max($target, $requested);

		// Respect capacity (tasks * 16) and hard cap
		$capacity = min(self::HARD_CAP, $tasks->count() * self::MAX_COMMENTS_PER_TASK);
		$target = min($target, $capacity);

		// Enforce multiple-of-64, but never exceed capacity/hard cap.
		$target = $this->fitToMultipleOf64WithinCap($target, $capacity);

		// Adjust the per-task counts to reach exactly $target without exceeding 16 per task.
		$countsByTask = $this->adjustCountsToTarget($countsByTask, $target);

		$finalTotal = array_sum($countsByTask);

		// Final safeguard: still enforce multiple-of-64 and <= cap
		$finalTotal = min($finalTotal, $capacity);
		$finalTotal = $this->fitToMultipleOf64WithinCap($finalTotal, $capacity);
		if ($finalTotal !== array_sum($countsByTask)) {
			$countsByTask = $this->adjustCountsToTarget($countsByTask, $finalTotal);
		}

		$io->section('TaskCommentSeeder plan');
		$io->text([
			'Tasks: ' . $tasks->count(),
			'Users: ' . $users->count(),
			'Authors (5%): ' . $authors->count(),
			'Raw total (initial): ' . $rawTotal,
			'Base minimum (64*N capped): ' . $baseMin,
			'Requested (--count, capped): ' . ($requested !== null ? (string)$requested : 'n/a'),
			'Capacity (tasks*16, hard cap): ' . $capacity,
			'Final target (multiple of 64): ' . $finalTotal,
		]);

		// ---- Create comments ----
		$created = 0;
		$byAuthor = [];
		$byTask = [];

		// Track per-task created IDs to generate reply chains
		$createdIdsByTask = [];

		$enumValues = array_column(UserType::cases(), 'value');

		foreach ($countsByTask as $taskId => $n) {
			if ($n <= 0) continue;

			$threadId = (string)Str::uuid();
			$createdIdsByTask[$taskId] = [];

			for ($i = 0; $i < $n; $i++) {
				$author = $authors->random();
				$authorId = (string)($author['id'] ?? '');
				if ($authorId === '') continue;

				// user_type: prefer user row value if present/valid, else random enum
				$authorType = $author['user_type'] ?? null;
				$ut = is_string($authorType) && in_array($authorType, $enumValues, true)
					? $authorType
					: (string)$enumValues[array_rand($enumValues)];

				$isReply = false;
				$parentId = null;
				$depth = 0;

				// Replies only if we already have at least 1 comment on the task
				if (!empty($createdIdsByTask[$taskId]) && random_int(1, 100) <= 18) { // ~18% replies
					$isReply = true;
					$parentId = (string)$createdIdsByTask[$taskId][array_rand($createdIdsByTask[$taskId])];
					$depth = random_int(1, 3);
				}

				$isDeleted = (random_int(1, 100) <= 5);  // ~5%
				$isEdited  = (random_int(1, 100) <= 12); // ~12%
				$flagged   = (random_int(1, 100) <= 6);  // ~6%

				$deleterId = null;
				$deletedAt = null;

				if ($isDeleted) {
					$deleter = $users->random();
					$deleterId = is_string($deleter['id'] ?? null) ? (string)$deleter['id'] : null;
					$deletedAt = now()->subMinutes(random_int(1, 60 * 24 * 30));
				}

				$edits = null;
				$editCount = 0;

				if ($isEdited) {
					$editCount = random_int(1, 4);
					$edits = [];
					for ($e = 0; $e < $editCount; $e++) {
						$edits[] = [
							'at'         => now()->subMinutes(random_int(1, 60 * 24 * 7))->toISOString(),
							'previous'   => $faker->sentence(random_int(6, 18)),
							'editor_id'  => (random_int(1, 100) <= 80) ? $authorId : null,
							'updater_id' => null,
						];
					}
				}

				$reactions = null;
				if (random_int(1, 100) <= 35) { // ~35%
					$reactions = [
						['emoji' => '👍', 'count' => random_int(1, 5)],
					];
					if (random_int(1, 100) <= 30) {
						$reactions[] = ['emoji' => '❤️', 'count' => random_int(1, 3)];
					}
				}

				$tags = $this->randomStringList($faker, 0, 6, prefix: 'tag_');
				$attachments = $this->randomStringList($faker, 0, 2, prefix: 'file_');

				$reference = null;
				if (random_int(1, 100) <= 70) { // ~70% with reference
					$reference = $this->generateUniqueReferenceWithExists($table);
				}

				$time = now()->subSeconds(random_int(0, 60 * 60 * 24 * 60));

				$attrs = [
					// Task-connected (must exist; nullable=false in migration)
					AC::COL_TSK_ID   => $taskId,

					// Comment columns
					'time'           => $time,
					'comment'        => $faker->sentence(random_int(8, 22)),
					'reference'      => $reference,
					UC::COL_USER_ID  => $authorId,
					UC::COL_U_TP     => $ut,

					AC::COL_IS_EDT   => $isEdited,
					AC::COL_IS_DEL   => $isDeleted,
					'deleter'        => $deleterId,
					AC::COL_DEL_AT   => $deletedAt,

					AC::COL_EDT_CNT  => $editCount,
					'flagged'        => $flagged,

					'thread'         => $threadId,
					AC::COL_IS_RPL   => $isReply,
					AC::COL_RPL_CNT  => 0,

					'order'          => $i,
					'depth'          => $depth,
					'parent'         => $parentId,

					'attachments'    => $attachments,
					'tags'           => $tags,
					'reactions'      => $reactions,
					'replies'        => null,
					'edits'          => $edits,
					'metadata'       => [
						'seed' => 'TaskCommentSeeder',
						'v'    => 1,
					],
				];

				// Ensure any NOT NULL + no-default columns are populated (including any extra TaskConnected fields)
				$this->fillMissingRequiredColumns($attrs, $required, $foreigns, $fkPools, $users, $tasks);

				$m = new TaskComment();
				foreach ($attrs as $k => $v) {
					// Do NOT strip nulls for nullable columns
					$m->setAttribute($k, $v);
				}
				$output->writeln('Creating TaskComment for task ' . $taskId . ' by author ' . $authorId);
				$m->save();

				$created++;
				$createdIdsByTask[$taskId][] = (string)$m->getAttribute('id');

				$byTask[$taskId] = (int)($byTask[$taskId] ?? 0) + 1;
				$byAuthor[$authorId] = (int)($byAuthor[$authorId] ?? 0) + 1;

				if ($created >= $finalTotal) break 2; // hard stop
			}
		}

		$io->section('TaskCommentSeeder result');
		$io->text([
			'Created: ' . $created,
			'Hard cap: ' . self::HARD_CAP,
			'Tasks touched: ' . count($byTask),
			'Authors used: ' . count($byAuthor),
		]);

		// Short summary (min/max/avg per task) without dumping full maps
		if ($byTask !== []) {
			$vals = array_values($byTask);
			sort($vals);
			$min = (int)($vals[0] ?? 0);
			$max = (int)($vals[count($vals) - 1] ?? 0);
			$avg = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : 0.0;

			$io->text([
				'Per-task comments: min=' . $min . ', max=' . $max . ', avg=' . $avg,
			]);
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
	 * @return Collection<int,string>
	 */
	private function loadTaskIdsRaw(): Collection
	{
		$rows = DB::select('select id from ' . DC::TABLE_TASKS);
		$out = collect();

		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string)$r->id : '';
			if ($id !== '') $out->push($id);
		}

		return $out->unique()->values();
	}

	/**
	 * @return Collection<int,array{id:string,user_type:?string}>
	 */
	private function loadUserPoolRaw(): Collection
	{
		$cols = Schema::getColumnListing(DC::TABLE_USERS);

		// Try common columns in descending preference
		$typeCol = null;
		foreach ([UC::COL_U_TP, 'type', 'user_type'] as $c) {
			if (in_array($c, $cols, true)) {
				$typeCol = $c;
				break;
			}
		}

		$select = $typeCol ? 'select id, ' . $typeCol . ' as user_type from ' . DC::TABLE_USERS
			: 'select id from ' . DC::TABLE_USERS;

		$rows = DB::select($select);

		$out = collect();
		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string)$r->id : '';
			if ($id === '') continue;

			$ut = null;
			if (property_exists($r, 'user_type')) {
				$s = trim((string)($r->user_type ?? ''));
				$ut = $s !== '' ? $s : null;
			}

			$out->push([
				'id' => $id,
				'user_type' => $ut,
			]);
		}

		return $out->values();
	}

	/**
	 * @param Collection<int,array{id:string,user_type:?string}> $users
	 * @return Collection<int,array{id:string,user_type:?string}>
	 */
	private function pickAuthorsSubset(Collection $users): Collection
	{
		$n = (int)floor($users->count() * self::AUTHOR_SAMPLE_RATIO);
		if ($n < 1) $n = 1;

		// shuffle via collection methods (shape-consistent)
		return $users->shuffle()->take($n)->values();
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

	private function fitToMultipleOf64WithinCap(int $n, int $cap): int
	{
		if ($cap <= 0) return 0;

		$cap = min($cap, self::HARD_CAP);
		$n = max(0, min($n, $cap));

		if ($n === 0) return 0;

		$up = $this->adjustUpToMultipleOf64($n);
		if ($up <= $cap) return $up;

		// If rounding up exceeds cap, round down
		$down = (int)(floor($cap / 64) * 64);
		if ($down <= 0) $down = min(64, $cap);

		return $down;
	}

	private function adjustUpToMultipleOf64(int $n): int
	{
		if ($n <= 0) return 64;
		$mod = $n % 64;
		return $mod === 0 ? $n : ($n + (64 - $mod));
	}

	/**
	 * Adjust per-task counts to reach target, respecting [0..16].
	 *
	 * @param array<string,int> $countsByTask
	 * @return array<string,int>
	 */
	private function adjustCountsToTarget(array $countsByTask, int $target): array
	{
		$current = array_sum($countsByTask);
		if ($current === $target) return $countsByTask;

		$steps = 0;

		if ($current < $target) {
			$need = $target - $current;

			while ($need > 0 && $steps < self::PLAN_ADJUST_MAX_STEPS) {
				$steps++;

				// candidate tasks with room (<16), biased to lower current counts
				$candidates = [];
				$sumW = 0;

				foreach ($countsByTask as $tid => $c) {
					if ($c >= self::MAX_COMMENTS_PER_TASK) continue;
					$w = (int)pow((self::MAX_COMMENTS_PER_TASK - $c), 2);
					if ($w < 1) $w = 1;
					$candidates[] = [$tid, $w];
					$sumW += $w;
				}

				if ($candidates === []) break;

				$pickTid = $this->weightedPickKey($candidates, $sumW);
				if ($pickTid === null) break;

				$countsByTask[$pickTid] = (int)$countsByTask[$pickTid] + 1;
				$need--;
			}
		} else {
			$drop = $current - $target;

			while ($drop > 0 && $steps < self::PLAN_ADJUST_MAX_STEPS) {
				$steps++;

				// candidate tasks with >0, biased to higher counts
				$candidates = [];
				$sumW = 0;

				foreach ($countsByTask as $tid => $c) {
					if ($c <= 0) continue;
					$w = (int)pow($c, 2);
					if ($w < 1) $w = 1;
					$candidates[] = [$tid, $w];
					$sumW += $w;
				}

				if ($candidates === []) break;

				$pickTid = $this->weightedPickKey($candidates, $sumW);
				if ($pickTid === null) break;

				$countsByTask[$pickTid] = (int)$countsByTask[$pickTid] - 1;
				$drop--;
			}
		}

		return $countsByTask;
	}

	/**
	 * @param array<int,array{0:string,1:int}> $candidates
	 */
	private function weightedPickKey(array $candidates, int $sumW): ?string
	{
		$sumW = max(1, $sumW);
		$r = random_int(1, $sumW);
		$acc = 0;

		foreach ($candidates as [$tid, $w]) {
			$acc += max(1, (int)$w);
			if ($r <= $acc) return (string)$tid;
		}

		return isset($candidates[0][0]) ? (string)$candidates[0][0] : null;
	}

	/**
	 * Unique-ish reference, with do/while exists check (raw SQL), break-out strategy included.
	 */
	private function generateUniqueReferenceWithExists(string $table): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$candidate = 'TSK-CMT-' . (string)Str::uuid();

			$row = DB::selectOne(
				'select 1 as x from ' . $table . ' where reference = ? limit 1',
				[$candidate]
			);

			if ($row === null) return $candidate;
		} while ($attempts < self::UNIQUE_REFERENCE_MAX_ATTEMPTS);

		return 'TSK-CMT-' . (string)Str::uuid();
	}

	/**
	 * Return a list of unique strings (for JSON casts).
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

	/**
	 * information_schema: identify NOT NULL columns with no default.
	 *
	 * @return array<string,array{data_type:string,is_nullable:string,column_default:mixed}>
	 */
	private function requiredColumnsRaw(string $table): array
	{
		$out = [];

		try {
			$db = DB::selectOne('select database() as db');
			$schema = is_scalar($db->db ?? null) ? (string)$db->db : '';
			if ($schema === '') return $out;

			$rows = DB::select(
				'select column_name, data_type, is_nullable, column_default
                 from information_schema.columns
                 where table_schema = ? and table_name = ?',
				[$schema, $table]
			);

			foreach ($rows as $r) {
				$col = is_scalar($r->column_name ?? null) ? (string)$r->column_name : '';
				if ($col === '') continue;

				$isNullable = strtoupper((string)($r->is_nullable ?? 'YES'));
				$hasDefault = $r->column_default !== null;

				// Required means: NOT NULL and no default and not primary id handled by model trait
				if ($isNullable === 'NO' && !$hasDefault && $col !== 'id') {
					$out[$col] = [
						'data_type' => (string)($r->data_type ?? ''),
						'is_nullable' => $isNullable,
						'column_default' => $r->column_default,
					];
				}
			}
		} catch (\Throwable) {
			return $out;
		}

		return $out;
	}

	/**
	 * information_schema: map FK column -> referenced table.
	 *
	 * @return array<string,string>
	 */
	private function foreignKeyMapRaw(string $table): array
	{
		$out = [];

		try {
			$db = DB::selectOne('select database() as db');
			$schema = is_scalar($db->db ?? null) ? (string)$db->db : '';
			if ($schema === '') return $out;

			$rows = DB::select(
				'select column_name, referenced_table_name
                 from information_schema.key_column_usage
                 where table_schema = ? and table_name = ?
                   and referenced_table_name is not null',
				[$schema, $table]
			);

			foreach ($rows as $r) {
				$col = is_scalar($r->column_name ?? null) ? (string)$r->column_name : '';
				$ref = is_scalar($r->referenced_table_name ?? null) ? (string)$r->referenced_table_name : '';
				if ($col !== '' && $ref !== '') $out[$col] = $ref;
			}
		} catch (\Throwable) {
			return $out;
		}

		return $out;
	}

	/**
	 * @return Collection<int,string>
	 */
	private function loadIdPoolRaw(string $table): Collection
	{
		if (!Schema::hasTable($table)) return collect();

		$rows = DB::select('select id from ' . $table);
		$out = collect();

		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string)$r->id : '';
			if ($id !== '') $out->push($id);
		}

		return $out->unique()->values();
	}

	/**
	 * Fills any required columns that are missing in $attrs.
	 * - If FK: uses referenced table pools.
	 * - Else: uses conservative defaults per data_type.
	 *
	 * IMPORTANT: does not strip nulls for nullable columns (and does not use array_filter()).
	 *
	 * @param array<string,mixed> $attrs
	 * @param array<string,array{data_type:string,is_nullable:string,column_default:mixed}> $required
	 * @param array<string,string> $foreigns
	 * @param array<string,Collection<int,string>> $fkPools
	 * @param Collection<int,array{id:string,user_type:?string}> $users
	 * @param Collection<int,string> $tasks
	 */
	private function fillMissingRequiredColumns(
		array &$attrs,
		array $required,
		array $foreigns,
		array $fkPools,
		Collection $users,
		Collection $tasks
	): void {
		foreach ($required as $col => $meta) {
			if (array_key_exists($col, $attrs)) {
				// If explicitly set, keep as-is (even null); DB will enforce if not allowed.
				continue;
			}

			// If it's the task id column (already set elsewhere), force it from attrs if possible
			if ($col === AC::COL_TSK_ID) {
				$attrs[$col] = $attrs[AC::COL_TSK_ID] ?? (string)$tasks->random();
				continue;
			}

			// FK-backed required columns: pick an existing id from referenced table
			if (isset($foreigns[$col])) {
				$ref = $foreigns[$col];
				$pool = $fkPools[$ref] ?? null;

				if ($pool instanceof Collection && $pool->isNotEmpty()) {
					$attrs[$col] = (string)$pool->random();
					continue;
				}

				// last resort: if FK is to users/tasks and pool not prepared, still try local pools
				if ($ref === DC::TABLE_USERS && $users->isNotEmpty()) {
					$attrs[$col] = (string)($users->random()['id'] ?? '');
					continue;
				}
				if ($ref === DC::TABLE_TASKS && $tasks->isNotEmpty()) {
					$attrs[$col] = (string)$tasks->random();
					continue;
				}

				// If we reach here, seeding would likely fail; leave unset to trigger a clear DB error.
				continue;
			}

			$type = strtolower(trim((string)($meta['data_type'] ?? '')));

			// Conservative defaults by type
			if (in_array($type, ['datetime', 'timestamp'], true)) {
				$attrs[$col] = now();
				continue;
			}
			if ($type === 'date') {
				$attrs[$col] = now()->toDateString();
				continue;
			}
			if ($type === 'time') {
				$attrs[$col] = now()->format('H:i:s');
				continue;
			}
			if (in_array($type, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'integer'], true)) {
				$attrs[$col] = 0;
				continue;
			}
			if (in_array($type, ['decimal', 'float', 'double'], true)) {
				$attrs[$col] = 0;
				continue;
			}
			if ($type === 'json') {
				$attrs[$col] = [];
				continue;
			}

			// For strings/uuids/enums/etc.
			if (str_contains($type, 'char') || str_contains($type, 'text') || $type === 'varchar') {
				$attrs[$col] = 'seed_' . Str::random(12);
				continue;
			}

			// Default fallback
			$attrs[$col] = 0;
		}
	}
}
