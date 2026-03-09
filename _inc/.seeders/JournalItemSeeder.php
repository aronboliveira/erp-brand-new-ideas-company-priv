<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Enums\{TransactionType, TransferType};
use App\Models\{JournalEntry, JournalItem};
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class JournalItemSeeder extends Seeder
{
	private ConsoleOutput $out;
	// private const SECONDS_LIMIT = 4 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$clock = microtime(true);
		$itemsTable   = DC::TABLE_JRN_IT;
		$journalTable = DC::TABLE_JOURNAL_ENTRIES;

		if (!Schema::hasTable($journalTable)) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: table '{$journalTable}' does not exist.");
			return;
		}
		if (!Schema::hasTable($itemsTable)) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: table '{$itemsTable}' does not exist.");
			return;
		}

		$hardCap = 512;

		$existing = 0;
		try {
			$existing = (int) (DB::select("select count(*) as c from {$itemsTable}")[0]->c ?? 0);
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed counting existing journal items: ' . $e->getMessage());
		}

		$remainingCap = max(0, $hardCap - $existing);
		$capMultiple  = intdiv($remainingCap, 64) * 64;

		if ($capMultiple <= 0) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: hard cap reached ({$hardCap}). Existing={$existing}.");
			return;
		}

		$coaIds = $this->fetchIds(DC::TABLE_COAS);
		if (!$coaIds) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: no Chart of Accounts found (table " . DC::TABLE_COAS . ").");
			return;
		}

		$companyIds = $this->fetchUserIdsByTypes(['company', 'vendor']);
		if (!$companyIds) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Warning: no company/vendor users found; company fallback may be unstable.");
		}

		$entityIds = $this->fetchIds(DC::TABLE_USERS);

		$trxTypeMap = $this->fetchTransactionTypeMap();

		$attemptCap = 40;
		$baseNow    = CarbonImmutable::now();

		$cols = $this->existingColumns($journalTable, [
			'id',
			'currency',
			'company',
			'branch',
			'department',
			'project',
			BC::COL_EXC_RT,
			BC::COL_TRS_ID,
		]);

		// Only pick JournalEntries that currently have no JournalItems (idempotent-ish).
		$selectCols = implode(', ', array_map(static fn($c) => "je.{$c}", $cols));
		$sql = "
			select {$selectCols}
			from {$journalTable} je
			left join {$itemsTable} ji
				on ji.journal = je.id
				and ji.deleted_at is null
			where ji.id is null
			and je.deleted_at is null
			limit 20000
		";

		$journalRows = [];
		try {
			$journalRows = DB::select($sql);
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed selecting candidate journal entries: ' . $e->getMessage(), ['sql' => $sql]);
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: failed fetching journal entries.");
			return;
		}

		if (!$journalRows) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: no candidate JournalEntries without items found.");
			return;
		}

		// Build per-entry item counts using inverse-quadratic weights, heavily skewed to 1.
		$entryIds = array_values(array_map(static fn($r) => (string) ($r->id ?? ''), $journalRows));
		$entryIds = array_values(array_filter($entryIds, static fn($v) => is_string($v) && trim($v) !== ''));

		if (!$entryIds) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: invalid candidate JournalEntry ids.");
			return;
		}

		$counts = [];
		$rawTotal = 0;

		foreach ($entryIds as $id) {
			$n = $this->pickItemCount(); // 1..8
			$counts[$id] = $n;
			$rawTotal += $n;
		}

		$rawTotal = $rawTotal + ($rawTotal % 64 === 0 ? 0 : (64 - ($rawTotal % 64)));
		$totalTarget = min($rawTotal, $capMultiple);

		if ($totalTarget <= 0) {
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: could not allocate a 64-multiple within remaining cap.");
			return;
		}

		// If we cannot allocate >= 1 per entry, reduce the number of processed entries.
		if ($totalTarget < count($entryIds)) {
			$this->out->writeln(
				"<comment>[JournalItemSeeder]</comment> Warning: remaining capacity ({$capMultiple}) is below candidate entries (" . count($entryIds) . "). Seeding only {$totalTarget} entries with 1 item each."
			);
			$entryIds = array_slice($entryIds, 0, $totalTarget);
			$counts = [];
			foreach ($entryIds as $id)
				$counts[$id] = 1;
		} else {
			$counts = $this->normalizeCountsToTarget($counts, $totalTarget);
		}

		$totalPlanned = array_sum($counts);
		if ($totalPlanned % 64 !== 0) {
			// Hard rule: must be multiple of 64. Fall back to floor within capMultiple.
			$floorTarget = intdiv($totalPlanned, 64) * 64;
			if ($floorTarget <= 0) {
				$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped: planned total is not a valid 64-multiple.");
				return;
			}
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> Warning: adjusted planned total from {$totalPlanned} to {$floorTarget} to preserve 64-multiple.");
			$counts = $this->normalizeCountsToTarget($counts, $floorTarget);
			$totalPlanned = array_sum($counts);
		}

		$this->out->writeln("<info>[JournalItemSeeder]</info> Planning: candidates=" . count($entryIds) . " items={$totalPlanned} (existing={$existing}, cap={$hardCap}).");

		$created = 0;

		foreach ($entryIds as $idx => $journalId) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				Log::warning(self::class . ' seeding time limit reached, stopping early');
				$this->out->writeln("<comment>[JournalItemSeeder]</comment> Seeding time limit reached, stopping early.");
				return;
			}
			$planned = (int) ($counts[$journalId] ?? 0);
			if ($planned <= 0)
				continue;

			$journal = null;
			try {
				$journal = JournalEntry::query()->where('id', $journalId)->first();
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed loading JournalEntry: ' . $e->getMessage(), ['journal_id' => $journalId]);
			}

			if (!$journal) {
				$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped journal {$journalId}: not found.");
				continue;
			}

			$currency = (string) ($journal->getAttribute('currency') ?? SC::DEF_SITE_CURRENCY_ID);
			if (trim($currency) === '')
				$currency = (string) SC::DEF_SITE_CURRENCY_ID;

			$excRt = $journal->getAttribute(BC::COL_EXC_RT);
			$excRt = is_numeric($excRt) ? (float) $excRt : 1.0;

			$company = (string) ($journal->getAttribute('company') ?? '');
			if ($company === '' && $companyIds)
				$company = (string) $companyIds[array_rand($companyIds)];

			$branch     = $journal->getAttribute('branch');
			$department = $journal->getAttribute('department');
			$project    = $journal->getAttribute('project');

			$existingJournalTrxId = $journal->getAttribute(BC::COL_TRS_ID);
			$existingJournalTrxId = is_string($existingJournalTrxId) ? trim($existingJournalTrxId) : null;
			if ($existingJournalTrxId === '')
				$existingJournalTrxId = null;

			$total = 0.0;
			if ($planned >= 2) {
				$total = $this->randomMoneyFloat(1, 2500000, 6);
			}

			$debitLines  = $planned >= 2 ? max(1, (int) ceil($planned / 2)) : 1;
			$creditLines = $planned >= 2 ? max(1, $planned - $debitLines) : 0;
			if ($planned >= 2 && $creditLines <= 0) {
				$creditLines = 1;
				$debitLines = max(1, $planned - 1);
			}

			$debits  = $planned >= 2 ? $this->allocateAmounts($total, $debitLines, 6) : [0.0];
			$credits = $planned >= 2 ? $this->allocateAmounts($total, $creditLines, 6) : [];

			$codes = [];
			$trxIds = [];

			$line = 1;

			// Debit lines first
			for ($k = 0; $k < $debitLines; $k++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					$this->out->writeln("<comment>[JournalItemSeeder]</comment> Seeding time limit reached, stopping early.");
					return;
				}
				$amount = (float) ($debits[$k] ?? 0.0);

				$accountId = $this->pickId($coaIds, 0.0);
				if (!$accountId) {
					$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped item: no account id available.");
					continue;
				}

				$transactionId = null;
				if ($existingJournalTrxId && $k === 0 && $this->chance(0.85))
					$transactionId = $existingJournalTrxId;
				elseif ($this->chance(0.20))
					$transactionId = $this->pickAnyExistingTransactionId($trxTypeMap);

				$transactionType = null;
				if ($transactionId && isset($trxTypeMap[$transactionId]))
					$transactionType = (string) $trxTypeMap[$transactionId];

				$entityId = $this->chance(0.15) ? $this->pickId($entityIds, 0.0) : null;

				$nfe = $this->chance(0.03)
					? $this->makeNfePayload($itemsTable, $attemptCap, $baseNow)
					: [
						BC::COL_NFE_KEY      => null,
						BC::COL_NFE_NUMBER   => null,
						BC::COL_NFE_SERIES   => null,
						BC::COL_NFE_XML_PATH => null,
						BC::COL_NFE_PROTOCOL => null,
						BC::COL_NFE_AUTH_AT  => null,
					];

				$payload = [
					'journal' => $journalId,
					'account' => $accountId,
					'line' => $line,
					'posting_type' => 'debit',
					'debit' => $amount,
					'credit' => 0.0,
					'currency' => $currency,
					BC::COL_EXC_RT => $excRt,

					BC::COL_BNK_ACC => null,
					BC::COL_BNK_EXT_DT => null,

					'transaction' => $transactionId,
					BC::COL_TRS_TP => $transactionType,

					'transfer' => null,
					'payment' => null,
					BC::COL_TRF_TP => null,

					BC::COL_PIX_KEY => null,
					BC::COL_CHK_NM => null,
					BC::COL_TED_DOC_N => null,

					'company' => $company ?: ($companyIds ? $this->pickId($companyIds, 0.0) : DC::DEFAULT_UUID),
					'branch' => is_string($branch) ? $branch : null,
					'department' => is_string($department) ? $department : null,
					'project' => is_string($project) ? $project : null,
					'entity' => $entityId,

					'description' => $this->chance(0.85) ? 'Seeded journal item (debit).' : null,
					'memo' => $this->chance(0.65) ? 'Seed memo: debit allocation.' : null,
					'notes' => $this->chance(0.65) ? 'Seed notes: verify line distribution.' : null,

					BC::COL_IS_RCC => $this->chance(0.10),
					BC::COL_RCC_DT => $this->chance(0.08) ? $baseNow->subDays(random_int(0, 365))->toDateTimeString() : null,
					BC::COL_RCC_DOC => null,

					BC::COL_NFE_KEY => $nfe[BC::COL_NFE_KEY],
					BC::COL_NFE_NUMBER => $nfe[BC::COL_NFE_NUMBER],
					BC::COL_NFE_SERIES => $nfe[BC::COL_NFE_SERIES],
					BC::COL_NFE_XML_PATH => $nfe[BC::COL_NFE_XML_PATH],
					BC::COL_NFE_PROTOCOL => $nfe[BC::COL_NFE_PROTOCOL],
					BC::COL_NFE_AUTH_AT => $nfe[BC::COL_NFE_AUTH_AT],

					'attachments' => $this->chance(0.35) ? [['type' => 'note', 'value' => 'seeded_attachment']] : null,
					'taxes' => $this->chance(0.25) ? ['seed' => true, 'lines' => []] : null,
					'categories' => $this->chance(0.30) ? $this->randomCategories() : null,
					'metadata' => $this->chance(0.50) ? ['seed' => true, 'side' => 'debit', 'line' => $line] : null,
				];

				$this->out->writeln("[JIT " . ($created + 1) . "/{$totalPlanned}] je={$journalId} line={$line} debit={$amount} currency={$currency} trx=" . ($transactionId ?: 'null'));

				try {
					$item = new JournalItem();
					$item->forceFill($payload);
					$item->save();

					$code = (string) ($item->getAttribute('code') ?? '');
					if ($code !== '')
						$codes[] = $code;

					if (is_string($transactionId) && $transactionId !== '')
						$trxIds[] = $transactionId;

					$created++;
				} catch (\Throwable $e) {
					Log::error(self::class . ' failed saving JournalItem (debit): ' . $e->getMessage(), [
						'journal' => $journalId,
						'line' => $line,
					]);
				}

				$line++;
				if ($created >= $totalPlanned)
					break 2;
			}

			// Credit lines
			for ($k = 0; $k < $creditLines; $k++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					$this->out->writeln("<comment>[JournalItemSeeder]</comment> Seeding time limit reached, stopping early.");
					return;
				}
				$amount = (float) ($credits[$k] ?? 0.0);

				$accountId = $this->pickId($coaIds, 0.0);
				if (!$accountId) {
					$this->out->writeln("<comment>[JournalItemSeeder]</comment> Skipped item: no account id available.");
					continue;
				}

				$transactionId = null;
				if ($existingJournalTrxId && $k === 0 && $this->chance(0.55))
					$transactionId = $existingJournalTrxId;
				elseif ($this->chance(0.15))
					$transactionId = $this->pickAnyExistingTransactionId($trxTypeMap);

				$transactionType = null;
				if ($transactionId && isset($trxTypeMap[$transactionId]))
					$transactionType = (string) $trxTypeMap[$transactionId];

				$entityId = $this->chance(0.12) ? $this->pickId($entityIds, 0.0) : null;

				$nfe = $this->chance(0.02)
					? $this->makeNfePayload($itemsTable, $attemptCap, $baseNow)
					: [
						BC::COL_NFE_KEY      => null,
						BC::COL_NFE_NUMBER   => null,
						BC::COL_NFE_SERIES   => null,
						BC::COL_NFE_XML_PATH => null,
						BC::COL_NFE_PROTOCOL => null,
						BC::COL_NFE_AUTH_AT  => null,
					];

				$payload = [
					'journal' => $journalId,
					'account' => $accountId,
					'line' => $line,
					'posting_type' => 'credit',
					'debit' => 0.0,
					'credit' => $amount,
					'currency' => $currency,
					BC::COL_EXC_RT => $excRt,

					BC::COL_BNK_ACC => null,
					BC::COL_BNK_EXT_DT => null,

					'transaction' => $transactionId,
					BC::COL_TRS_TP => $transactionType,

					'transfer' => null,
					'payment' => null,
					BC::COL_TRF_TP => null,

					BC::COL_PIX_KEY => null,
					BC::COL_CHK_NM => null,
					BC::COL_TED_DOC_N => null,

					'company' => $company ?: ($companyIds ? $this->pickId($companyIds, 0.0) : DC::DEFAULT_UUID),
					'branch' => is_string($branch) ? $branch : null,
					'department' => is_string($department) ? $department : null,
					'project' => is_string($project) ? $project : null,
					'entity' => $entityId,

					'description' => $this->chance(0.85) ? 'Seeded journal item (credit).' : null,
					'memo' => $this->chance(0.65) ? 'Seed memo: credit allocation.' : null,
					'notes' => $this->chance(0.65) ? 'Seed notes: verify line distribution.' : null,

					BC::COL_IS_RCC => $this->chance(0.10),
					BC::COL_RCC_DT => $this->chance(0.08) ? $baseNow->subDays(random_int(0, 365))->toDateTimeString() : null,
					BC::COL_RCC_DOC => null,

					BC::COL_NFE_KEY => $nfe[BC::COL_NFE_KEY],
					BC::COL_NFE_NUMBER => $nfe[BC::COL_NFE_NUMBER],
					BC::COL_NFE_SERIES => $nfe[BC::COL_NFE_SERIES],
					BC::COL_NFE_XML_PATH => $nfe[BC::COL_NFE_XML_PATH],
					BC::COL_NFE_PROTOCOL => $nfe[BC::COL_NFE_PROTOCOL],
					BC::COL_NFE_AUTH_AT => $nfe[BC::COL_NFE_AUTH_AT],

					'attachments' => $this->chance(0.35) ? [['type' => 'note', 'value' => 'seeded_attachment']] : null,
					'taxes' => $this->chance(0.25) ? ['seed' => true, 'lines' => []] : null,
					'categories' => $this->chance(0.30) ? $this->randomCategories() : null,
					'metadata' => $this->chance(0.50) ? ['seed' => true, 'side' => 'credit', 'line' => $line] : null,
				];

				$this->out->writeln("[JIT " . ($created + 1) . "/{$totalPlanned}] je={$journalId} line={$line} credit={$amount} currency={$currency} trx=" . ($transactionId ?: 'null'));

				try {
					$item = new JournalItem();
					$item->forceFill($payload);
					$item->save();

					$code = (string) ($item->getAttribute('code') ?? '');
					if ($code !== '')
						$codes[] = $code;

					if (is_string($transactionId) && $transactionId !== '')
						$trxIds[] = $transactionId;

					$created++;
				} catch (\Throwable $e) {
					Log::error(self::class . ' failed saving JournalItem (credit): ' . $e->getMessage(), [
						'journal' => $journalId,
						'line' => $line,
					]);
				}

				$line++;
				if ($created >= $totalPlanned)
					break;
			}

			// Update JournalEntry.items and JournalEntry.transactions through the model (so the boot filters run).
			try {
				$existingItems = $journal->getAttribute('items');
				if (!is_array($existingItems))
					$existingItems = [];

				$existingTrs = $journal->getAttribute('transactions');
				if (!is_array($existingTrs))
					$existingTrs = [];

				$mergedItems = array_values(array_unique(array_merge($existingItems, $codes)));
				$mergedTrs   = array_values(array_unique(array_merge($existingTrs, $trxIds)));

				$journal->setAttribute('items', $mergedItems);
				$journal->setAttribute('transactions', $mergedTrs);

				// Align totals to the seeded balanced total when we created >= 2 lines; keep 0 otherwise.
				$ttl = $planned >= 2 ? $total : 0.0;

				$journal->setAttribute(BC::COL_TTL_DBT, number_format((float) $ttl, 6, '.', ''));
				$journal->setAttribute(BC::COL_TTL_CRT, number_format((float) $ttl, 6, '.', ''));

				$journal->save();
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed updating JournalEntry items/transactions: ' . $e->getMessage(), [
					'journal_id' => $journalId,
				]);
			}

			if ($created >= $totalPlanned)
				break;
		}

		$this->out->writeln("<info>[JournalItemSeeder]</info> Done. Inserted={$created} (planned={$totalPlanned}, existing={$existing}, cap={$hardCap}).");
	}

	private function existingColumns(string $table, array $columns): array
	{
		$out = [];
		foreach ($columns as $col) {
			$c = trim((string) $col);
			if ($c !== '' && Schema::hasColumn($table, $c))
				$out[] = $c;
		}
		return $out ?: ['id'];
	}

	private function fetchIds(string $table): array
	{
		if (!Schema::hasTable($table))
			return [];

		try {
			$rows = DB::select("select id from {$table} limit 20000");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return array_values($out);
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchIds failed for {$table}: " . $e->getMessage());
			return [];
		}
	}

	private function fetchUserIdsByTypes(array $types): array
	{
		if (!Schema::hasTable(DC::TABLE_USERS))
			return [];

		$types = array_values(array_filter(array_map(static fn($v) => strtolower(trim((string) $v)), $types)));
		if (!$types)
			return [];

		$placeholders = implode(', ', array_fill(0, count($types), '?'));

		try {
			$rows = DB::select(
				"select id from " . DC::TABLE_USERS . " where lower(type) in ({$placeholders}) limit 20000",
				$types
			);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchUserIdsByTypes failed: " . $e->getMessage(), ['types' => $types]);
			return [];
		}
	}

	private function fetchTransactionTypeMap(): array
	{
		if (!Schema::hasTable(DC::TABLE_TRS))
			return [];

		$colType = BC::COL_PAY_TP;
		if (!Schema::hasColumn(DC::TABLE_TRS, $colType))
			return [];

		try {
			$rows = DB::select("select id, {$colType} as t from " . DC::TABLE_TRS . " limit 20000");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				$t  = is_string($r->t ?? null) ? trim((string) $r->t) : '';
				if ($id !== '' && $t !== '')
					$out[$id] = TransactionType::normalize($t)?->value ?? TransactionType::Other->value;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchTransactionTypeMap failed: " . $e->getMessage());
			return [];
		}
	}

	private function pickAnyExistingTransactionId(array $trxTypeMap): ?string
	{
		if (!$trxTypeMap)
			return null;

		$keys = array_keys($trxTypeMap);
		return $keys ? (string) $keys[array_rand($keys)] : null;
	}

	private function chance(float $p): bool
	{
		return mt_rand() / mt_getrandmax() < max(0.0, min(1.0, $p));
	}

	private function pickId(array $ids, float $nullChance = 0.1): ?string
	{
		if (!$ids || $this->chance($nullChance))
			return null;

		return (string) $ids[array_rand($ids)];
	}

	/**
	 * Inverse-quadratic distribution (1/k^2), with additional bias to 1.
	 */
	private function pickItemCount(): int
	{
		$weights = [];
		for ($k = 1; $k <= 8; $k++)
			$weights[$k] = 1.0 / ($k * $k);

		$weights[1] = $weights[1] * 6.0; // extra skew to 1

		$sum = array_sum($weights);
		$r = (mt_rand() / mt_getrandmax()) * $sum;

		$acc = 0.0;
		foreach ($weights as $k => $w) {
			$acc += $w;
			if ($r <= $acc)
				return (int) $k;
		}

		return 1;
	}

	/**
	 * Adjusts counts map so that sum == target, each 1..8.
	 */
	private function normalizeCountsToTarget(array $counts, int $target): array
	{
		$ids = array_keys($counts);
		if (!$ids)
			return $counts;

		$sum = array_sum($counts);

		// Reduce
		$guard = 0;
		while ($sum > $target && $guard < 200000) {
			$guard++;

			$found = false;
			foreach ($ids as $id) {
				$v = (int) ($counts[$id] ?? 1);
				if ($v > 1) {
					$counts[$id] = $v - 1;
					$sum--;
					$found = true;
					if ($sum <= $target) break;
				}
			}

			if (!$found)
				break;
		}

		// Increase
		$guard = 0;
		while ($sum < $target && $guard < 200000) {
			$guard++;

			$found = false;
			shuffle($ids);

			foreach ($ids as $id) {
				$v = (int) ($counts[$id] ?? 1);
				if ($v < 8) {
					$counts[$id] = $v + 1;
					$sum++;
					$found = true;
					if ($sum >= $target) break;
				}
			}

			if (!$found)
				break;
		}

		// Clamp final safety
		foreach ($counts as $id => $v) {
			$v = (int) $v;
			if ($v < 1) $v = 1;
			if ($v > 8) $v = 8;
			$counts[$id] = $v;
		}

		return $counts;
	}

	private function allocateAmounts(float $total, int $parts, int $scale): array
	{
		if ($parts <= 1)
			return [$this->roundMoney($total, $scale)];

		$weights = [];
		$wSum = 0.0;

		for ($i = 0; $i < $parts; $i++) {
			$w = max(0.000001, mt_rand() / mt_getrandmax());
			$weights[] = $w;
			$wSum += $w;
		}

		$out = [];
		$acc = 0.0;

		for ($i = 0; $i < $parts; $i++) {
			$raw = ($weights[$i] / $wSum) * $total;
			$val = $this->roundMoney($raw, $scale);
			$out[] = $val;
			$acc += $val;
		}

		// Fix rounding drift on last line
		$drift = $this->roundMoney($total - $acc, $scale);
		$lastIdx = $parts - 1;
		$out[$lastIdx] = $this->roundMoney($out[$lastIdx] + $drift, $scale);

		return $out;
	}

	private function roundMoney(float $value, int $scale): float
	{
		$factor = (float) pow(10, max(0, $scale));
		return round($value * $factor) / $factor;
	}

	private function randomMoneyFloat(int $minWhole, int $maxWhole, int $scale): float
	{
		$whole = random_int($minWhole, $maxWhole);
		$fracMax = (int) pow(10, max(0, $scale)) - 1;
		$frac = random_int(0, max(0, $fracMax));
		$s = (string) $whole . '.' . str_pad((string) $frac, $scale, '0', STR_PAD_LEFT);
		return (float) $s;
	}

	private function makeNfePayload(string $itemsTable, int $attemptCap, CarbonImmutable $now): array
	{
		$key = null;

		$attempts = 0;
		while ($attempts < $attemptCap) {
			$attempts++;

			$candidate = $this->randomDigits(44);
			$exists = false;

			try {
				$exists = (bool) (DB::select(
					"select 1 as e from {$itemsTable} where " . BC::COL_NFE_KEY . " = ? limit 1",
					[$candidate]
				)[0]->e ?? false);
			} catch (\Throwable $e) {
				Log::debug(self::class . ' nfe_key exists check failed: ' . $e->getMessage());
			}

			if (!$exists) {
				$key = $candidate;
				break;
			}
		}

		if ($key === null)
			$this->out->writeln("<comment>[JournalItemSeeder]</comment> NFe key attempts exhausted; leaving NFe fields null.");

		return [
			BC::COL_NFE_KEY      => $key,
			BC::COL_NFE_NUMBER   => $key ? (string) random_int(1, 999999999) : null,
			BC::COL_NFE_SERIES   => $key ? (string) random_int(1, 999) : null,
			BC::COL_NFE_XML_PATH => $key ? ('/storage/nfe/' . $key . '.xml') : null,
			BC::COL_NFE_PROTOCOL => $key ? (string) random_int(1000000000000, 9999999999999) : null,
			BC::COL_NFE_AUTH_AT  => $key ? $now->subDays(random_int(0, 1200))->toDateTimeString() : null,
		];
	}

	private function randomDigits(int $len): string
	{
		$out = '';
		for ($i = 0; $i < $len; $i++)
			$out .= (string) random_int(0, 9);
		return $out;
	}

	private function randomCategories(): array
	{
		$all = ['seeded', 'ops', 'finance', 'ledger', 'compliance', 'cashflow', 'recon'];
		$k = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $k; $i++)
			$out[] = $all[array_rand($all)];
		return array_values(array_unique($out));
	}
}
