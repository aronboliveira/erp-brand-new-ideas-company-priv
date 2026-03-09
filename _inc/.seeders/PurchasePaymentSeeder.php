<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Models\{PurchasePayment};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class PurchasePaymentSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const CAP = 2048;
	private const MAX_PER_PURCHASE = 8;

	private const CHUNK = 400;

	private const UNIQUE_ATTEMPTS = 24;
	private const PICK_ATTEMPTS   = 24;
	private const NFE_ATTEMPTS    = 24;

	private const KEEP_PCT_HIGH = 92;
	private const KEEP_PCT_MED  = 85;
	private const KEEP_PCT_LOW  = 75;
	private const KEEP_PCT_NFE  = 6;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$ppTable = DC::TABLE_PRC_PAY;
		$purchasesTable = DC::TABLE_PURCHASES;
		$paymentsTable = DC::TABLE_PAY;
		$bankAccTable = DC::TABLE_BANK_ACC;

		if (!Schema::hasTable($purchasesTable)) {
			$this->out->writeln("<error>[PurchasePaymentSeeder] purchases table not found: {$purchasesTable}</error>");
			return;
		}
		if (!Schema::hasTable($ppTable)) {
			$this->out->writeln("<error>[PurchasePaymentSeeder] purchase_payments table not found: {$ppTable}</error>");
			return;
		}

		$purchaseIds = $this->pluckPurchaseIds($purchasesTable, self::CAP);
		if (!$purchaseIds) {
			$this->out->writeln("<error>[PurchasePaymentSeeder] No purchases found. Aborting.</error>");
			return;
		}

		$bankAccountIds = Schema::hasTable($bankAccTable) ? $this->pluckIds($bankAccTable, 50000) : [];
		$allPaymentIds  = Schema::hasTable($paymentsTable) ? $this->pluckIds($paymentsTable, 50000) : [];

		$paymentPurchaseFk = $this->detectPurchaseFkInPayments($paymentsTable);
		$paymentIdsByPurchase = ($paymentPurchaseFk && $allPaymentIds)
			? $this->pluckPaymentIdsByPurchase($paymentsTable, $paymentPurchaseFk, $purchaseIds)
			: [];

		$existingCounts = $this->pluckExistingCountsByPurchase($ppTable, $purchaseIds, BC::COL_PRC_ID);

		$mandatoryNeeded = 0;
		$maxCreatable = 0;

		foreach ($purchaseIds as $pid) {
			$cur = (int) ($existingCounts[$pid] ?? 0);
			if ($cur <= 0) $mandatoryNeeded++;
			$maxCreatable += max(0, self::MAX_PER_PURCHASE - $cur);
		}

		$maxWithinCap = min(self::CAP, $maxCreatable);
		$target = $this->bestEffortTarget64($mandatoryNeeded, $maxWithinCap);

		$this->out->writeln("<info>[PurchasePaymentSeeder] purchases=" . count($purchaseIds) . " mandatory_needed={$mandatoryNeeded} max_creatable={$maxCreatable} target={$target} cap=" . self::CAP . "</info>");

		if ($target <= 0) {
			$this->out->writeln("<error>[PurchasePaymentSeeder] target resolved to 0. Nothing to do.</error>");
			return;
		}

		$methods = $this->paymentMethods();
		$created = 0;
		$seq = 0;

		// Phase 1: ensure every purchase has at least 1 PurchasePayment (unless already has)
		foreach ($purchaseIds as $i => $purchaseId) {
			if (microtime(true) - $clock >= self::SECONDS_LIMIT) {
				$this->out->writeln("<comment>[PurchasePaymentSeeder] time limit reached, stopping at purchase index {$i}.</comment>");
				return;
			}
			if ($created >= $target) break;

			$cur = (int) ($existingCounts[$purchaseId] ?? 0);
			if ($cur >= 1) continue;
			if ($cur >= self::MAX_PER_PURCHASE) continue;

			$seq++;
			try {
				if ($this->createOne(
					$ppTable,
					$purchaseId,
					$seq,
					$methods,
					$bankAccountIds,
					$allPaymentIds,
					$paymentIdsByPurchase[$purchaseId] ?? [],
					$existingCounts,
					$clock
				)) {
					$created++;
				}
			} catch (\Throwable $e) {
				Log::warning('PurchasePaymentSeeder phase1 failed', [
					'purchase_id' => $purchaseId,
					'seq' => $seq,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		// Phase 2: spread additional payments (up to 8 per purchase), until target/cap
		for ($round = 2; $round <= self::MAX_PER_PURCHASE && $created < $target; $round++) {
			foreach ($purchaseIds as $purchaseId) {
				if ($created >= $target) break;
				if (microtime(true) - $clock >= self::SECONDS_LIMIT) {
					$this->out->writeln("<comment>[PurchasePaymentSeeder] time limit reached, stopping at round {$round}.</comment>");
					return;
				}
				$cur = (int) ($existingCounts[$purchaseId] ?? 0);
				if ($cur >= $round) continue;
				if ($cur >= self::MAX_PER_PURCHASE) continue;

				$seq++;
				try {
					if ($this->createOne(
						$ppTable,
						$purchaseId,
						$seq,
						$methods,
						$bankAccountIds,
						$allPaymentIds,
						$paymentIdsByPurchase[$purchaseId] ?? [],
						$existingCounts,
						$clock
					)) {
						$created++;
					}
				} catch (\Throwable $e) {
					Log::warning('PurchasePaymentSeeder phase2 failed', [
						'purchase_id' => $purchaseId,
						'round' => $round,
						'seq' => $seq,
						'msg' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
			}
		}

		$this->out->writeln("<info>[PurchasePaymentSeeder] created={$created} target={$target}</info>");
	}

	private function createOne(
		string $ppTable,
		string $purchaseId,
		int $seq,
		array $methods,
		array $bankAccountIds,
		array $allPaymentIds,
		array $purchasePaymentCandidates,
		array &$existingCounts,
		float $clock,
	): bool {
		$attempt = 0;
		if (!empty($clock)) $clock = microtime(true);
		while ($attempt++ < self::PICK_ATTEMPTS) {
			$this->out->writeln("<comment>[PurchasePaymentSeeder] createOne attempt {$attempt} for purchase={$purchaseId} seq={$seq}</comment>");
			if (microtime(true) - $clock >= self::SECONDS_LIMIT) {
				$this->out->writeln("<comment>[PurchasePaymentSeeder] time limit reached during createOne for purchase={$purchaseId} seq={$seq}, aborting pick attempts.</comment>");
				return false;
			}
			$method = (int) ($methods[$seq % count($methods)] ?? 0);
			$payId = null;
			$baccId = null;
			$preferPurchasePayments = $purchasePaymentCandidates && $this->shouldKeep(self::KEEP_PCT_MED);
			if ($preferPurchasePayments) {
				$payId = $purchasePaymentCandidates[$seq % count($purchasePaymentCandidates)] ?? null;
			} elseif ($allPaymentIds && $this->shouldKeep(self::KEEP_PCT_LOW)) {
				$payId = $allPaymentIds[$seq % count($allPaymentIds)] ?? null;
			}

			$needsBank = $this->methodTypicallyNeedsBankAccount($method);
			if ($needsBank && $bankAccountIds && $this->shouldKeep(self::KEEP_PCT_HIGH))
				$baccId = $bankAccountIds[$seq % count($bankAccountIds)] ?? null;

			if ($this->existsPurchasePaymentCombo($ppTable, $purchaseId, $payId, $baccId)) {
				$seq++;
				continue;
			}

			$date = $this->dateForSeq($seq);
			$amount = $this->amountForSeq($seq);

			$reference = $this->tolerant("PRCPAY-" . substr($purchaseId, 0, 8) . "-{$seq}", self::KEEP_PCT_MED);
			$description = $this->tolerant(($seq % 3 === 0) ? "seeded purchase payment {$seq}" : (string) (fake()->sentence(10) ?? ''), self::KEEP_PCT_MED);
			$addReceipt = $this->tolerant(($seq % 5 === 0) ? "receipt://{$purchaseId}/{$seq}" : null, self::KEEP_PCT_LOW);

			$nfe = $this->buildOptionalNfePayload($ppTable, $seq);

			$this->out->writeln(
				"<comment>[PurchasePaymentSeeder] creating purchase={$purchaseId} method={$method} date={$date} amount={$amount} pay_id=" . ($payId ?: 'null') . " bacc_id=" . ($baccId ?: 'null') . "</comment>"
			);

			try {
				$m = new PurchasePayment();
				$m->setAttribute(BC::COL_PRC_ID, $purchaseId);
				$m->setAttribute(BC::COL_PAY_ID, $payId);
				$m->setAttribute(BC::COL_BACC_ID, $baccId);
				$m->setAttribute('date', $date);
				$m->setAttribute('amount', $amount);
				$m->setAttribute(BC::COL_PAY_MTD, $method);
				$m->setAttribute('reference', $reference);
				$m->setAttribute('description', $description);
				$m->setAttribute(BC::COL_ADD_RCP, $addReceipt);
				$this->out->writeln("<comment>[PurchasePaymentSeeder] set reference, description, add_receipt</comment>");
				if ($nfe !== null) {
					foreach ($nfe as $k => $v) $m->setAttribute($k, $v);
				}
				$this->out->writeln("<comment>[PurchasePaymentSeeder] saving purchase payment for purchase={$purchaseId} seq={$seq}</comment>");
				$m->save();

				$existingCounts[$purchaseId] = (int) ($existingCounts[$purchaseId] ?? 0) + 1;

				return true;
			} catch (\Throwable $e) {
				Log::warning('PurchasePaymentSeeder createOne save failed', [
					'purchase_id' => $purchaseId,
					'seq' => $seq,
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
				return false;
			}
		}

		$this->out->writeln("<error>[PurchasePaymentSeeder] exceeded pick attempts for purchase={$purchaseId} seq={$seq}, skipping.</error>");
		return false;
	}

	private function buildOptionalNfePayload(string $ppTable, int $seq): ?array
	{
		if (!$this->shouldKeep(self::KEEP_PCT_NFE)) return null;

		$key = $this->generateUniqueNfeKey($ppTable);
		if ($key === null) return null;

		$num = (string) (100000 + ($seq % 900000));
		$series = (string) (1 + ($seq % 9));

		$authAt = $this->shouldKeep(self::KEEP_PCT_MED)
			? Carbon::now()->subDays($seq % 60)->setTime((int) ($seq % 24), 0, 0)->toDateTimeString()
			: null;

		return [
			BC::COL_NFE_KEY => $key,
			BC::COL_NFE_NUMBER => $this->tolerant($num, self::KEEP_PCT_HIGH),
			BC::COL_NFE_SERIES => $this->tolerant($series, self::KEEP_PCT_HIGH),
			BC::COL_NFE_XML_PATH => $this->tolerant("storage/nfe/xml/{$key}.xml", self::KEEP_PCT_LOW),
			BC::COL_NFE_PROTOCOL => $this->tolerant((string) random_int(100000000000000, 999999999999999), self::KEEP_PCT_LOW),
			BC::COL_NFE_AUTH_AT => $authAt,
		];
	}

	private function generateUniqueNfeKey(string $ppTable): ?string
	{
		$attempt = 0;

		do {
			$attempt++;
			if ($attempt > self::NFE_ATTEMPTS) return null;

			$key = $this->digits(44);
			$exists = false;

			try {
				$row = DB::selectOne("select 1 as x from `{$ppTable}` where `" . BC::COL_NFE_KEY . "` = ? limit 1", [$key]);
				$exists = $row !== null;
			} catch (\Throwable $e) {
				Log::warning('PurchasePaymentSeeder nfe_key exists-check failed', [
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
				$exists = false;
			}

			if (!$exists) return $key;
		} while (true);
	}

	private function digits(int $n): string
	{
		$out = '';
		for ($i = 0; $i < $n; $i++) {
			try {
				$out .= (string) random_int(0, 9);
			} catch (\Throwable) {
				$out .= (string) (($i + 7) % 10);
			}
		}
		return $out;
	}

	private function existsPurchasePaymentCombo(string $ppTable, string $purchaseId, ?string $payId, ?string $baccId): bool
	{
		$sql = "select 1 as x from `{$ppTable}` where `" . BC::COL_PRC_ID . "` = ?";
		$bindings = [$purchaseId];

		if ($payId === null) $sql .= " and `" . BC::COL_PAY_ID . "` is null";
		else {
			$sql .= " and `" . BC::COL_PAY_ID . "` = ?";
			$bindings[] = $payId;
		}

		if ($baccId === null) $sql .= " and `" . BC::COL_BACC_ID . "` is null";
		else {
			$sql .= " and `" . BC::COL_BACC_ID . "` = ?";
			$bindings[] = $baccId;
		}

		$sql .= " limit 1";

		try {
			return DB::selectOne($sql, $bindings) !== null;
		} catch (\Throwable $e) {
			Log::warning('PurchasePaymentSeeder combo exists-check failed', [
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return false;
		}
	}

	private function amountForSeq(int $seq): string
	{
		try {
			$min = 1000;
			$max = 5000000;
			$cents = random_int($min, $max);
			return number_format($cents / 100, 2, '.', '');
		} catch (\Throwable $e) {
			Log::debug('PurchasePaymentSeeder amount random_int failed', [
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return number_format((($seq % 10000) + 1000) / 100, 2, '.', '');
		}
	}

	private function dateForSeq(int $seq): string
	{
		return Carbon::now()
			->subDays($seq % 120)
			->format('Y-m-d');
	}

	private function detectPurchaseFkInPayments(string $paymentsTable): ?string
	{
		if (!Schema::hasTable($paymentsTable)) return null;

		if (Schema::hasColumn($paymentsTable, BC::COL_PRC_ID)) return BC::COL_PRC_ID;
		if (Schema::hasColumn($paymentsTable, 'purchase')) return 'purchase';

		return null;
	}

	private function pluckPaymentIdsByPurchase(string $paymentsTable, string $purchaseFk, array $purchaseIds): array
	{
		$map = [];

		foreach (array_chunk($purchaseIds, self::CHUNK) as $chunk) {
			$ph = implode(',', array_fill(0, count($chunk), '?'));
			$sql = "select `id`, `{$purchaseFk}` as `pid` from `{$paymentsTable}` where `{$purchaseFk}` in ({$ph})";
			try {
				$rows = DB::select($sql, array_values($chunk));
				foreach ($rows as $r) {
					$pid = (string) ($r->pid ?? '');
					$id = (string) ($r->id ?? '');
					if ($pid === '' || $id === '') continue;
					$map[$pid] ??= [];
					$map[$pid][] = $id;
				}
			} catch (\Throwable $e) {
				Log::warning('PurchasePaymentSeeder pluckPaymentIdsByPurchase failed', [
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		return $map;
	}

	private function pluckExistingCountsByPurchase(string $ppTable, array $purchaseIds, string $purchaseCol): array
	{
		$map = [];

		foreach (array_chunk($purchaseIds, self::CHUNK) as $chunk) {
			$ph = implode(',', array_fill(0, count($chunk), '?'));
			$sql = "select `{$purchaseCol}` as pid, count(*) as c from `{$ppTable}` where `{$purchaseCol}` in ({$ph}) group by `{$purchaseCol}`";
			try {
				$rows = DB::select($sql, array_values($chunk));
				foreach ($rows as $r) {
					$pid = (string) ($r->pid ?? '');
					if ($pid === '') continue;
					$map[$pid] = (int) ($r->c ?? 0);
				}
			} catch (\Throwable $e) {
				Log::warning('PurchasePaymentSeeder pluckExistingCountsByPurchase failed', [
					'msg' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]);
			}
		}

		return $map;
	}

	private function pluckPurchaseIds(string $purchasesTable, int $limit): array
	{
		$sql = "select `id` from `{$purchasesTable}`";
		if (Schema::hasColumn($purchasesTable, 'deleted_at')) $sql .= " where `deleted_at` is null";
		$sql .= " limit " . (int) $limit;

		try {
			$rows = DB::select($sql);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning('PurchasePaymentSeeder pluckPurchaseIds failed', [
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function pluckIds(string $table, int $limit = 50000): array
	{
		try {
			$rows = DB::select("select `id` from `{$table}` limit " . (int) $limit);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::debug('PurchasePaymentSeeder pluckIds failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function paymentMethods(): array
	{
		return [0, 1, 2, 3, 4, 5, 6, 7];
	}

	private function methodTypicallyNeedsBankAccount(int $method): bool
	{
		return !in_array($method, [0, 3], true);
	}

	private function shouldKeep(int $keepPercent): bool
	{
		$keepPercent = max(0, min(100, $keepPercent));
		try {
			return random_int(1, 100) <= $keepPercent;
		} catch (\Throwable $e) {
			Log::debug('PurchasePaymentSeeder shouldKeep random_int failed', [
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return true;
		}
	}

	private function tolerant(mixed $value, int $keepPercent): mixed
	{
		if ($value === null) return null;
		return $this->shouldKeep($keepPercent) ? $value : null;
	}

	private function bestEffortTarget64(int $minRequired, int $maxAllowed): int
	{
		$minRequired = max(0, $minRequired);
		$maxAllowed = max(0, $maxAllowed);

		if ($maxAllowed <= 0) return 0;

		$floor = $maxAllowed - ($maxAllowed % 64);
		if ($floor >= $minRequired && $floor > 0) return $floor;

		$ceil = $minRequired % 64 === 0 ? $minRequired : ($minRequired + (64 - ($minRequired % 64)));
		if ($ceil <= $maxAllowed) return $ceil;

		$this->out->writeln("<error>[PurchasePaymentSeeder] cannot satisfy 64-multiple constraint within bounds: min={$minRequired} max={$maxAllowed}. Using min.</error>");
		return $minRequired;
	}
}
