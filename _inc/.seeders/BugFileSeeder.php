<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{DocumentKind, MimeType, UserType};
use App\Models\BugFile;
use App\Traits\EnsuresSystemUser;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BugFileSeeder extends Seeder
{
	use EnsuresSystemUser;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	// private const HARD_CAP = 800;
	private const HARD_CAP = 2;

	// “Mesmo loop” do TaskFile: por bug, 1..16 “grupos” variando user_type + mime_type
	private const GROUPS_PER_BUG_MIN = 1;
	private const GROUPS_PER_BUG_MAX = 8;

	// Por mime:
	private const NON_DOC_MIN = 1;
	private const NON_DOC_MAX = 4;
	private const DOC_MIN = 0;
	private const DOC_MAX = 8;

	// Safety / break-outs
	private const UNIQUE_ID_MAX_ATTEMPTS = 16;
	private const UNIQUE_PAIR_MAX_ATTEMPTS = 16;
	private const FILL_MAX_ATTEMPTS_MULT = 64;

	public function run(): void
	{
		$clock = microtime(true);
		$cout = new ConsoleOutput();
		$io = $this->makeIo($cout);
		$faker = FakerFactory::create('pt_BR');

		if (!Schema::hasTable(DC::TABLE_BG_FL) || !Schema::hasTable(DC::TABLE_BUGS)) {
			$io->warning('Missing required tables (bug_files/bugs). Skipping BugFileSeeder.');
			return;
		}

		$bugIds = $this->loadBugIdsRaw();
		if ($bugIds->isEmpty()) {
			$io->warning('No bugs found; skipping BugFileSeeder.');
			return;
		}

		$mimeCases = MimeType::cases();
		if ($mimeCases === []) {
			$io->warning('No MimeType cases found; skipping BugFileSeeder.');
			return;
		}

		$userTypes = UserType::cases();
		if ($userTypes === []) {
			$io->warning('No UserType cases found; skipping BugFileSeeder.');
			return;
		}

		$systemUserId = $this->ensureSystemUser();

		$requested = $this->readCountOption();
		$defaultTarget = min(self::HARD_CAP, $this->roundUpTo64($bugIds->count() * 64));

		$target = $requested !== null
			? min(self::HARD_CAP, $this->roundUpTo64(max(64, $requested)))
			: max(64, $defaultTarget);

		$target = $this->fitToMultipleOf64WithinCap($target, self::HARD_CAP);

		$required = $this->requiredColumnsRaw(DC::TABLE_BG_FL);
		$foreigns = $this->foreignKeyMapRaw(DC::TABLE_BG_FL);
		$fkPools = $this->preloadRequiredFkPools($required, $foreigns, $bugIds, $io);
		if ($fkPools === null) {
			$io->warning('Failed to preload required FK pools. Skipping BugFileSeeder.');
			return;
		}

		$docMimes = collect($mimeCases)->filter(fn(MimeType $m) => $m->isDocument())->values();
		$nonDocMimes = collect($mimeCases)->filter(fn(MimeType $m) => !$m->isDocument())->values();
		if ($nonDocMimes->isEmpty()) $nonDocMimes = collect($mimeCases)->values();

		$io->section('BugFileSeeder plan');
		$io->text([
			'Bugs: ' . $bugIds->count(),
			'User types: ' . count($userTypes),
			'Mime types: ' . count($mimeCases),
			'Requested (--count): ' . ($requested !== null ? (string) $requested : 'n/a'),
			'Target (multiple of 64, hard cap): ' . $target,
		]);

		DB::transaction(function () use (
			$cout,
			$io,
			$faker,
			$bugIds,
			$userTypes,
			$mimeCases,
			$docMimes,
			$nonDocMimes,
			$systemUserId,
			$target,
			$required,
			$foreigns,
			$fkPools,
			&$clock
		): void {
			$created = 0;

			$byType = [];
			$byMime = [];

			// Para espalhar variação (não ficar só no começo da lista):
			$bugs = $bugIds->shuffle()->values();
			$mimes = collect($mimeCases)->shuffle()->values();
			$types = collect($userTypes)->shuffle()->values();

			$mimeCursor = 0;
			$typeCursor = 0;

			foreach ($bugs as $bugId) {
				$groups = random_int(self::GROUPS_PER_BUG_MIN, self::GROUPS_PER_BUG_MAX);

				for ($g = 0; $g < $groups; $g++) {
					/** @var UserType $userType */
					$userType = $types[$typeCursor % $types->count()];
					$typeCursor++;

					/** @var MimeType $mime */
					$mime = $mimes[$mimeCursor % $mimes->count()];
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
							$cout,
							$faker,
							$bugId,
							$systemUserId,
							$userType,
							$mime,
							$required,
							$foreigns,
							$fkPools
						);

						$created++;
						$byType[$userType->value] = (int) ($byType[$userType->value] ?? 0) + 1;
						$byMime[$mime->value] = (int) ($byMime[$mime->value] ?? 0) + 1;

						if ($created >= $target) break 3;
					}
				}
			}

			// Preenchimento até target (com break-out):
			$fillAttempts = 0;
			$fillMax = max(1024, $target * self::FILL_MAX_ATTEMPTS_MULT);

			while ($created < $target && $fillAttempts < $fillMax) {
				$fillAttempts++;

				$bugId = (string) $bugIds->random();
				/** @var UserType $userType */
				$userType = (random_int(1, 100) <= 55)
					? $userTypes[array_rand($userTypes)]
					: UserType::Client;

				/** @var MimeType $mime */
				$mime = (random_int(1, 100) <= 70)
					? $nonDocMimes->random()
					: ($docMimes->isNotEmpty() ? $docMimes->random() : $nonDocMimes->random());

				$this->createOne(
					$cout,
					$faker,
					$bugId,
					$systemUserId,
					$userType,
					$mime,
					$required,
					$foreigns,
					$fkPools
				);

				$created++;
				$byType[$userType->value] = (int) ($byType[$userType->value] ?? 0) + 1;
				$byMime[$mime->value] = (int) ($byMime[$mime->value] ?? 0) + 1;
			}

			$io->section('BugFileSeeder result');
			$io->text([
				'Created: ' . $created,
				'Target: ' . $target,
				'Hard cap: ' . self::HARD_CAP,
				'Fill attempts: ' . $fillAttempts,
			]);

			$io->section('Variation summary (top user_type)');
			$io->text(
				collect($byType)->sortDesc()->take(12)->map(
					fn(int $v, string $k) => $k . ': ' . $v
				)->values()->all() ?: ['(none)']
			);

			$io->section('Variation summary (top mime_type)');
			$io->text(
				collect($byMime)->sortDesc()->take(16)->map(
					fn(int $v, string $k) => $k . ': ' . $v
				)->values()->all() ?: ['(none)']
			);
		}, 3);
	}

	private function createOne(
		ConsoleOutput $cout,
		Faker $faker,
		string $bugId,
		string $systemUserId,
		UserType $userType,
		MimeType $mime,
		array $required,
		array $foreigns,
		array $fkPools
	): void {
		try {
			$id = $this->generateUniqueId();

			$ext = $mime->getExtension();
			$ext = $ext !== '' ? $ext : 'bin';

			[$name, $path] = $this->generateUniqueNameAndPath($bugId, $ext);

			$now = now();
			$createdAt = $now->copy()->subMinutes(random_int(0, 60 * 24 * 60));
			$updatedAt = (random_int(1, 100) <= 35)
				? $createdAt->copy()->addMinutes(random_int(1, 60 * 24 * 10))
				: $createdAt;

			$sizeBytes = $this->pickBytesByMime($mime);

			$lastAccessed = (random_int(1, 100) <= 40)
				? $now->copy()->subMinutes(random_int(0, 60 * 24 * 30))
				: null;

			$exp = (random_int(1, 100) <= 10)
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

			$attrs = [
				'id'        => $id,
				AC::COL_BUG => $bugId,
				UC::COL_U_TP => $userType->value,

				// legado (reduz chance de inconsistência):
				'file' => $path,

				// HasFileColumns / AbstractFile:
				DC::COL_FL_PT   => $path,
				'name'          => $name,
				'extension'     => $ext,
				DC::COL_MM_TP   => $mime->value,
				DC::COL_LA      => $lastAccessed,
				'size'          => $sizeBytes,
				'description'   => (random_int(1, 100) <= 55) ? $faker->sentence(random_int(6, 14)) : null,
				'notes'         => (random_int(1, 100) <= 30) ? $faker->sentence(random_int(6, 14)) : null,
				DC::COL_DL_CT   => random_int(0, 250),
				DC::COL_FL_SZ   => (float) $sizeBytes,
				DC::COL_PERM_RLS => $this->randomPermissionRules(),
				'executors'     => null,
				'editors'       => null,
				'viewers'       => null,
				DC::COL_EXP_DT  => $exp,
				'type'          => $type,

				DC::COL_TABLE_CREATOR => $systemUserId,
				DC::COL_TABLE_UPDATER => null,
				DC::COL_C_AT          => $createdAt,
				DC::COL_U_AT          => $updatedAt,
			];

			$this->fillMissingRequiredColumns($attrs, $required, $foreigns, $fkPools, $bugId, $systemUserId);

			// EXIGIDO: ConsoleOutput->writeln imediatamente antes de salvar/criar, com detalhe breve da variação
			$cout->writeln(sprintf(
				'Criando BugFile: bug=%s user_type=%s mime=%s ext=%s bytes=%d name=%s',
				mb_substr($bugId, 0, 8),
				$userType->value,
				$mime->value,
				$ext,
				$sizeBytes,
				mb_substr($name, 0, 80)
			));

			$m = new BugFile();
			foreach ($attrs as $k => $v) {
				$m->setAttribute($k, $v);
			}
			$m->save();
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed: ' . $e->getMessage(), [
				'bug' => $bugId,
				'user_type' => $userType->value,
				'mime' => $mime->value,
			]);
		}
	}

	private function generateUniqueId(): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$id = (string) Str::uuid();

			$row = DB::selectOne(
				'select 1 as x from ' . DC::TABLE_BG_FL . ' where id = ? limit 1',
				[$id]
			);

			if ($row === null) return $id;
		} while ($attempts < self::UNIQUE_ID_MAX_ATTEMPTS);

		return (string) Str::uuid();
	}

	private function generateUniqueNameAndPath(string $bugId, string $ext): array
	{
		$attempts = 0;

		do {
			$attempts++;

			$uuid = (string) Str::uuid();
			$suffix = substr(str_replace('-', '', $uuid), 0, 8);

			$base = 'FILE_' . $suffix . '_' . now()->timestamp;
			$fileName = mb_substr($base, 0, 200) . '.' . $ext;

			$path = 'uploads/bugs/' . $bugId . '/' . $fileName;

			// unique(['name', file_path]) do HasFileColumns
			$row = DB::selectOne(
				'select 1 as x from ' . DC::TABLE_BG_FL . ' where name = ? and ' . DC::COL_FL_PT . ' = ? limit 1',
				[$fileName, $path]
			);

			if ($row === null) return [$fileName, $path];
		} while ($attempts < self::UNIQUE_PAIR_MAX_ATTEMPTS);

		$uuid = (string) Str::uuid();
		$fileName = 'FILE_' . $uuid . '_' . now()->timestamp . '_' . random_int(1000, 9999) . '.' . $ext;
		$path = 'uploads/bugs/' . $bugId . '/' . $fileName;

		return [$fileName, $path];
	}

	private function pickBytesByMime(MimeType $mime): int
	{
		if ($mime->isVideo()) return random_int(20 * 1024 * 1024, 800 * 1024 * 1024);
		if ($mime->isImage()) return random_int(50 * 1024, 8 * 1024 * 1024);
		if ($mime->isArchive()) return random_int(100 * 1024, 100 * 1024 * 1024);
		if ($mime->isDocument()) return random_int(20 * 1024, 5 * 1024 * 1024);
		if ($mime->isCode()) return random_int(1 * 1024, 2 * 1024 * 1024);

		return random_int(512, 25_000_000);
	}

	private function randomPermissionRules(): string
	{
		$digits = [];
		for ($i = 0; $i < 6; $i++) $digits[] = (string) random_int(0, 7);
		return implode('', $digits);
	}

	private function makeIo(ConsoleOutput $cout): SymfonyStyle
	{
		$cmd = $this->command;

		$input = ($cmd instanceof Command && method_exists($cmd, 'getInput'))
			? $cmd->getInput()
			: new ArrayInput([]);

		// prefer ConsoleOutput (exigido), mesmo quando há Command output
		return new SymfonyStyle($input, $cout);
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
	private function loadBugIdsRaw(): Collection
	{
		$rows = DB::select('select id from ' . DC::TABLE_BUGS);
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

	/**
	 * @return array<string,Collection<int,string>>|null
	 */
	private function preloadRequiredFkPools(
		array $required,
		array $foreigns,
		Collection $bugIds,
		SymfonyStyle $io
	): ?array {
		$pools = [];

		foreach ($foreigns as $col => $refTable) {
			if (!isset($required[$col])) continue;

			if ($refTable === DC::TABLE_BUGS) {
				if ($bugIds->isEmpty()) {
					$io->warning("Required FK '{$col}' needs bugs, but bugs are empty. Aborting seeder.");
					return null;
				}
				$pools[$refTable] = $bugIds;
				continue;
			}

			if (!Schema::hasTable($refTable)) {
				$io->warning("Required FK '{$col}' references missing table '{$refTable}'. Aborting seeder.");
				return null;
			}

			$ids = $this->loadIdPoolRaw($refTable);
			if ($ids->isEmpty()) {
				$io->warning("Required FK '{$col}' references '{$refTable}' but it has no rows. Aborting seeder.");
				return null;
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
		string $bugId,
		string $systemUserId
	): void {
		foreach ($required as $col => $meta) {
			if (array_key_exists($col, $attrs)) continue;

			if ($col === AC::COL_BUG) {
				$attrs[$col] = $bugId;
				continue;
			}

			if ($col === DC::COL_TABLE_CREATOR) {
				$attrs[$col] = $systemUserId;
				continue;
			}

			if (isset($foreigns[$col])) {
				$ref = $foreigns[$col];
				$pool = $fkPools[$ref] ?? null;

				if ($pool instanceof Collection && $pool->isNotEmpty()) {
					$attrs[$col] = (string) $pool->random();
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

			$attrs[$col] = 'seed_' . Str::random(12);
		}
	}
}
