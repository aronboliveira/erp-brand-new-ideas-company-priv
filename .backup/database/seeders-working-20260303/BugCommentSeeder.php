<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\UserType;
use App\Models\BugComment;
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

final class BugCommentSeeder extends Seeder
{
	private const MAX_COMMENTS_PER_BUG = 16;

	private const HARD_CAP = 3200;

	private const AUTHOR_SAMPLE_RATIO = 0.01; // 1%

	private const UNIQUE_REFERENCE_MAX_ATTEMPTS = 12;
	private const PLAN_ADJUST_MAX_STEPS = 120000;

	public function run(): void
	{
		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$io = $this->makeIo();
		$faker = FakerFactory::create('pt_BR');

		if (!Schema::hasTable(DC::TABLE_BG_CMT) || !Schema::hasTable(DC::TABLE_BUGS) || !Schema::hasTable(DC::TABLE_USERS)) {
			$io->warning('Missing required tables (bug_comments/bugs/users). Skipping BugCommentSeeder.');
			return;
		}

		$table = DC::TABLE_BG_CMT;

		$bugs = $this->loadBugIdsRaw();
		if ($bugs->isEmpty()) {
			$io->warning('No bugs found; skipping BugCommentSeeder.');
			return;
		}

		$users = $this->loadUserPoolRaw();
		if ($users->isEmpty()) {
			$io->warning('No users found; skipping BugCommentSeeder.');
			return;
		}

		$authors = $this->pickAuthorsSubset($users);
		if ($authors->isEmpty()) {
			$io->warning('Author subset is empty; skipping BugCommentSeeder.');
			return;
		}

		// ---- Identify required columns / foreigns using raw SQL (defensive for future trait changes) ----
		$required = $this->requiredColumnsRaw($table); // col => meta
		$foreigns = $this->foreignKeyMapRaw($table);   // col => referenced_table

		// Preflight pools for any required FKs
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

		// ---- Build per-bug counts: 0..16 inverted quadratic (tends to low) ----
		$countsByBug = [];
		$rawTotal = 0;

		foreach ($bugs as $bid) {
			$n = $this->sampleInvertedQuadratic(0, self::MAX_COMMENTS_PER_BUG);
			$countsByBug[$bid] = $n;
			$rawTotal += $n;
		}

		// Baseline rule: 64*N where N is number of base entities (bugs), capped.
		$baseMin = 64 * max(1, $bugs->count());
		$baseMin = min(self::HARD_CAP, $baseMin);

		// CLI override: --count is treated as minimum (still obey cap)
		$requested = $this->readCountOption();
		$requested = $requested !== null ? min(self::HARD_CAP, max(0, $requested)) : null;

		$target = max($rawTotal, $baseMin);
		if ($requested !== null) $target = max($target, $requested);

		// Capacity (bugs * 16) and hard cap
		$capacity = min(self::HARD_CAP, $bugs->count() * self::MAX_COMMENTS_PER_BUG);
		$target = min($target, $capacity);

		// Enforce multiple-of-64 but never exceed cap
		$target = $this->fitToMultipleOf64WithinCap($target, $capacity);

		// Adjust distribution to hit exactly target
		$countsByBug = $this->adjustCountsToTarget($countsByBug, $target);

		$finalTotal = array_sum($countsByBug);

		// Final safeguard: multiple-of-64 and <= cap
		$finalTotal = min($finalTotal, $capacity);
		$finalTotal = $this->fitToMultipleOf64WithinCap($finalTotal, $capacity);
		if ($finalTotal !== array_sum($countsByBug)) {
			$countsByBug = $this->adjustCountsToTarget($countsByBug, $finalTotal);
		}

		$io->section('BugCommentSeeder plan');
		$io->text([
			'Bugs: ' . $bugs->count(),
			'Users: ' . $users->count(),
			'Authors (1%): ' . $authors->count(),
			'Raw total (initial): ' . $rawTotal,
			'Base minimum (64*N capped): ' . $baseMin,
			'Requested (--count, capped): ' . ($requested !== null ? (string)$requested : 'n/a'),
			'Capacity (bugs*16, hard cap): ' . $capacity,
			'Final target (multiple of 64): ' . $finalTotal,
		]);

		// ---- Create comments ----
		$created = 0;
		$byAuthor = [];
		$byBug = [];

		// Track per-bug created IDs to build reply chains (even though migration has no parent FK, model invariant uses parent/depth/is_reply)
		$createdIdsByBug = [];

		$enumValues = array_column(UserType::cases(), 'value');

		foreach ($countsByBug as $bugId => $n) {
			if ($n <= 0) continue;

			$threadId = (string)Str::uuid();
			$createdIdsByBug[$bugId] = [];

			for ($i = 0; $i < $n; $i++) {
				$author = $authors->random();
				$authorId = (string)($author['id'] ?? '');
				if ($authorId === '') continue;

				// user_type: prefer user row if present/valid, else default to BugComment::defaultUserType() (Client) with some variance
				$authorType = $author['user_type'] ?? null;
				$ut = is_string($authorType) && in_array($authorType, $enumValues, true)
					? $authorType
					: (
						random_int(1, 100) <= 80
						? UserType::Client->value
						: (string)$enumValues[array_rand($enumValues)]
					);

				$isReply = false;
				$parentId = null;
				$depth = 0;

				if (!empty($createdIdsByBug[$bugId]) && random_int(1, 100) <= 18) { // ~18% replies
					$isReply = true;
					$parentId = (string)$createdIdsByBug[$bugId][array_rand($createdIdsByBug[$bugId])];
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
				if (random_int(1, 100) <= 35) {
					$reactions = [
						['emoji' => '👍', 'count' => random_int(1, 5)],
					];
					if (random_int(1, 100) <= 30) {
						$reactions[] = ['emoji' => '🐞', 'count' => random_int(1, 3)];
					}
				}

				$tags = $this->randomStringList($faker, 0, 6, prefix: 'tag_');
				$attachments = $this->randomStringList($faker, 0, 2, prefix: 'file_');

				$reference = null;
				if (random_int(1, 100) <= 70) {
					$reference = $this->generateUniqueReferenceWithExists($table);
				}

				$time = now()->subSeconds(random_int(0, 60 * 60 * 24 * 60));

				$attrs = [
					// Bug link (required by migration)
					AC::COL_BUG     => $bugId,

					// Comment columns
					'time'          => $time,
					'comment'       => $faker->sentence(random_int(8, 22)),
					'reference'     => $reference,
					UC::COL_USER_ID => $authorId,
					UC::COL_U_TP    => $ut,

					AC::COL_IS_EDT  => $isEdited,
					AC::COL_IS_DEL  => $isDeleted,
					'deleter'       => $deleterId,
					AC::COL_DEL_AT  => $deletedAt,

					AC::COL_EDT_CNT => $editCount,
					'flagged'       => $flagged,

					'thread'        => $threadId,
					AC::COL_IS_RPL  => $isReply,
					AC::COL_RPL_CNT => 0,

					'order'         => $i,
					'depth'         => $depth,
					'parent'        => $parentId,

					'attachments'   => $attachments,
					'tags'          => $tags,
					'reactions'     => $reactions,
					'replies'       => null,
					'edits'         => $edits,
					'metadata'      => [
						'seed' => 'BugCommentSeeder',
						'v'    => 1,
					],
				];

				// Fill any NOT NULL + no-default columns (including future changes in traits)
				$this->fillMissingRequiredColumns($attrs, $required, $foreigns, $fkPools, $users, $bugs);

				$m = new BugComment();
				foreach ($attrs as $k => $v) {
					// Do NOT strip nulls for nullable columns
					$m->setAttribute($k, $v);
				}
				$output->writeln('Creating BugComment for bug ' . $bugId . ' by author ' . $authorId);
				$m->save();

				$created++;
				$createdIdsByBug[$bugId][] = (string)$m->getAttribute('id');

				$byBug[$bugId] = (int)($byBug[$bugId] ?? 0) + 1;
				$byAuthor[$authorId] = (int)($byAuthor[$authorId] ?? 0) + 1;

				if ($created >= $finalTotal) break 2;
			}
		}

		$io->section('BugCommentSeeder result');
		$io->text([
			'Created: ' . $created,
			'Hard cap: ' . self::HARD_CAP,
			'Bugs touched: ' . count($byBug),
			'Authors used: ' . count($byAuthor),
		]);

		if ($byBug !== []) {
			$vals = array_values($byBug);
			sort($vals);
			$min = (int)($vals[0] ?? 0);
			$max = (int)($vals[count($vals) - 1] ?? 0);
			$avg = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : 0.0;

			$io->text([
				'Per-bug comments: min=' . $min . ', max=' . $max . ', avg=' . $avg,
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
	private function loadBugIdsRaw(): Collection
	{
		$rows = DB::select('select id from ' . DC::TABLE_BUGS);
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

		$typeCol = null;
		foreach ([UC::COL_U_TP, 'type', 'user_type'] as $c) {
			if (in_array($c, $cols, true)) {
				$typeCol = $c;
				break;
			}
		}

		$select = $typeCol
			? 'select id, ' . $typeCol . ' as user_type from ' . DC::TABLE_USERS
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
	 * @param array<string,int> $countsByBug
	 * @return array<string,int>
	 */
	private function adjustCountsToTarget(array $countsByBug, int $target): array
	{
		$current = array_sum($countsByBug);
		if ($current === $target) return $countsByBug;

		$steps = 0;

		if ($current < $target) {
			$need = $target - $current;

			while ($need > 0 && $steps < self::PLAN_ADJUST_MAX_STEPS) {
				$steps++;

				$candidates = [];
				$sumW = 0;

				foreach ($countsByBug as $bid => $c) {
					if ($c >= self::MAX_COMMENTS_PER_BUG) continue;
					$w = (int)pow((self::MAX_COMMENTS_PER_BUG - $c), 2);
					if ($w < 1) $w = 1;
					$candidates[] = [$bid, $w];
					$sumW += $w;
				}

				if ($candidates === []) break;

				$pickBid = $this->weightedPickKey($candidates, $sumW);
				if ($pickBid === null) break;

				$countsByBug[$pickBid] = (int)$countsByBug[$pickBid] + 1;
				$need--;
			}
		} else {
			$drop = $current - $target;

			while ($drop > 0 && $steps < self::PLAN_ADJUST_MAX_STEPS) {
				$steps++;

				$candidates = [];
				$sumW = 0;

				foreach ($countsByBug as $bid => $c) {
					if ($c <= 0) continue;
					$w = (int)pow($c, 2);
					if ($w < 1) $w = 1;
					$candidates[] = [$bid, $w];
					$sumW += $w;
				}

				if ($candidates === []) break;

				$pickBid = $this->weightedPickKey($candidates, $sumW);
				if ($pickBid === null) break;

				$countsByBug[$pickBid] = (int)$countsByBug[$pickBid] - 1;
				$drop--;
			}
		}

		return $countsByBug;
	}

	/**
	 * @param array<int,array{0:string,1:int}> $candidates
	 */
	private function weightedPickKey(array $candidates, int $sumW): ?string
	{
		$sumW = max(1, $sumW);
		$r = random_int(1, $sumW);
		$acc = 0;

		foreach ($candidates as [$key, $w]) {
			$acc += max(1, (int)$w);
			if ($r <= $acc) return (string)$key;
		}

		return isset($candidates[0][0]) ? (string)$candidates[0][0] : null;
	}

	private function generateUniqueReferenceWithExists(string $table): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$candidate = 'BG-CMT-' . (string)Str::uuid();

			$row = DB::selectOne(
				'select 1 as x from ' . $table . ' where reference = ? limit 1',
				[$candidate]
			);

			if ($row === null) return $candidate;
		} while ($attempts < self::UNIQUE_REFERENCE_MAX_ATTEMPTS);

		return 'BG-CMT-' . (string)Str::uuid();
	}

	/**
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
	 * Fill any NOT NULL + no-default columns without stripping nulls for nullable columns.
	 *
	 * @param array<string,mixed> $attrs
	 * @param array<string,array{data_type:string,is_nullable:string,column_default:mixed}> $required
	 * @param array<string,string> $foreigns
	 * @param array<string,Collection<int,string>> $fkPools
	 * @param Collection<int,array{id:string,user_type:?string}> $users
	 * @param Collection<int,string> $bugs
	 */
	private function fillMissingRequiredColumns(
		array &$attrs,
		array $required,
		array $foreigns,
		array $fkPools,
		Collection $users,
		Collection $bugs
	): void {
		foreach ($required as $col => $meta) {
			if (array_key_exists($col, $attrs)) continue;

			if ($col === AC::COL_BUG) {
				$attrs[$col] = $attrs[AC::COL_BUG] ?? (string)$bugs->random();
				continue;
			}

			if (isset($foreigns[$col])) {
				$ref = $foreigns[$col];
				$pool = $fkPools[$ref] ?? null;

				if ($pool instanceof Collection && $pool->isNotEmpty()) {
					$attrs[$col] = (string)$pool->random();
					continue;
				}

				if ($ref === DC::TABLE_USERS && $users->isNotEmpty()) {
					$attrs[$col] = (string)($users->random()['id'] ?? '');
					continue;
				}
				if ($ref === DC::TABLE_BUGS && $bugs->isNotEmpty()) {
					$attrs[$col] = (string)$bugs->random();
					continue;
				}

				continue;
			}

			$type = strtolower(trim((string)($meta['data_type'] ?? '')));

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

			if (str_contains($type, 'char') || str_contains($type, 'text') || $type === 'varchar') {
				$attrs[$col] = 'seed_' . Str::random(12);
				continue;
			}

			$attrs[$col] = 0;
		}
	}
}
