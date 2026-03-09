<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\{DocumentKind, MimeType, UserType};
use App\Models\TaskFile;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TaskFileSeeder extends Seeder
{
	private const HARD_CAP = 512;

	private const GROUPS_PER_TASK_MIN = 1;
	private const GROUPS_PER_TASK_MAX = 16;

	private const NON_DOC_MIN = 1;
	private const NON_DOC_MAX = 4;

	private const DOC_MIN = 0;
	private const DOC_MAX = 8;

	private const UNIQUE_PAIR_MAX_ATTEMPTS = 16;
	private const FILL_MAX_ATTEMPTS_MULT = 16;

	private const SECONDS_LIMIT = 6 * 10 ** 2;

	private $output = null;

	public function run(): void
	{
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$clock = microtime(true);
		$io = $this->makeIo();
		$faker = FakerFactory::create('pt_BR');

		if (
			!Schema::hasTable(DC::TABLE_TSK_FL)
			|| !Schema::hasTable(DC::TABLE_TASKS)
		) {
			$io->warning('Missing required tables (task_files/tasks). Skipping TaskFileSeeder.');
			return;
		}

		$taskIds = $this->loadTaskIdsRaw();
		if ($taskIds->isEmpty()) {
			$io->warning('No tasks found; skipping TaskFileSeeder.');
			return;
		}

		$userIds = Schema::hasTable(DC::TABLE_USERS)
			? $this->loadUserIdsRaw()
			: collect();

		$userTypes = $this->userTypeValues();
		if ($userTypes === []) {
			$userTypes = [UserType::Customer->value];
		}

		$mimeCases = MimeType::cases();
		if ($mimeCases === []) {
			$io->warning('No MimeType cases found; skipping TaskFileSeeder.');
			return;
		}

		$docMimes = collect($mimeCases)->filter(fn(MimeType $m) => $m->isDocument())->values();
		$nonDocMimes = collect($mimeCases)->filter(fn(MimeType $m) => !$m->isDocument())->values();

		if ($nonDocMimes->isEmpty()) {
			$nonDocMimes = collect($mimeCases)->values();
		}

		$requested = $this->readCountOption();
		$defaultTarget = min(self::HARD_CAP, $this->roundUpTo64($taskIds->count() * 64));
		$target = $requested !== null
			? min(self::HARD_CAP, $this->roundUpTo64(max(64, $requested)))
			: max(64, $defaultTarget);

		$target = min(self::HARD_CAP, $target);
		$target = $this->fitToMultipleOf64WithinCap($target, self::HARD_CAP);

		$io->section('TaskFileSeeder plan');
		$io->text([
			'Tasks: ' . $taskIds->count(),
			'Users: ' . $userIds->count(),
			'User types: ' . count($userTypes),
			'Mime types: ' . count($mimeCases) . ' (doc=' . $docMimes->count() . ', non-doc=' . $nonDocMimes->count() . ')',
			'Requested (--count): ' . ($requested !== null ? (string) $requested : 'n/a'),
			'Target (multiple of 64, hard cap): ' . $target,
		]);

		$required = $this->requiredColumnsRaw(DC::TABLE_TSK_FL);
		$foreigns = $this->foreignKeyMapRaw(DC::TABLE_TSK_FL);
		$fkPools = $this->preloadRequiredFkPools($required, $foreigns, $userIds, $taskIds, $io);

		$created = 0;
		$utCursor = 0;
		$mimeCursor = 0;

		$byUserType = [];
		$byMime = [];

		$shuffledMimes = collect($mimeCases)->shuffle()->values();
		$hardCap = self::HARD_CAP;
		foreach ($taskIds as $taskId) {
			if (!$hardCap) break;
			$hardCap--;
			$groups = random_int(self::GROUPS_PER_TASK_MIN, self::GROUPS_PER_TASK_MAX);
			for ($g = 0; $g < $groups; $g++) {
				if (!$hardCap) break;
				$ut = $userTypes[$utCursor % count($userTypes)];
				$utCursor++;

				$mime = $shuffledMimes[$mimeCursor % $shuffledMimes->count()];
				$mimeCursor++;

				$count = $mime->isDocument()
					? random_int(self::DOC_MIN, self::DOC_MAX)
					: random_int(self::NON_DOC_MIN, self::NON_DOC_MAX);

				for ($i = 0; $i < $count; $i++) {

					if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						return;
					}
					$this->createOne(
						$faker,
						$taskId,
						$ut,
						$mime,
						$userIds,
						$required,
						$foreigns,
						$fkPools
					);

					$created++;
					$byUserType[$ut] = (int) ($byUserType[$ut] ?? 0) + 1;
					$byMime[$mime->value] = (int) ($byMime[$mime->value] ?? 0) + 1;

					if ($created >= $target) break 3;
				}
			}
		}

		$fillAttempts = 0;
		$fillMax = max(1024, $target * self::FILL_MAX_ATTEMPTS_MULT);

		while ($created < $target && $fillAttempts < $fillMax) {
			if (!$hardCap) break;
			$hardCap--;
			$fillAttempts++;

			$taskId = (string) $taskIds->random();
			$ut = $userTypes[$utCursor % count($userTypes)];
			$utCursor++;

			/** @var MimeType $mime */
			$mime = $nonDocMimes->random();

			$this->createOne(
				$faker,
				$taskId,
				$ut,
				$mime,
				$userIds,
				$required,
				$foreigns,
				$fkPools
			);

			$created++;
			$byUserType[$ut] = (int) ($byUserType[$ut] ?? 0) + 1;
			$byMime[$mime->value] = (int) ($byMime[$mime->value] ?? 0) + 1;
		}

		$io->section('TaskFileSeeder result');
		$io->text([
			'Created: ' . $created,
			'Target: ' . $target,
			'Hard cap: ' . self::HARD_CAP,
			'Fill attempts: ' . $fillAttempts,
		]);

		$io->section('Variation summary');
		$utLines = collect($byUserType)
			->sortKeys()
			->map(fn(int $v, string $k) => $k . ': ' . $v)
			->values()
			->all();

		$mimeTop = collect($byMime)
			->sortDesc()
			->take(12)
			->map(fn(int $v, string $k) => $k . ': ' . $v)
			->values()
			->all();

		$io->text(array_merge(
			['Per user_type:'],
			$utLines ?: ['(none)'],
			[''],
			['Top mime_type:'],
			$mimeTop ?: ['(none)']
		));
	}

	private function createOne(
		Faker $faker,
		string $taskId,
		string $userType,
		MimeType $mime,
		Collection $userIds,
		array $required,
		array $foreigns,
		array $fkPools
	): void {
		$ext = $mime->getExtension();
		$ext = $ext !== '' ? $ext : 'bin';

		[$name, $path] = $this->generateUniqueNameAndPath($taskId, $ext);

		$now = now();
		$createdAt = $now->copy()->subMinutes(random_int(0, 60 * 24 * 60));
		$updatedAt = (random_int(1, 100) <= 35)
			? $createdAt->copy()->addMinutes(random_int(1, 60 * 24 * 10))
			: $createdAt;

		$size = random_int(256, 25_000_000);

		$lastAccessed = (random_int(1, 100) <= 40)
			? $now->copy()->subMinutes(random_int(0, 60 * 24 * 30))
			: null;

		$exp = (random_int(1, 100) <= 12)
			? $now->copy()->addDays(random_int(1, 365))
			: null;

		$type = null;
		if ($mime->isDocument() && random_int(1, 100) <= 80) {
			try {
				$type = DocumentKind::fromMime($mime)->value;
			} catch (\Throwable) {
				$type = null;
			}
		}

		$perm = $this->randomPermissionRules();

		$creator = ($userIds->isNotEmpty() && random_int(1, 100) <= 70) ? (string) $userIds->random() : null;
		$updater = ($userIds->isNotEmpty() && random_int(1, 100) <= 35) ? (string) $userIds->random() : null;

		$legacyFile = (random_int(1, 100) <= 55) ? $path : null;

		$attrs = [
			AC::COL_TSK_ID => $taskId,
			UC::COL_U_TP   => $userType,

			'file'          => $legacyFile,
			DC::COL_FL_PT   => (random_int(1, 100) <= 85) ? $path : null,
			'name'          => $name,
			'extension'     => $ext,
			DC::COL_MM_TP   => $mime->value,
			DC::COL_LA      => $lastAccessed,
			'size'          => $size,
			'description'   => (random_int(1, 100) <= 60) ? $faker->sentence(random_int(6, 14)) : null,
			'notes'         => (random_int(1, 100) <= 40) ? $faker->sentence(random_int(6, 14)) : null,
			DC::COL_DL_CT   => random_int(0, 250),
			DC::COL_FL_SZ   => (float) $size,
			DC::COL_PERM_RLS => $perm,
			'executors'     => null,
			'editors'       => null,
			'viewers'       => null,
			DC::COL_EXP_DT  => $exp,
			'type'          => $type,

			DC::COL_TABLE_CREATOR => $creator,
			DC::COL_TABLE_UPDATER => $updater,
			DC::COL_C_AT          => $createdAt,
			DC::COL_U_AT          => $updatedAt,
		];

		$this->fillMissingRequiredColumns($attrs, $required, $foreigns, $fkPools, $userIds, $taskId);

		$m = new TaskFile();
		foreach ($attrs as $k => $v) {
			$m->setAttribute($k, $v);
		}
		$this->output && $this->output->writeln('Creating TaskFile for task ' . $taskId . ' with name ' . $name . ' mime' . $mime->value);
		$m->save();
	}

	private function generateUniqueNameAndPath(string $taskId, string $ext): array
	{
		$attempts = 0;

		do {
			$attempts++;

			$uuid = (string) Str::uuid();
			$path = 'uploads/tasks/' . $taskId . '/' . $uuid . '.' . $ext;
			$name = 'FILE_' . $uuid . '_' . now()->timestamp . '.' . $ext;

			$row = DB::selectOne(
				'select 1 as x from ' . DC::TABLE_TSK_FL . ' where name = ? and ' . DC::COL_FL_PT . ' = ? limit 1',
				[$name, $path]
			);

			if ($row === null) return [$name, $path];
		} while ($attempts < self::UNIQUE_PAIR_MAX_ATTEMPTS);

		$uuid = (string) Str::uuid();
		$path = 'uploads/tasks/' . $taskId . '/' . $uuid . '.' . $ext;
		$name = 'FILE_' . $uuid . '_' . now()->timestamp . '_' . random_int(1000, 9999) . '.' . $ext;

		return [$name, $path];
	}

	private function randomPermissionRules(): string
	{
		$digits = [];
		for ($i = 0; $i < 6; $i++) {
			$digits[] = (string) random_int(0, 7);
		}
		return implode('', $digits);
	}

	private function userTypeValues(): array
	{
		if (method_exists(UserType::class, 'values')) {
			$v = UserType::values();
			if (is_array($v) && $v !== []) return array_values($v);
		}

		return array_values(array_map(
			fn(UserType $c) => $c->value,
			UserType::cases()
		));
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
					$n = (int) $v;
					return $n > 0 ? $n : null;
				}
			}
		} catch (\Throwable) {
			return null;
		}

		return null;
	}

	private function roundUpTo64(int $n): int
	{
		if ($n <= 0) return 64;
		$mod = $n % 64;
		return $mod === 0 ? $n : ($n + (64 - $mod));
	}

	private function fitToMultipleOf64WithinCap(int $n, int $cap): int
	{
		$cap = max(0, $cap);
		if ($cap === 0) return 0;

		$n = max(0, min($n, $cap));
		if ($n === 0) return 0;

		$up = $this->roundUpTo64($n);
		if ($up <= $cap) return $up;

		$down = (int) (floor($cap / 64) * 64);
		return $down > 0 ? $down : min(64, $cap);
	}

	/**
	 * @return Collection<int,string>
	 */
	private function loadTaskIdsRaw(): Collection
	{
		$rows = DB::select('select id from ' . DC::TABLE_TASKS);
		$out = collect();

		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
			if ($id !== '') $out->push($id);
		}

		return $out->unique()->values();
	}

	/**
	 * @return Collection<int,string>
	 */
	private function loadUserIdsRaw(): Collection
	{
		$rows = DB::select('select id from ' . DC::TABLE_USERS);
		$out = collect();

		foreach ($rows as $r) {
			$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
			if ($id !== '') $out->push($id);
		}

		return $out->unique()->values();
	}

	/**
	 * @return array<string,array{data_type:string,is_nullable:string,column_default:mixed}>
	 */
	private function requiredColumnsRaw(string $table): array
	{
		$out = [];

		try {
			$db = DB::selectOne('select database() as db');
			$schema = is_scalar($db->db ?? null) ? (string) $db->db : '';
			if ($schema === '') return $out;

			$rows = DB::select(
				'select column_name, data_type, is_nullable, column_default
                 from information_schema.columns
                 where table_schema = ? and table_name = ?',
				[$schema, $table]
			);

			foreach ($rows as $r) {
				$col = is_scalar($r->column_name ?? null) ? (string) $r->column_name : '';
				if ($col === '') continue;

				$isNullable = strtoupper((string) ($r->is_nullable ?? 'YES'));
				$hasDefault = $r->column_default !== null;

				if ($isNullable === 'NO' && !$hasDefault && $col !== 'id') {
					$out[$col] = [
						'data_type' => (string) ($r->data_type ?? ''),
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
	 * @return array<string,string>
	 */
	private function foreignKeyMapRaw(string $table): array
	{
		$out = [];

		try {
			$db = DB::selectOne('select database() as db');
			$schema = is_scalar($db->db ?? null) ? (string) $db->db : '';
			if ($schema === '') return $out;

			$rows = DB::select(
				'select column_name, referenced_table_name
                 from information_schema.key_column_usage
                 where table_schema = ? and table_name = ?
                   and referenced_table_name is not null',
				[$schema, $table]
			);

			foreach ($rows as $r) {
				$col = is_scalar($r->column_name ?? null) ? (string) $r->column_name : '';
				$ref = is_scalar($r->referenced_table_name ?? null) ? (string) $r->referenced_table_name : '';
				if ($col !== '' && $ref !== '') $out[$col] = $ref;
			}
		} catch (\Throwable) {
			return $out;
		}

		return $out;
	}

	private function preloadRequiredFkPools(
		array $required,
		array $foreigns,
		Collection $userIds,
		Collection $taskIds,
		SymfonyStyle $io
	): array {
		$pools = [];

		foreach ($foreigns as $col => $refTable) {
			if (!isset($required[$col])) continue;

			if ($refTable === DC::TABLE_USERS) {
				if ($userIds->isEmpty()) {
					$io->warning("Required FK '{$col}' needs users, but users table is empty/missing. Aborting seeder.");
					return [];
				}
				$pools[$refTable] = $userIds;
				continue;
			}

			if ($refTable === DC::TABLE_TASKS) {
				if ($taskIds->isEmpty()) {
					$io->warning("Required FK '{$col}' needs tasks, but tasks table is empty. Aborting seeder.");
					return [];
				}
				$pools[$refTable] = $taskIds;
				continue;
			}

			if (!Schema::hasTable($refTable)) {
				$io->warning("Required FK '{$col}' references missing table '{$refTable}'. Aborting seeder.");
				return [];
			}

			$ids = $this->loadIdPoolRaw($refTable);
			if ($ids->isEmpty()) {
				$io->warning("Required FK '{$col}' references '{$refTable}' but it has no rows. Aborting seeder.");
				return [];
			}

			$pools[$refTable] = $ids;
		}

		return $pools;
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
			$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
			if ($id !== '') $out->push($id);
		}

		return $out->unique()->values();
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	private function fillMissingRequiredColumns(
		array &$attrs,
		array $required,
		array $foreigns,
		array $fkPools,
		Collection $userIds,
		string $taskId
	): void {
		foreach ($required as $col => $meta) {
			if (array_key_exists($col, $attrs)) continue;

			if ($col === AC::COL_TSK_ID) {
				$attrs[$col] = $taskId;
				continue;
			}

			if (isset($foreigns[$col])) {
				$ref = $foreigns[$col];
				$pool = $fkPools[$ref] ?? null;

				if ($pool instanceof Collection && $pool->isNotEmpty()) {
					$attrs[$col] = (string) $pool->random();
					continue;
				}

				if ($ref === DC::TABLE_USERS && $userIds->isNotEmpty()) {
					$attrs[$col] = (string) $userIds->random();
					continue;
				}

				continue;
			}

			$type = strtolower(trim((string) ($meta['data_type'] ?? '')));

			if (in_array($type, ['datetime', 'timestamp'], true)) {
				$attrs[$col] = now();
				continue;
			}
			if ($type === 'date') {
				$attrs[$col] = now()->toDateString();
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

			if (str_contains($type, 'char') || str_contains($type, 'text') || $type === 'varchar') {
				$attrs[$col] = 'seed_' . Str::random(12);
				continue;
			}

			$attrs[$col] = 0;
		}
	}
}
