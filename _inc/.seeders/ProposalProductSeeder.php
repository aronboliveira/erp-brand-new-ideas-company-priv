<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Models\ProposalProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProposalProductSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const CAP = 4096;
	private const LIMIT_IDS = 50000;
	private const PICK_ATTEMPTS = 24;
	private const WRITE_EVERY = 1;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		$proposalIds = $this->pluckIds(DC::TABLE_PROPOSALS, self::LIMIT_IDS);
		$productIds = $this->pluckIds(DC::TABLE_PRODUCTS, self::LIMIT_IDS);

		if (!$proposalIds || !$productIds) {
			$this->out->writeln('<error>[ProposalProductSeeder] missing proposals or products. aborting.</error>');
			return;
		}

		$existing = $this->pluckExistingProductIds(DC::TABLE_PPS_PRD, BC::COL_PRD_ID, self::LIMIT_IDS);
		$used = [];
		foreach ($existing as $v) $used[$v] = true;

		$maxPossible = count($productIds) - count($used);
		if ($maxPossible <= 0) {
			$this->out->writeln('<info>[ProposalProductSeeder] no available unique products left. aborting.</info>');
			return;
		}

		$rawTarget = min(self::CAP, $maxPossible);
		$target = $this->normalizeTarget($rawTarget);
		if ($target > self::CAP) $target = self::CAP - (self::CAP % 64);

		$this->out->writeln("<info>[ProposalProductSeeder] target={$target} (max_possible={$maxPossible})</info>");

		$created = 0;
		$cursor = 0;

		foreach ($proposalIds as $idx => $proposalId) {
			if ($created >= $target) break;

			$prodId = $this->pickUniqueProductId($productIds, $used, $cursor++);
			if ($prodId === null) break;

			$qty = 1 + (($idx * 2) % 12);
			$price = 25 + (($idx * 19) % 1200);
			$tax = (($idx % 3) === 0) ? 8.0 : 0.0;
			$discount = (($idx % 4) === 0) ? 15.0 : 0.0;

			try {
				$m = new ProposalProduct();

				$m->setAttribute(BC::COL_PPS_ID, $proposalId);
				$m->setAttribute(BC::COL_PRD_ID, $prodId);
				$m->setAttribute('quantity', $qty);
				$m->setAttribute('price', $price);
				$m->setAttribute('tax', $tax);
				$m->setAttribute('discount', $discount);
				$m->setAttribute('description', ($idx % 5 === 0) ? 'seeded proposal line item' : null);

				if (self::WRITE_EVERY === 1)
					$this->out->writeln("<comment>[ProposalProductSeeder] creating pps={$proposalId} prd={$prodId} qty={$qty} price={$price} tax={$tax} disc={$discount}</comment>");

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('ProposalProductSeeder failed (base pass)', [
					'proposal_id' => $proposalId,
					'product_id' => $prodId,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$spreadAttempts = 0;

		while ($created < $target && $spreadAttempts++ < ($target * 4)) {
			$proposalId = $this->pickOne($proposalIds, $spreadAttempts);
			$prodId = $this->pickUniqueProductId($productIds, $used, $cursor++);
			if ($prodId === null) break;

			$qty = 1 + (($spreadAttempts * 5) % 16);
			$price = 30 + (($spreadAttempts * 31) % 2000);
			$tax = (($spreadAttempts % 3) === 0) ? 5.0 : 0.0;
			$discount = (($spreadAttempts % 4) === 0) ? 10.0 : 0.0;

			try {
				$m = new ProposalProduct();

				$m->setAttribute(BC::COL_PPS_ID, $proposalId);
				$m->setAttribute(BC::COL_PRD_ID, $prodId);
				$m->setAttribute('quantity', $qty);
				$m->setAttribute('price', $price);
				$m->setAttribute('tax', $tax);
				$m->setAttribute('discount', $discount);
				$m->setAttribute('description', ($spreadAttempts % 6 === 0) ? 'seeded proposal line item (spread)' : null);

				if (self::WRITE_EVERY === 1)
					$this->out->writeln("<comment>[ProposalProductSeeder] creating pps={$proposalId} prd={$prodId} qty={$qty} price={$price} tax={$tax} disc={$discount}</comment>");

				$m->save();
				$created++;
			} catch (\Throwable $e) {
				Log::warning('ProposalProductSeeder failed (spread pass)', [
					'proposal_id' => $proposalId,
					'product_id' => $prodId,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		$this->out->writeln("<info>[ProposalProductSeeder] created={$created} target={$target}</info>");
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
			Log::debug('ProposalProductSeeder pluckIds failed', [
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
			Log::debug('ProposalProductSeeder pluckExistingProductIds failed', [
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
				$exists = DB::table(DC::TABLE_PPS_PRD)->where(BC::COL_PRD_ID, $candidate)->exists();
			} catch (\Throwable $e) {
				Log::debug('ProposalProductSeeder uniqueness exists-check failed', [
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
