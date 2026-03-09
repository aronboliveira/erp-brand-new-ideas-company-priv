<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProductCategorySeeder extends Seeder
{
	private ConsoleOutput $out;

	private const CAP = 800;
	private const UNIQUE_ATTEMPTS = 20;
	private const WRITE_EVERY = 1;

	private const KEEP_PCT_HIGH = 95; // ~5% null
	private const KEEP_PCT_MED  = 88; // ~12% null

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$target = $this->normalizeTargetWithinCap(self::CAP);
		$this->out->writeln("<info>[ProductCategorySeeder] target={$target} (cap=" . self::CAP . ")</info>");

		$tableCats = DC::TABLE_PRD_CAT;
		$tableUsers = DC::TABLE_USERS;
		$tableProdServCats = DC::TABLE_PROD_SERV_CATS;

		$creatorIds = $this->pluckIds($tableUsers, 50000);
		$prodServCatIds = Schema::hasTable($tableProdServCats) ? $this->pluckIds($tableProdServCats, 50000) : [];

		$tagPool = [
			'inventory',
			'catalog',
			'featured',
			'sale',
			'seasonal',
			'clearance',
			'imported',
			'local',
			'b2b',
			'b2c',
			'premium',
			'budget',
			'fragile',
			'heavy',
			'digital',
			'subscription',
		];

		$created = 0;

		for ($i = 0; $i < $target; $i++) {
			try {
				$creatorId = $creatorIds ? $creatorIds[$i % count($creatorIds)] : null;

				$linkedProdServCatId = $prodServCatIds
					? $this->tolerant($prodServCatIds[($i + 17) % count($prodServCatIds)], self::KEEP_PCT_MED)
					: null;

				$name = $this->generateUniqueName($tableCats, "Category", $i);

				$description = $this->shouldKeep(self::KEEP_PCT_MED)
					? fake()->sentence(14)
					: null;

				// tags is NOT nullable in migration, must always be set
				$tags = [
					$tagPool[$i % count($tagPool)],
					$tagPool[($i + 3) % count($tagPool)],
				];

				if (($i % 7) === 0) $tags[] = 'seeded';
				if (($i % 11) === 0) $tags[] = 'priority';

				if (self::WRITE_EVERY === 1) {
					$this->out->writeln(
						"<comment>[ProductCategorySeeder] creating name=\"{$name}\" link=" .
							($linkedProdServCatId ? substr($linkedProdServCatId, 0, 8) : 'null') .
							" tags=" . count($tags) .
							"</comment>"
					);
				}

				$m = new ProductCategory();

				if ($creatorId !== null) $m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);

				$m->setAttribute('name', $name);
				$m->setAttribute('description', $description);
				$m->setAttribute(PJC::COL_PRD_SERV_CAT_ID, $linkedProdServCatId);
				$m->setAttribute('tags', $tags);

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('ProductCategorySeeder iteration failed', [
					'i' => $i,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$this->out->writeln("<info>[ProductCategorySeeder] created={$created} target={$target}</info>");
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
			Log::debug('ProductCategorySeeder pluckIds failed', [
				'table' => $table,
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
}
