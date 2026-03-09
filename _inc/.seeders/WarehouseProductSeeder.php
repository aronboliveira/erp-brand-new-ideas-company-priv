<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Models\WarehouseProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class WarehouseProductSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const CAP = 4096;
	private const LIMIT_IDS = 50000;
	private const UNIQUE_ATTEMPTS = 24;
	private const WRITE_EVERY = 1;
	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$warehouseIds = $this->pluckIds(DC::TABLE_WHS, self::LIMIT_IDS);
		$productIds = $this->pluckIds(DC::TABLE_PRODUCTS, self::LIMIT_IDS);

		if (!$warehouseIds || !$productIds) {
			$this->out->writeln('<error>[WarehouseProductSeeder] missing warehouses or products. aborting.</error>');
			return;
		}

		// Get already assigned products to skip them
		$assignedProducts = WarehouseProduct::pluck(BC::COL_PRD_ID)->toArray();
		$availableProducts = array_diff($productIds, $assignedProducts);

		$target = min(self::CAP, count($availableProducts), count($warehouseIds) * 4);
		$this->out->writeln("<info>[WarehouseProductSeeder] target={$target}, available_products=" . count($availableProducts) . "</info>");

		$created = 0;
		$productIndex = 0;
		$availableProducts = array_values($availableProducts); // Re-index array

		foreach ($warehouseIds as $widx => $warehouseId) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('<comment>[WarehouseProductSeeder] time limit reached, aborting.</comment>');
				return;
			}
			if ($created >= $target || $productIndex >= count($availableProducts)) break;

			$productId = $availableProducts[$productIndex++];
			$qty = 1 + (($widx * 7) % 80);

			try {
				$m = new WarehouseProduct();
				$m->setAttribute(BC::COL_WRH_ID, $warehouseId);
				$m->setAttribute(BC::COL_PRD_ID, $productId);
				$m->setAttribute('quantity', $qty);

				if (self::WRITE_EVERY === 1)
					$this->out->writeln("<comment>[WarehouseProductSeeder] creating wh={$warehouseId} prd={$productId} qty={$qty}</comment>");

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('WarehouseProductSeeder failed', [
					'warehouse_id' => $warehouseId,
					'product_id' => $productId,
					'msg' => $e->getMessage(),
				]);
			}
		}

		$this->out->writeln("<info>[WarehouseProductSeeder] created={$created} target={$target}</info>");
	}

	private function normalizeTarget(int $rawTotal): int
	{
		if ($rawTotal <= 0) return 64;
		$mod = $rawTotal % 64;
		return $mod === 0 ? $rawTotal : ($rawTotal + (64 - $mod));
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
			Log::debug('WarehouseProductSeeder pluckIds failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function pickOne(array $ids, int $seed): ?string
	{
		$n = count($ids);
		if ($n === 0) return null;
		return $ids[$seed % $n] ?? $ids[0] ?? null;
	}
}
