<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\StockReport;
use Illuminate\Database\Seeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class StockReportSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 512;
	private const SECONDS_LIMIT = 600; // 10 minutes
	private const UNIQUE_CODE_ATTEMPTS = 35;
	private const RANDOM_ID_ATTEMPTS = 8;
	private const GLOBAL_ATTEMPT_MULT = 12;
	private const UUID_POOL_LIMIT = 2048;

	/** @var array<int, string> */
	private array $productIds = [];

	/** @var array<int, string> */
	private array $productServiceIds = [];

	/** @var array<string, array<int, string>> */
	private array $uuidPools = [];

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);

		if (!Schema::hasTable(DC::TABLE_STK_RPT)) {
			$this->out->writeln('<error> Table ' . DC::TABLE_STK_RPT . ' does not exist - cannot seed </error>');
			Log::error('StockReportSeeder: stock_reports table missing', ['table' => DC::TABLE_STK_RPT]);
			return;
		}

		// Preload product pools - CRITICAL for ensuring we always have references
		$this->productIds = $this->loadUuidPool(DC::TABLE_PRODUCTS);
		$this->productServiceIds = $this->loadUuidPool(DC::TABLE_PROD_SERVS);

		if (empty($this->productIds) && empty($this->productServiceIds)) {
			$this->out->writeln('<error> No products or product_services found - cannot create StockReports </error>');
			Log::error('StockReportSeeder: no product references available', [
				'products_table' => DC::TABLE_PRODUCTS,
				'product_services_table' => DC::TABLE_PROD_SERVS,
			]);
			return;
		}

		$this->out->writeln("<info> Loaded " . count($this->productIds) . " products, " . count($this->productServiceIds) . " product_services </info>");

		$typeMappings = StockReport::TYPE_MAPPINGS;
		[$tableOccurrences, $tableToTypes, $uniqueTables] = $this->buildTableStats($typeMappings);

		$fallbackTable = $uniqueTables[0] ?? DC::TABLE_USERS;
		$basePlans = $this->buildBaselinePlans($typeMappings, $fallbackTable);
		$extraPlans = $this->buildTableDrivenPlans($typeMappings, $tableOccurrences, $tableToTypes, $fallbackTable);

		shuffle($extraPlans);

		$rawTotal = min(count($basePlans) + count($extraPlans), self::HARD_CAP);
		$target = min($this->capTo64Multiple(max(64, $rawTotal)), self::HARD_CAP);

		if ($target < count($basePlans)) {
			$target = min($this->capTo64Multiple(count($basePlans)), self::HARD_CAP);
		}

		$plans = $basePlans;
		$need = $target - count($plans);

		if ($need > 0 && $extraPlans) {
			$plans = array_merge($plans, array_slice($extraPlans, 0, $need));
		}

		while (count($plans) < $target) {
			$table = $uniqueTables ? $uniqueTables[array_rand($uniqueTables)] : $fallbackTable;
			$typesForTable = $tableToTypes[$table] ?? array_keys($typeMappings);
			$type = $typesForTable ? $typesForTable[array_rand($typesForTable)] : StockReport::TYPE_CUSTOM;
			$root = $typeMappings[$type][0] ?? $fallbackTable;

			$plans[] = ['type' => $type, 'table' => $table, 'root' => $root];
		}

		$plans = array_slice($plans, 0, $target);

		$created = 0;
		$skipped = 0;
		$attempts = 0;
		$maxAttempts = max(128, $target * self::GLOBAL_ATTEMPT_MULT);

		$this->out->writeln("<info> Starting StockReport seeding: target={$target} </info>");

		foreach ($plans as $plan) {
			if ($created >= $target || $created >= self::HARD_CAP) break;
			if (++$attempts > $maxAttempts) break;

			if ($this->timeExceeded($clock)) return;

			$attrs = $this->makeAttributes(
				plan: $plan,
				seq: $created + 1,
				target: $target,
				tableOccurrences: (int) ($tableOccurrences[$plan['table']] ?? 1)
			);

			if (!$attrs) {
				$skipped++;
				continue;
			}

			if ($this->persistReport($attrs, $plan)) {
				$created++;
			} else {
				$skipped++;
			}
		}

		while ($created < $target && $attempts++ <= $maxAttempts) {
			if ($this->timeExceeded($clock)) return;

			$table = $uniqueTables ? $uniqueTables[array_rand($uniqueTables)] : $fallbackTable;
			$typesForTable = $tableToTypes[$table] ?? array_keys($typeMappings);
			$type = $typesForTable ? $typesForTable[array_rand($typesForTable)] : StockReport::TYPE_CUSTOM;
			$root = $typeMappings[$type][0] ?? $fallbackTable;

			$plan = ['type' => $type, 'table' => $table, 'root' => $root];

			$attrs = $this->makeAttributes(
				plan: $plan,
				seq: $created + 1,
				target: $target,
				tableOccurrences: (int) ($tableOccurrences[$table] ?? 1)
			);

			if (!$attrs) {
				$skipped++;
				continue;
			}

			if ($this->persistReport($attrs, $plan)) {
				$created++;
			} else {
				$skipped++;
			}
		}

		$this->out->writeln("<info> StockReportSeeder completed: created={$created}/{$target}, skipped={$skipped} </info>");
	}

	private function buildTableStats(array $typeMappings): array
	{
		$occ = [];
		$tableToTypes = [];

		foreach ($typeMappings as $type => $tables) {
			$tables = is_array($tables) ? array_values($tables) : [];

			foreach ($tables as $t) {
				$table = is_string($t) ? trim($t) : '';
				if ($table === '') continue;

				$occ[$table] = ($occ[$table] ?? 0) + 1;
				$tableToTypes[$table] ??= [];
				$tableToTypes[$table][] = $type;
			}
		}

		return [$occ, $tableToTypes, array_values(array_keys($occ))];
	}

	private function buildBaselinePlans(array $typeMappings, string $fallbackTable): array
	{
		$plans = [];

		foreach (array_keys($typeMappings) as $type) {
			$tables = is_array($typeMappings[$type] ?? []) ? array_values($typeMappings[$type]) : [];
			$contextTable = $tables[0] ?? $fallbackTable;
			$rootTable = $tables[0] ?? $fallbackTable;

			$plans[] = ['type' => $type, 'table' => $contextTable, 'root' => $rootTable];
		}

		return $plans;
	}

	private function buildTableDrivenPlans(array $typeMappings, array $tableOccurrences, array $tableToTypes, string $fallbackTable): array
	{
		$plans = [];

		foreach (array_keys($tableOccurrences) as $table) {
			$mult = (int) ($tableOccurrences[$table] ?? 1);
			$n = random_int(1, 8) * max(1, $mult);

			$typesForTable = $tableToTypes[$table] ?? array_values(array_keys($typeMappings));
			$countTypes = count($typesForTable);
			if ($countTypes < 1) continue;

			for ($i = 0; $i < $n; $i++) {
				$type = $typesForTable[$i % $countTypes] ?? StockReport::TYPE_CUSTOM;
				$root = $typeMappings[$type][0] ?? $fallbackTable;

				$plans[] = ['type' => $type, 'table' => $table, 'root' => $root];
			}
		}

		return $plans;
	}

	private function makeAttributes(array $plan, int $seq, int $target, int $tableOccurrences): ?array
	{
		$type = strtolower(trim((string) ($plan['type'] ?? StockReport::TYPE_CUSTOM)));
		$table = trim((string) ($plan['table'] ?? ''));
		$root = trim((string) ($plan['root'] ?? ''));

		// CRITICAL: Always assign exactly ONE product reference (never both, never none)
		[$productId, $productServiceId] = $this->pickSingleProductReference();

		if ($productId === null && $productServiceId === null) {
			$this->out->writeln("<comment> Skipping {$type}: no product reference available </comment>");
			return null;
		}

		$typeId = $this->pickUuidFromTable($root) ?? (string) Str::uuid();

		$now = now();
		$daysBack = random_int(0, 365);
		$start = $now->copy()->subDays($daysBack)->toDateString();
		$end = $now->copy()->subDays(max(0, $daysBack - random_int(0, 90)))->toDateString();

		if ($start > $end) [$start, $end] = [$end, $start];

		$qty = random_int(0, 2500);
		$coaId = random_int(0, 100) < 20 ? $this->pickUuidFromTable(DC::TABLE_COAS) : null;
		$plnId = random_int(0, 100) < 15 ? $this->pickUuidFromTable(DC::TABLE_PLN_SCHD) : null;
		$jrnId = random_int(0, 100) < 12 ? $this->pickUuidFromTable(DC::TABLE_JOURNAL_ENTRIES) : null;

		$submittedBy = random_int(0, 100) < 35 ? $this->pickUuidFromTable(DC::TABLE_USERS) : null;
		$approvedBy = null;
		$rejectedBy = null;

		if (random_int(0, 100) < 25) {
			$approvedBy = $this->pickUuidFromTable(DC::TABLE_USERS);
		} elseif (random_int(0, 100) < 15) {
			$rejectedBy = $this->pickUuidFromTable(DC::TABLE_USERS);
		}

		$code = $this->generateUniqueCode();
		$title = strtoupper($type) . " report #{$seq} (" . ($table !== '' ? $table : 'n/a') . ')';

		return [
			'code' => $code,
			'title' => $title,
			'type' => $type,
			BC::COL_TP_ID => $typeId,
			'quantity' => $qty,

			// GUARANTEED: Exactly one of these is set, never both, never none
			BC::COL_PRD_ID => $productId,
			BC::COL_PRD_SV_ID => $productServiceId,

			PJC::COL_COA_ID => $coaId,
			PJC::COL_PLN_SCHD_ID => $plnId,
			BC::COL_JRN_ENT_ID => $jrnId,

			PJC::COL_S_DT => $start,
			PJC::COL_E_DT => $end,

			'description' => "Seeded StockReport for type={$type}, table={$table}, root={$root}.",

			BC::COL_IS_PDF_AVL => (bool) random_int(0, 1),
			BC::COL_IS_SST_AVL => (bool) random_int(0, 1),
			BC::COL_IS_DOC_AVL => (bool) random_int(0, 1),
			BC::COL_IS_WEB_AVL => true,
			BC::COL_IS_PBI_AVL => (bool) random_int(0, 1),

			BC::COL_SBM_BY => $submittedBy,
			BC::COL_SBM_AT => $submittedBy ? now()->subMinutes(random_int(0, 100000)) : null,

			BC::COL_APV_BY => $approvedBy,
			BC::COL_APV_AT => $approvedBy ? now()->subMinutes(random_int(0, 100000)) : null,

			PJC::COL_REJ_BY => $rejectedBy,
			PJC::COL_REJ_AT => $rejectedBy ? now()->subMinutes(random_int(0, 100000)) : null,

			'receipts' => random_int(0, 100) < 30 ? [['ref' => (string) Str::uuid(), 'kind' => 'receipt', 'url' => null]] : null,
			'attachments' => random_int(0, 100) < 25 ? [['ref' => (string) Str::uuid(), 'kind' => 'attachment', 'path' => null]] : null,
			'filters' => [
				'table' => $table !== '' ? $table : null,
				'root' => $root !== '' ? $root : null,
				'type' => $type,
				'occurrences' => $tableOccurrences,
				'seed_seq' => $seq,
				'seed_target' => $target,
			],
			'metadata' => [
				'seed' => [
					'module' => 'StockReportSeeder',
					'generated_at' => $now->toIso8601String(),
					'context_table' => $table,
					'root_table' => $root,
				],
			],
		];
	}

	/**
	 * Returns exactly ONE product reference: [product_id, product_service_id]
	 * Guarantees: exactly one is set, the other is null (never both, never none)
	 */
	private function pickSingleProductReference(): array
	{
		$hasProducts = !empty($this->productIds);
		$hasProductServices = !empty($this->productServiceIds);

		// If only one pool exists, use it
		if ($hasProductServices && !$hasProducts) {
			return [null, $this->pickFromArray($this->productServiceIds)];
		}

		if ($hasProducts && !$hasProductServices) {
			return [$this->pickFromArray($this->productIds), null];
		}

		// Both pools exist: randomly choose one (50/50)
		if ($hasProducts && $hasProductServices) {
			if (random_int(0, 1) === 0) {
				return [$this->pickFromArray($this->productIds), null];
			} else {
				return [null, $this->pickFromArray($this->productServiceIds)];
			}
		}

		// Neither pool exists (should be caught in run(), but safety fallback)
		return [null, null];
	}

	private function persistReport(array $attrs, array $plan): bool
	{
		$type = (string) ($plan['type'] ?? '');
		$prdId = $attrs[BC::COL_PRD_ID] ?? null;
		$psId = $attrs[BC::COL_PRD_SV_ID] ?? null;

		// Final validation: ensure exactly one product reference exists
		$hasPrd = is_string($prdId) && trim($prdId) !== '';
		$hasPs = is_string($psId) && trim($psId) !== '';

		if (!$hasPrd && !$hasPs) {
			$this->out->writeln("<error> CRITICAL: type={$type} has NO product references (both null) </error>");
			Log::error('StockReportSeeder: missing product references', [
				'type' => $type,
				'product_id' => $prdId,
				'product_service_id' => $psId,
			]);
			return false;
		}

		if ($hasPrd && $hasPs) {
			$this->out->writeln("<comment> Warning: type={$type} has BOTH product references, nullifying product_id </comment>");
			$attrs[BC::COL_PRD_ID] = null;
		}

		try {
			StockReport::query()->create($attrs);
			$ref = $hasPs ? "ps={$psId}" : "prd={$prdId}";
			$this->out->writeln("<info> Created StockReport: type={$type}, {$ref} </info>");
			return true;
		} catch (Throwable $e) {
			// Retry with nullified optional FKs
			$attrs2 = $attrs;
			$attrs2[PJC::COL_COA_ID] = null;
			$attrs2[PJC::COL_PLN_SCHD_ID] = null;
			$attrs2[BC::COL_JRN_ENT_ID] = null;
			$attrs2[BC::COL_SBM_BY] = null;
			$attrs2[BC::COL_APV_BY] = null;
			$attrs2[PJC::COL_REJ_BY] = null;

			// Regenerate code if unique violation
			if ($e instanceof QueryException && $this->isUniqueCodeViolation($e)) {
				$attrs2['code'] = $this->generateUniqueCode();
				$this->out->writeln("<comment> Retrying with new code after unique violation </comment>");
			}

			try {
				StockReport::query()->create($attrs2);
				$this->out->writeln("<info> Created StockReport (retry): type={$type} </info>");
				return true;
			} catch (Throwable $e2) {
				// Last resort: raw insert
				try {
					$raw = $this->encodeForRawInsert($attrs2);
					DB::table(DC::TABLE_STK_RPT)->insert($raw);
					$this->out->writeln("<comment> Created StockReport (raw insert): type={$type} </comment>");
					return true;
				} catch (Throwable $e3) {
					$this->out->writeln("<error> FAILED to create StockReport: type={$type}, error=" . $e3->getMessage() . " </error>");
					Log::error('StockReportSeeder: failed to persist', [
						'type' => $type,
						'exception' => get_class($e3),
						'message' => $e3->getMessage(),
					]);
					return false;
				}
			}
		}
	}

	private function encodeForRawInsert(array $attrs): array
	{
		$out = $attrs;
		$out['id'] ??= (string) Str::uuid();
		$out['code'] ??= $this->generateUniqueCode();

		foreach (['receipts', 'attachments', 'filters', 'metadata'] as $k) {
			if (isset($out[$k]) && is_array($out[$k])) {
				$out[$k] = json_encode($out[$k], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
		}

		if (Schema::hasColumn(DC::TABLE_STK_RPT, 'created_at') && !isset($out['created_at'])) {
			$out['created_at'] = now();
		}
		if (Schema::hasColumn(DC::TABLE_STK_RPT, 'updated_at') && !isset($out['updated_at'])) {
			$out['updated_at'] = now();
		}

		return $out;
	}

	private function isUniqueCodeViolation(QueryException $e): bool
	{
		$msg = strtolower($e->getMessage());
		return str_contains($msg, 'code') && (str_contains($msg, 'unique') || str_contains($msg, 'duplicate'));
	}

	private function generateUniqueCode(): string
	{
		for ($i = 0; $i < self::UNIQUE_CODE_ATTEMPTS; $i++) {
			$code = 'STK-RPT-' . strtoupper((string) Str::uuid());
			$exists = (bool) (DB::selectOne("SELECT 1 AS x FROM " . DC::TABLE_STK_RPT . " WHERE code = ? LIMIT 1", [$code])?->x ?? false);
			if (!$exists) return $code;
		}

		return 'STK-RPT-' . strtoupper((string) Str::uuid());
	}

	private function pickUuidFromTable(?string $table): ?string
	{
		$pool = $this->loadUuidPool($table);
		return $pool ? $this->pickFromArray($pool) : null;
	}

	/** @return array<int, string> */
	private function loadUuidPool(?string $table): array
	{
		$table = trim((string) $table);
		if ($table === '') return [];

		if (isset($this->uuidPools[$table])) return $this->uuidPools[$table];

		if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
			return $this->uuidPools[$table] = [];
		}

		try {
			$ids = DB::table($table)
				->select('id')
				->whereNotNull('id')
				->limit(self::UUID_POOL_LIMIT)
				->pluck('id')
				->map(fn($v) => is_string($v) ? trim($v) : '')
				->filter(fn($v) => $v !== '' && Str::isUuid($v))
				->unique()
				->values()
				->all();

			return $this->uuidPools[$table] = $ids;
		} catch (Throwable $e) {
			Log::debug('StockReportSeeder: failed loading uuid pool', ['table' => $table, 'error' => $e->getMessage()]);
			return $this->uuidPools[$table] = [];
		}
	}

	private function pickFromArray(array $items): ?string
	{
		if (empty($items)) return null;
		$v = $items[array_rand($items)];
		return (is_string($v) && trim($v) !== '' && Str::isUuid($v)) ? $v : null;
	}

	private function capTo64Multiple(int $n): int
	{
		$r = $n % 64;
		$next = $n + (($r === 0) ? 0 : (64 - $r));
		return max(64, min($next, self::HARD_CAP));
	}

	private function timeExceeded(float $t0): bool
	{
		if ((microtime(true) - $t0) <= self::SECONDS_LIMIT) return false;

		$this->out->writeln('<comment> Time limit reached, stopping early </comment>');
		Log::warning('StockReportSeeder: time limit exceeded', ['limit_seconds' => self::SECONDS_LIMIT]);
		return true;
	}
}
