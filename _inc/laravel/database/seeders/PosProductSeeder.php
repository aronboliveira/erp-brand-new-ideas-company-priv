<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Models\PosProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class PosProductSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const CAP = 4096;
	private const CAP = 2;
	private const LIMIT_IDS = 50000;
	private const PICK_ATTEMPTS = 24;
	private const WRITE_EVERY = 1;
	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$posIds = $this->pluckIds(DC::TABLE_POS, self::LIMIT_IDS);
		$prodServiceIds = $this->pluckIds(DC::TABLE_PROD_SERVS, self::LIMIT_IDS);

		if (!$posIds || !$prodServiceIds) {
			$this->out->writeln('<error>[PosProductSeeder] missing pos or product_services. aborting.</error>');
			return;
		}

		$existing = $this->pluckExistingProductIds(DC::TABLE_POS_PRD, BC::COL_PRD_ID, self::LIMIT_IDS);
		$used = [];
		foreach ($existing as $v) $used[$v] = true;

		$maxPossible = count($prodServiceIds) - count($used);
		if ($maxPossible <= 0) {
			$this->out->writeln('<info>[PosProductSeeder] no available unique products left. aborting.</info>');
			return;
		}

		$rawTarget = min(self::CAP, $maxPossible);
		$target = $this->normalizeTarget($rawTarget);
		if ($target > self::CAP) $target = self::CAP - (self::CAP % 64);
		if ($target <= 0) $target = self::CAP;

		$this->out->writeln("<info>[PosProductSeeder] target={$target} (max_possible={$maxPossible})</info>");

		$created = 0;
		$cursor = 0;

		foreach ($posIds as $pidx => $posId) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('<comment>[PosProductSeeder] time limit reached, aborting.</comment>');
				return;
			}
			if ($created >= $target) break;

			$prodId = $this->pickUniqueProductId($prodServiceIds, $used, $cursor++);
			if ($prodId === null) break;

			$qty = 1 + (($pidx * 3) % 10);
			$price = 10 + (($pidx * 17) % 500);
			$tax = (($pidx % 3) === 0) ? 7.5 : 0.0;
			$discount = (($pidx % 4) === 0) ? 10.0 : 0.0;

			try {
				$m = new PosProduct();

				$m->setAttribute(BC::COL_POS_ID, $posId);
				$m->setAttribute(BC::COL_PRD_ID, $prodId);
				$m->setAttribute('quantity', $qty);
				$m->setAttribute('price', $price);
				$m->setAttribute('tax', $tax);
				$m->setAttribute('discount', $discount);
				$m->setAttribute('description', ($pidx % 5 === 0) ? 'seeded pos line item' : null);

				// if (self::WRITE_EVERY === 1)
				// 	$this->out->writeln("<comment>[PosProductSeeder] creating pos={$posId} prd={$prodId} qty={$qty} price={$price} tax={$tax} disc={$discount}</comment>");

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('PosProductSeeder failed (base pass)', [
					'pos_id' => $posId,
					'product_id' => $prodId,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$spreadAttempts = 0;

		while ($created < $target && $spreadAttempts++ < ($target * 4)) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				$this->out->writeln('<comment>[PosProductSeeder] time limit reached during spread phase, aborting.</comment>');
				return;
			}
			$posId = $this->pickOne($posIds, $spreadAttempts);
			$prodId = $this->pickUniqueProductId($prodServiceIds, $used, $cursor++);
			if ($prodId === null) break;

			$qty = 1 + (($spreadAttempts * 5) % 12);
			$price = 10 + (($spreadAttempts * 29) % 800);
			$tax = (($spreadAttempts % 3) === 0) ? 12.0 : 0.0;
			$discount = (($spreadAttempts % 4) === 0) ? 5.0 : 0.0;

			try {
				$m = new PosProduct();

				$m->setAttribute(BC::COL_POS_ID, $posId);
				$m->setAttribute(BC::COL_PRD_ID, $prodId);
				$m->setAttribute('quantity', $qty);
				$m->setAttribute('price', $price);
				$m->setAttribute('tax', $tax);
				$m->setAttribute('discount', $discount);
				$m->setAttribute('description', ($spreadAttempts % 6 === 0) ? 'seeded pos line item (spread)' : null);

				// if (self::WRITE_EVERY === 1)
				// 	$this->out->writeln("<comment>[PosProductSeeder] creating pos={$posId} prd={$prodId} qty={$qty} price={$price} tax={$tax} disc={$discount}</comment>");

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('PosProductSeeder failed (spread pass)', [
					'pos_id' => $posId,
					'product_id' => $prodId,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$this->out->writeln("<info>[PosProductSeeder] created={$created} target={$target}</info>");
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
			Log::debug('PosProductSeeder pluckIds failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function pluckExistingProductIds(string $table, string $col, int $limit = 50000): array
	{
		try {
			$rows = DB::select("select {$col} as v from {$table} where {$col} is not null limit {$limit}");
			$out = [];
			foreach ($rows as $r) {
				$v = isset($r->v) ? (string) $r->v : '';
				if ($v !== '') $out[] = $v;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::debug('PosProductSeeder pluckExistingProductIds failed', [
				'table' => $table,
				'col' => $col,
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

	private function pickUniqueProductId(array $pool, array &$used, int $seed): ?string
	{
		$n = count($pool);
		if ($n === 0) return null;

		$attempt = 0;

		while ($attempt++ < self::PICK_ATTEMPTS) {
			$candidate = $pool[($seed + $attempt) % $n] ?? null;
			if ($candidate === null || trim((string) $candidate) === '') continue;
			if (isset($used[$candidate])) continue;

			$exists = false;
			try {
				$exists = DB::table(DC::TABLE_POS_PRD)->where(BC::COL_PRD_ID, $candidate)->exists();
			} catch (\Throwable $e) {
				Log::debug('PosProductSeeder uniqueness exists-check failed', [
					'candidate' => $candidate,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}

			if ($exists) {
				$used[$candidate] = true;
				continue;
			}

			$used[$candidate] = true;
			return $candidate;
		}

		return null;
	}
}
