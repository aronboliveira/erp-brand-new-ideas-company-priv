<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProductSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const CAP = 3200;
	private const UNIQUE_ATTEMPTS = 20;
	private const WRITE_EVERY = 1;

	private const KEEP_PCT_HIGH = 95; // ~5% null
	private const KEEP_PCT_MED  = 88; // ~12% null
	private const KEEP_PCT_LOW  = 80; // ~20% null

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$target = $this->normalizeTargetWithinCap(self::CAP);
		$this->out->writeln("<info>[ProductSeeder] target={$target} (cap=" . self::CAP . ")</info>");

		$productsTable = (new Product())->getTable();
		$prodCatsTable = DC::TABLE_PRD_CAT;
		$usersTable = DC::TABLE_USERS;
		$prodServsTable = DC::TABLE_PROD_SERVS;

		$creatorIds = $this->pluckIds($usersTable, 50000);

		// Load product categories (id + linked product_service_category id) via raw SQL
		$prodCatRows = $this->pluckProductCategoryRows($prodCatsTable, 100000);

		if (!$prodCatRows) {
			$this->out->writeln("<error>[ProductSeeder] No product categories found in {$prodCatsTable}. Run ProductCategorySeeder first.</error>");
			return;
		}

		$prodCatIds = [];
		$prodCatToProdServCat = []; // product_category_id => product_service_category_id|null
		foreach ($prodCatRows as $r) {
			$cid = (string) ($r['id'] ?? '');
			if ($cid === '') continue;
			$prodCatIds[] = $cid;
			$prodCatToProdServCat[$cid] = $r['psc_id'] ?? null;
		}

		// Attempt to detect product_services category column
		$psCatCol = $this->detectFirstExistingColumn($prodServsTable, [
			PJC::COL_PRD_SERV_CAT_ID,
			'product_service_category_id',
			'category_id',
			BC::COL_CAT_ID ?? 'category_id',
		]);

		$allProdServIds = Schema::hasTable($prodServsTable) ? $this->pluckIds($prodServsTable, 100000) : [];

		$prodServIdsByCat = [];
		if ($psCatCol !== null) {
			$prodServIdsByCat = $this->pluckProductServiceIdsGroupedByCategory($prodServsTable, $psCatCol, 200000);
		}

		$types = ['product', 'service', 'subscription', 'bundle', 'addon', 'digital', 'physical'];

		$created = 0;

		// Phase 1: ensure every ProductCategory id gets at least 1 Product
		$baseCount = min(count($prodCatIds), $target);

		for ($i = 0; $i < $baseCount; $i++) {
			$catId = $prodCatIds[$i];

			$this->createOneProduct(
				$i,
				$productsTable,
				$creatorIds,
				$types,
				$catId,
				$prodCatToProdServCat,
				$prodServIdsByCat,
				$allProdServIds,
				$psCatCol
			);

			$created++;
		}

		// Phase 2: spread remaining up to target
		for ($i = $baseCount; $i < $target; $i++) {
			$catId = $prodCatIds[$i % count($prodCatIds)];

			$this->createOneProduct(
				$i,
				$productsTable,
				$creatorIds,
				$types,
				$catId,
				$prodCatToProdServCat,
				$prodServIdsByCat,
				$allProdServIds,
				$psCatCol
			);

			$created++;
		}

		$this->out->writeln("<info>[ProductSeeder] created={$created} target={$target}</info>");
	}

	private function createOneProduct(
		int $i,
		string $productsTable,
		array $creatorIds,
		array $types,
		string $productCategoryId,
		array $prodCatToProdServCat,
		array $prodServIdsByCat,
		array $allProdServIds,
		?string $psCatCol
	): void {
		try {
			$creatorId = $creatorIds ? $creatorIds[$i % count($creatorIds)] : null;

			$pscId = $prodCatToProdServCat[$productCategoryId] ?? null;

			$prodServiceId = null;

			// Best-effort mapping: pick a product_service id whose category matches category->PJC::COL_PRD_SERV_CAT_ID
			if ($pscId !== null && $psCatCol !== null) {
				$bucket = $prodServIdsByCat[$pscId] ?? [];
				if ($bucket) $prodServiceId = $bucket[($i + 7) % count($bucket)];
			}

			// Fallback: any product_service id
			if ($prodServiceId === null && $allProdServIds) {
				$prodServiceId = $allProdServIds[($i + 11) % count($allProdServIds)];
			}

			// Null tolerance for nullable FK
			$prodServiceId = $this->tolerant($prodServiceId, self::KEEP_PCT_HIGH);

			$name = $this->generateUniqueName($productsTable, "Product", $i);

			$price = $this->randomMoney(1, 5000, 2);
			$qty = ($i % 9 === 0) ? 0 : $this->randomInt(1, 500);

			$description = $this->shouldKeep(self::KEEP_PCT_MED) ? fake()->paragraph() : null;
			$image = $this->shouldKeep(self::KEEP_PCT_LOW) ? ("products/" . substr((string) Str::uuid(), 0, 12) . ".jpg") : null;

			// Ensure we cycle types so each appears early
			$type = $types[$i % count($types)];
			$type = $this->tolerant($type, self::KEEP_PCT_MED);

			if (self::WRITE_EVERY === 1) {
				$this->out->writeln(
					"<comment>[ProductSeeder] creating name=\"{$name}\" price={$price} qty={$qty} prd_sv=" .
						($prodServiceId ? substr($prodServiceId, 0, 8) : 'null') .
						" cat=" . substr($productCategoryId, 0, 8) .
						"</comment>"
				);
			}

			$m = new Product();

			if ($creatorId !== null) $m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);

			$m->setAttribute(BC::COL_PRD_SV_ID, $prodServiceId);
			$m->setAttribute('name', $name);
			$m->setAttribute('price', $price);
			$m->setAttribute('quantity', $qty);
			$m->setAttribute('description', $description);
			$m->setAttribute('image', $image);
			$m->setAttribute('type', $type);

			$m->save();
		} catch (\Throwable $e) {
			Log::warning('ProductSeeder iteration failed', [
				'i' => $i,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
	}

	private function normalizeTargetWithinCap(int $cap): int
	{
		if ($cap <= 0) return 64;
		$n = intdiv($cap, 64);
		return max(64, $n * 64);
	}

	private function pluckIds(string $table, int $limit = 50000): array
	{
		try {
			$rows = DB::select("select id from {$table} limit {$limit}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::debug('ProductSeeder pluckIds failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function pluckProductCategoryRows(string $table, int $limit): array
	{
		try {
			$col = PJC::COL_PRD_SERV_CAT_ID;
			$rows = DB::select("select id, {$col} as psc_id from {$table} limit {$limit}");

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id === '') continue;

				$psc = isset($r->psc_id) ? (string) $r->psc_id : null;
				$psc = is_string($psc) && trim($psc) !== '' ? $psc : null;

				$out[] = ['id' => $id, 'psc_id' => $psc];
			}

			return $out;
		} catch (\Throwable $e) {
			Log::debug('ProductSeeder pluckProductCategoryRows failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function detectFirstExistingColumn(string $table, array $candidates): ?string
	{
		try {
			if (!Schema::hasTable($table)) return null;

			foreach ($candidates as $c) {
				if (!is_string($c) || trim($c) === '') continue;
				if (Schema::hasColumn($table, $c)) return $c;
			}

			return null;
		} catch (\Throwable) {
			return null;
		}
	}

	private function pluckProductServiceIdsGroupedByCategory(string $table, string $catCol, int $limit): array
	{
		try {
			$rows = DB::select("select id, {$catCol} as cid from {$table} where {$catCol} is not null limit {$limit}");

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				$cid = (string) ($r->cid ?? '');

				if (trim($id) === '' || trim($cid) === '') continue;

				if (!array_key_exists($cid, $out)) $out[$cid] = [];
				$out[$cid][] = $id;
			}

			return $out;
		} catch (\Throwable $e) {
			Log::debug('ProductSeeder pluckProductServiceIdsGroupedByCategory failed', [
				'table' => $table,
				'catCol' => $catCol,
				'msg' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function existsBySql(string $sql, array $bindings): bool
	{
		try {
			$row = DB::selectOne($sql, $bindings);
			return $row !== null;
		} catch (\Throwable) {
			return false;
		}
	}

	private function generateUniqueName(string $table, string $prefix, int $i): string
	{
		$attempt = 0;

		do {
			$attempt++;

			$suffix = strtoupper(substr((string) Str::uuid(), 0, 8));
			$name = "{$prefix} {$i}-{$suffix}";

			$exists = $this->existsBySql(
				"select 1 from {$table} where name = ? limit 1",
				[$name]
			);

			if (!$exists) return $name;

			usleep(15000);
		} while ($attempt < self::UNIQUE_ATTEMPTS);

		return "{$prefix} {$i}-" . strtoupper(substr((string) Str::uuid(), 0, 12));
	}

	private function shouldKeep(int $keepPercent): bool
	{
		$keepPercent = max(0, min(100, $keepPercent));
		try {
			return random_int(1, 100) <= $keepPercent;
		} catch (\Throwable) {
			return true;
		}
	}

	private function tolerant(mixed $value, int $keepPercent): mixed
	{
		if ($value === null) return null;
		return $this->shouldKeep($keepPercent) ? $value : null;
	}

	private function randomInt(int $min, int $max): int
	{
		try {
			return random_int($min, $max);
		} catch (\Throwable) {
			return $min;
		}
	}

	private function randomMoney(int $min, int $max, int $decimals = 2): float
	{
		$v = $this->randomInt($min * 100, $max * 100);
		return round($v / 100, $decimals);
	}
}
