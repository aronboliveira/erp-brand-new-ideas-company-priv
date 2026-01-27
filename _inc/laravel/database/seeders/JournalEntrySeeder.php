<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{EvaluationStatus, LedgerBookType, PaymentType, UserType};
use App\Models\JournalEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class JournalEntrySeeder extends Seeder
{
	private ConsoleOutput $out;

	private const SECONDS_LIMIT = 6 * 10 ** 2;
	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$clock = microtime(true);
		$table = DC::TABLE_JOURNAL_ENTRIES;

		if (!Schema::hasTable($table)) {
			$this->out->writeln("<comment>[JournalEntrySeeder]</comment> Skipped: table '{$table}' does not exist.");
			return;
		}

		$statuses = array_values(EvaluationStatus::cases());
		$payTypes = array_values(PaymentType::cases());
		$bookTypes = array_values(LedgerBookType::cases());

		$minNeeded = max(
			16 * count($statuses),
			16 * count($payTypes),
			16 * count($bookTypes)
		);

		$desired = (int) (env('JOURNAL_ENTRY_SEED_COUNT') ?: 1024);
		$rawTotal = max($desired, $minNeeded);

		$rawTotal = $rawTotal + ($rawTotal % 64 === 0 ? 0 : (64 - ($rawTotal % 64)));

		$hardCap = 1024;

		$existing = $this->safeCount($table);
		$remainingCap = max(0, $hardCap - $existing);
		$total = min($rawTotal, $remainingCap);

		// enforce 64xN strictly
		$total = $total - ($total % 64);
		if ($total < 64) {
			$this->out->writeln(
				"<comment>[JournalEntrySeeder]</comment> Skipped: remaining cap ({$remainingCap}) cannot satisfy 64xN rule. Existing={$existing}, cap={$hardCap}."
			);
			return;
		}

		if ($total < $minNeeded) {
			$this->out->writeln(
				"<comment>[JournalEntrySeeder]</comment> Warning: total ({$total}) is below minNeeded ({$minNeeded}); enum coverage may be incomplete."
			);
		}

		$typeCol = $this->resolveUserTypeColumn();
		$userRows = $this->fetchRows(DC::TABLE_USERS, ['id', $typeCol]);

		$userIds = array_values(array_filter(array_map(
			fn($r) => (string) ($r->id ?? ''),
			$userRows
		), fn($v) => is_string($v) && trim($v) !== ''));

		$reviewerIds = [];
		$companyIds = [];

		$allowedReviewerTypes = array_values(array_unique([
			UserType::Accountant->value,
			UserType::Admin->value,
			UserType::SuperAdmin->value,
			'accountant',
			'admin',
			'super admin',
			'super_admin',
			'superadmin',
			'sa',
			'adm',
			'act',
		]));

		$allowedCompanyTypes = array_values(array_unique([
			UserType::Company->value,
			UserType::Vendor->value,
			'company',
			'vendor',
			'cpn',
			'vd',
		]));

		foreach ($userRows as $r) {
			$id = (string) ($r->id ?? '');
			if (trim($id) === '') continue;

			$tRaw = '';
			try {
				$tRaw = (string) ($r->{$typeCol} ?? '');
			} catch (\Throwable) {
				$tRaw = '';
			}
			$t = mb_strtolower(trim($tRaw));

			if (in_array($t, $allowedReviewerTypes, true))
				$reviewerIds[] = $id;

			if (in_array($t, $allowedCompanyTypes, true))
				$companyIds[] = $id;
		}

		$reviewerIds = array_values(array_unique($reviewerIds));
		$companyIds  = array_values(array_unique($companyIds));

		if (!$companyIds) {
			$this->out->writeln("<comment>[JournalEntrySeeder]</comment> Warning: no Company/Vendor users found; falling back to any user for 'company'.");
			$companyIds = $userIds;
		}

		$branchIds = $this->fetchIds(DC::TABLE_BRANCHES);
		$departmentIds = $this->fetchIds(DC::TABLE_DEPARTMENTS);
		$projectIds = $this->fetchIds(DC::TABLE_PROJECTS);

		$docIds = $this->fetchIds(DC::TABLE_DOCS);

		$invoiceIds = $this->fetchIds(DC::TABLE_INVS);
		$billIds = $this->fetchIds(DC::TABLE_BILLS);
		$orderIds = $this->fetchIds(DC::TABLE_ORDERS);
		$transactionIds = $this->fetchIds(DC::TABLE_TRS);
		$paymentIds = $this->fetchIds(DC::TABLE_PAY);
		$payslipIds = $this->fetchIds(DC::TABLE_PAY_SLP);
		$expenseIds = $this->fetchIds(DC::TABLE_EXP);
		$posIds = $this->fetchIds(DC::TABLE_POS);
		$posPaymentPairs = $this->fetchRows(DC::TABLE_POS_PAY, ['id', BC::COL_POS_ID]);

		$creditNoteIds = $this->fetchIds(DC::TABLE_CR_NOTES);
		$debitNoteIds = $this->fetchIds(DC::TABLE_DB_NOTES);
		$loanIds = $this->fetchIds(DC::TABLE_LN);
		$allowanceIds = $this->fetchIds(DC::TABLE_ALW);
		$taxIds = $this->fetchIds(DC::TABLE_TAXES);

		$revenueIds = $this->fetchIds(DC::TABLE_RVN);
		$contractIds = $this->fetchIds(DC::TABLE_CONTRACTS);
		$dealIds = $this->fetchIds(DC::TABLE_DEALS);

		$baseNow = CarbonImmutable::now();

		$attemptCap = 40;
		$nfeAttemptCap = 18;

		$createdIds = [];

		$linkModes = ['invoice', 'bill', 'pos', 'mixed'];

		for ($i = 0; $i < $total; $i++) {

			if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
				Log::warning(self::class . ' seeding time limit reached, stopping early');
				return;
			}
			$status = $statuses[$i % count($statuses)];
			$payType = $payTypes[$i % count($payTypes)];
			$bookType = $bookTypes[$i % count($bookTypes)];

			$mode = $linkModes[$i % count($linkModes)];

			$entryDate = $baseNow->subDays(random_int(0, 3650));

			$postingDate = null;
			if ($this->chance(0.92)) {
				$postingDate = $entryDate->addDays(random_int(0, 12))->toDateString();
				if ($postingDate < $entryDate->toDateString()) $postingDate = $entryDate->toDateString();
			}

			$reversalDate = $this->chance(0.10)
				? $entryDate->addDays(random_int(1, 90))->toDateString()
				: null;

			$period = $this->chance(0.92) ? $this->formatFiscalPeriod($entryDate) : null;

			$currency = $this->chance(0.95) ? (string) SC::DEF_SITE_CURRENCY_ID : $this->randomCurrency();
			$exchangeRate = $currency === (string) SC::DEF_SITE_CURRENCY_ID
				? '1.000000'
				: $this->randomDecimalString(0, 7, 6);

			// keep totals consistent (most of the time equal debits/credits)
			$totalBase = $this->chance(0.96) ? $this->randomDecimalString(0, 2500000, 6) : '0.000000';
			$totalDebit = $totalBase;
			$totalCredit = $totalBase;

			// Occasionally create an "empty" entry but with non-null JSON lists (consistent: totals = 0)
			$itemsJson = null;
			$transactionsJson = null;
			if ($this->chance(0.12)) {
				$totalDebit = '0.000000';
				$totalCredit = '0.000000';
				$itemsJson = [];
				$transactionsJson = [];
			}

			$author = $this->pickId($userIds, 0.10);
			$reviewer = $this->pickId($reviewerIds, 0.18);

			$acceptedAt = null;
			$rejectedAt = null;
			$rejectedReason = null;

			$st = $status->value;

			if (in_array($st, ['accept', 'completed', 'active'], true) && $this->chance(0.92)) {
				$acceptedAt = $entryDate->addDays(random_int(0, 10))
					->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					->toDateTimeString();
				if (!$reviewer && $reviewerIds)
					$reviewer = $this->pickId($reviewerIds, 0.0);
			} elseif (in_array($st, ['decline', 'cancelled', 'expired'], true) && $this->chance(0.85)) {
				$rejectedAt = $entryDate->addDays(random_int(0, 10))
					->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					->toDateTimeString();
				$rejectedReason = $this->chance(0.90) ? $this->randomRejectionReason() : null;
				if (!$reviewer && $reviewerIds)
					$reviewer = $this->pickId($reviewerIds, 0.0);
			} elseif ($this->chance(0.08)) {
				$acceptedAt = $entryDate->addDays(random_int(0, 5))
					->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					->toDateTimeString();
			} elseif ($this->chance(0.06)) {
				$rejectedAt = $entryDate->addDays(random_int(0, 5))
					->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					->toDateTimeString();
				$rejectedReason = $this->chance(0.85) ? $this->randomRejectionReason() : null;
			}

			$code = null;
			if ($this->chance(0.96)) {
				$attempts = 0;
				$exists = false;

				do {
					$attempts++;
					$code = $this->makeJournalCode();
					$exists = $this->existsByColumn($table, 'code', $code);
				} while ($exists && $attempts < $attemptCap);

				if ($exists) {
					$code = null;
					$this->out->writeln("<comment>[JournalEntrySeeder]</comment> Code uniqueness attempts exhausted at i={$i}.");
				}
			}

			$entryName = $this->chance(0.92) ? $this->randomEntryName($mode) : null;
			$reference = $this->chance(0.92)
				? ('REF-' . strtoupper(Str::random(4)) . '-' . random_int(100000, 999999))
				: null;

			$company = $this->pickId($companyIds, 0.0);

			$branch = $this->pickId($branchIds, 0.70);
			$department = $this->pickId($departmentIds, 0.72);
			$project = $this->pickId($projectIds, 0.78);

			$docJournalId = $this->pickId($docIds, 0.20);
			$document = $this->chance(0.72) ? $docJournalId : $this->pickId($docIds, 0.75);

			$invId = null;
			$blId = null;
			$odId = null;
			$trsId = null;
			$payId = null;
			$pslpId = null;
			$expId = null;
			$posId = null;
			$posPayId = null;
			$crNoteId = null;
			$dbNoteId = null;
			$lnId = null;
			$alwId = null;

			if ($mode === 'invoice') {
				$invId = $this->pickId($invoiceIds, 0.08);
				$payId = $this->pickId($paymentIds, 0.22);
			} elseif ($mode === 'bill') {
				$blId = $this->pickId($billIds, 0.08);
				$payId = $this->pickId($paymentIds, 0.30);
			} elseif ($mode === 'pos') {
				$posPick = $this->pickPosPaymentPair($posIds, $posPaymentPairs, 0.12, $attemptCap);
				$posId = $posPick['pos_id'];
				$posPayId = $posPick['pos_payment_id'];
			} else {
				if ($this->chance(0.62)) $odId = $this->pickId($orderIds, 0.14);
				if ($this->chance(0.60)) $trsId = $this->pickId($transactionIds, 0.18);
				if ($this->chance(0.56)) $payId = $this->pickId($paymentIds, 0.22);
				if ($this->chance(0.34)) $expId = $this->pickId($expenseIds, 0.35);
			}

			if ($this->chance(0.20)) $pslpId = $this->pickId($payslipIds, 0.55);
			if ($this->chance(0.12)) $crNoteId = $this->pickId($creditNoteIds, 0.55);
			if ($this->chance(0.10)) $dbNoteId = $this->pickId($debitNoteIds, 0.60);
			if ($this->chance(0.12)) $lnId = $this->pickId($loanIds, 0.60);
			if ($this->chance(0.12)) $alwId = $this->pickId($allowanceIds, 0.60);

			$revenue = $this->pickId($revenueIds, 0.90);
			$contract = $this->pickId($contractIds, 0.92);
			$deal = $this->pickId($dealIds, 0.92);

			$isReversal = false;
			$reversingId = null;
			$reversedId = null;

			// conservative reversal: only point to a previously-created entry
			if ($createdIds && $this->chance(0.08)) {
				$isReversal = true;
				$reversedId = (string) $createdIds[array_rand($createdIds)];
			}

			$ecdTransmitted = $this->chance(0.18);
			$ecdAt = $ecdTransmitted
				? $entryDate->addDays(random_int(0, 40))->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))->toDateTimeString()
				: null;

			$hashEcd = $ecdTransmitted && $this->chance(0.92)
				? hash('sha256', (string) Str::uuid() . '|' . (string) $code . '|' . (string) $entryDate->toDateString())
				: null;

			$nire = $this->chance(0.22) ? (string) random_int(10000000, 99999999) : null;

			$nfe = $this->seedNfePayload($table, $nfeAttemptCap);

			$orgUserId = $this->chance(0.10) ? $this->pickId($userIds, 0.0) : null;
			$ip = $this->chance(0.90) ? $this->randomIpAddress() : null;
			$ua = $this->chance(0.88) ? $this->randomUserAgent() : null;

			$attachments = $this->chance(0.88) ? [
				['type' => 'url', 'label' => 'Reference', 'url' => 'https://example.test/journal/' . Str::uuid()->toString()],
				['type' => 'note', 'value' => 'seeded_attachment'],
			] : null;

			$tags = $this->chance(0.90) ? $this->randomTags() : null;

			$metadata = $this->chance(0.92) ? [
				'seed' => true,
				'variant' => 'journal_entry',
				'distribution' => [
					'status' => $status->value,
					'payment_type' => $payType->value,
					'book_type' => $bookType->value,
					'mode' => $mode,
				],
				'ref' => $reference,
			] : null;

			$taxesPayload = null;
			if ($this->chance(0.82)) {
				$chosenTaxIds = $this->pickMany($taxIds, random_int(0, 3));
				$taxesPayload = [
					'tax_ids' => $chosenTaxIds,
					'lines' => array_map(fn($tid) => [
						'tax_id' => $tid,
						'base' => $totalBase ?: $this->randomDecimalString(0, 200000, 6),
					], $chosenTaxIds),
				];
			}

			$createdBy = $this->pickId($userIds, 0.10);
			$updatedBy = $this->chance(0.70) ? ($createdBy ?: $this->pickId($userIds, 0.12)) : null;

			$payload = [
				'code' => $code,
				'name' => $entryName,
				'reference' => $reference,
				'date' => $entryDate->toDateString(),
				AC::COL_PST_DT => $postingDate,
				PJC::COL_RVS_DT => $reversalDate,
				'period' => $period,

				'author' => $author,
				'reviewer' => $reviewer,
				BC::COL_ACC_AT => $acceptedAt,
				BC::COL_REJ_AT => $rejectedAt,
				BC::COL_REJ_RS => $rejectedReason,

				'status' => $status->value,
				BC::COL_PAY_TP => $this->chance(0.95) ? $payType->value : null,

				BC::COL_TTL_DBT => $totalDebit,
				BC::COL_TTL_CRT => $totalCredit,
				'currency' => $currency,
				BC::COL_EXC_RT => $exchangeRate,

				'description' => $this->chance(0.92) ? $this->randomDescription($mode) : null,
				'memo' => $this->chance(0.88) ? $this->randomMemo() : null,
				'notes' => $this->chance(0.88) ? $this->randomNotes() : null,

				'company' => $company,
				'branch' => $branch,
				'department' => $department,
				'project' => $project,
				'document' => $document,

				BC::COL_INV_ID => $invId,
				BC::COL_BL_ID => $blId,
				BC::COL_OD_ID => $odId,
				BC::COL_TRS_ID => $trsId,
				BC::COL_PAY_ID => $payId,
				BC::COL_PSLP_ID => $pslpId,
				BC::COL_EXP_ID => $expId,
				BC::COL_POS_ID => $posId,
				BC::COL_POS_PAY_ID => $posPayId,
				BC::COL_CRD_NT_ID => $crNoteId,
				BC::COL_DBT_NT_ID => $dbNoteId,
				BC::COL_LN_ID => $lnId,
				BC::COL_ALW_ID => $alwId,

				'revenue' => $revenue,
				'contract' => $contract,
				'deal' => $deal,

				PJC::COL_JRN_ID => $docJournalId,

				BC::COL_IS_RVS => $isReversal,
				BC::COL_RVSING_ID => $reversingId,
				BC::COL_RVSED_ID => $reversedId,

				BC::COL_BK_TP => $bookType->value,
				'nire' => $nire,
				BC::COL_ECD_TRS => $ecdTransmitted,
				BC::COL_ECD_AT => $ecdAt,
				BC::COL_HSH_ECD => $hashEcd,

				BC::COL_NFE_KEY => $nfe[BC::COL_NFE_KEY] ?? null,
				BC::COL_NFE_NUMBER => $nfe[BC::COL_NFE_NUMBER] ?? null,
				BC::COL_NFE_SERIES => $nfe[BC::COL_NFE_SERIES] ?? null,
				BC::COL_NFE_XML_PATH => $nfe[BC::COL_NFE_XML_PATH] ?? null,
				BC::COL_NFE_PROTOCOL => $nfe[BC::COL_NFE_PROTOCOL] ?? null,
				BC::COL_NFE_AUTH_AT => $nfe[BC::COL_NFE_AUTH_AT] ?? null,

				BC::COL_ORG_USER_ID => $orgUserId,
				BC::COL_IP_ADR => $ip,
				BC::COL_USR_AGT => $ua,

				'attachments' => $attachments,
				'tags' => $tags,
				'metadata' => $metadata,
				'taxes' => $taxesPayload,
				'items' => $itemsJson,
				'transactions' => $transactionsJson,

				DC::COL_TABLE_CREATOR => $createdBy,
				DC::COL_TABLE_UPDATER => $updatedBy,
			];

			$this->out->writeln(
				"[JE " . ($i + 1) . "/{$total}] status={$status->value} pay=" . ($payload[BC::COL_PAY_TP] ?? 'null')
					. " book={$bookType->value} mode={$mode} dbt={$totalDebit} crt={$totalCredit} company=" . ($company ?: 'null')
					. " nfe=" . (($payload[BC::COL_NFE_KEY] ?? null) ? 'yes' : 'no')
			);

			try {
				$entry = new JournalEntry();
				$entry->forceFill($payload);
				$entry->save();

				$id = (string) $entry->getKey();
				if (trim($id) !== '')
					$createdIds[] = $id;
			} catch (\Throwable $e) {
				Log::error(self::class . ' failed saving JournalEntry: ' . $e->getMessage(), [
					'i' => $i,
					'status' => $status->value,
					'payment_type' => $payload[BC::COL_PAY_TP] ?? null,
					'book_type' => $bookType->value,
					'code' => $code,
					'company' => $company,
				]);
			}
		}

		$this->out->writeln("<info>[JournalEntrySeeder]</info> Done. Inserted={$total} (existing={$existing}, cap={$hardCap}).");
	}

	private function resolveUserTypeColumn(): string
	{
		try {
			if (Schema::hasColumn(DC::TABLE_USERS, 'type'))
				return 'type';
			if (Schema::hasColumn(DC::TABLE_USERS, UC::COL_TP))
				return UC::COL_TP;
		} catch (\Throwable) {
			return 'type';
		}
		return 'type';
	}

	private function safeCount(string $table): int
	{
		try {
			$row = DB::selectOne("select count(*) as c from {$table}");
			return (int) ((is_object($row) ? ($row->c ?? 0) : 0) ?? 0);
		} catch (\Throwable $e) {
			Log::warning(self::class . " safeCount failed for {$table}: " . $e->getMessage());
			return 0;
		}
	}

	private function existsByColumn(string $table, string $column, string $value): bool
	{
		if (trim($value) === '')
			return false;

		try {
			$row = DB::selectOne("select 1 as ok from {$table} where {$column} = ? limit 1", [$value]);
			return $row !== null;
		} catch (\Throwable $e) {
			Log::debug(self::class . " existsByColumn failed: {$table}.{$column}: " . $e->getMessage(), [
				'value' => $value,
			]);
			return false;
		}
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

	private function fetchRows(string $table, array $cols): array
	{
		if (!Schema::hasTable($table))
			return [];

		$colsSql = implode(', ', array_map(fn($c) => trim((string) $c), $cols));
		try {
			return DB::select("select {$colsSql} from {$table} limit 20000");
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchRows failed for {$table}: " . $e->getMessage(), ['cols' => $cols]);
			return [];
		}
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

	private function pickMany(array $ids, int $count): array
	{
		if (!$ids || $count <= 0)
			return [];

		$count = min($count, count($ids));
		$keys = array_rand($ids, $count);

		if (!is_array($keys))
			return [(string) $ids[$keys]];

		$out = [];
		foreach ($keys as $k)
			$out[] = (string) $ids[$k];

		return array_values($out);
	}

	private function pickPosPaymentPair(array $posIds, array $posPaymentPairs, float $nullChance, int $attemptCap): array
	{
		if ($this->chance($nullChance))
			return ['pos_id' => null, 'pos_payment_id' => null];

		if (!$posIds || !$posPaymentPairs)
			return ['pos_id' => $this->pickId($posIds, 0.0), 'pos_payment_id' => null];

		$attempts = 0;
		while ($attempts < $attemptCap) {
			$attempts++;

			$pair = $posPaymentPairs[array_rand($posPaymentPairs)];
			$payId = (string) ($pair->id ?? '');
			$posId = (string) ($pair->{BC::COL_POS_ID} ?? '');

			if ($payId !== '' && $posId !== '')
				return ['pos_id' => $posId, 'pos_payment_id' => $payId];
		}

		$this->out->writeln("<comment>[JournalEntrySeeder]</comment> POS pair attempts exhausted; falling back to pos_id only.");
		return ['pos_id' => $this->pickId($posIds, 0.0), 'pos_payment_id' => null];
	}

	private function randomDecimalString(int $minWhole, int $maxWhole, int $scale): string
	{
		$whole = random_int($minWhole, $maxWhole);
		$fracMax = (int) pow(10, max(0, $scale)) - 1;
		$frac = random_int(0, max(0, $fracMax));
		return (string) $whole . '.' . str_pad((string) $frac, $scale, '0', STR_PAD_LEFT);
	}

	private function makeJournalCode(): string
	{
		return 'JE-' . CarbonImmutable::now()->format('YmdHis') . '-' . Str::uuid()->toString();
	}

	private function formatFiscalPeriod(CarbonImmutable $date): string
	{
		$y = (int) $date->format('Y');
		$q = (int) ceil(((int) $date->format('n')) / 3);
		return "{$y}-Q{$q}";
	}

	private function seedNfePayload(string $journalTable, int $attemptCap): array
	{
		if (!$this->chance(0.18))
			return [];

		$attempts = 0;
		$key = null;
		$exists = false;

		do {
			$attempts++;
			$key = $this->makeNfeKey();
			$exists = $this->existsByColumn($journalTable, BC::COL_NFE_KEY, $key);
		} while ($exists && $attempts < $attemptCap);

		if ($exists) {
			$this->out->writeln("<comment>[JournalEntrySeeder]</comment> NF-e key uniqueness attempts exhausted; leaving NF-e empty.");
			return [];
		}

		$number = (string) random_int(10000, 9999999);
		$series = (string) random_int(1, 999);
		$protocol = (string) random_int(100000000000000, 999999999999999);

		return [
			BC::COL_NFE_KEY => $key,
			BC::COL_NFE_NUMBER => $number,
			BC::COL_NFE_SERIES => $series,
			BC::COL_NFE_XML_PATH => '/storage/nfe/xml/' . $key . '.xml',
			BC::COL_NFE_PROTOCOL => $protocol,
			BC::COL_NFE_AUTH_AT => CarbonImmutable::now()->subDays(random_int(0, 1200))->toDateTimeString(),
		];
	}

	private function makeNfeKey(): string
	{
		$out = '';
		for ($i = 0; $i < 44; $i++)
			$out .= (string) random_int(0, 9);
		return $out;
	}

	private function randomCurrency(): string
	{
		$all = ['BRL', 'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CNY'];
		return $all[array_rand($all)];
	}

	private function randomIpAddress(): string
	{
		if ($this->chance(0.10)) {
			$parts = [];
			for ($i = 0; $i < 8; $i++)
				$parts[] = dechex(random_int(0, 65535));
			return implode(':', $parts);
		}

		return random_int(1, 223) . '.'
			. random_int(0, 255) . '.'
			. random_int(0, 255) . '.'
			. random_int(1, 254);
	}

	private function randomUserAgent(): string
	{
		$agents = [
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (Android 14; Mobile; rv:121.0) Gecko/121.0 Firefox/121.0',
		];
		$ua = $agents[array_rand($agents)];
		return mb_substr($ua, 0, 512);
	}

	private function randomEntryName(string $mode): string
	{
		return match ($mode) {
			'invoice' => 'Invoice Journal Entry',
			'bill' => 'Bill Journal Entry',
			'pos' => 'POS Journal Entry',
			default => 'General Journal Entry',
		} . ' ' . strtoupper(Str::random(5));
	}

	private function randomDescription(string $mode): string
	{
		return match ($mode) {
			'invoice' => 'Journal entry linked to an invoice workflow.',
			'bill' => 'Journal entry linked to a bill workflow.',
			'pos' => 'Journal entry linked to point-of-sale operations.',
			default => 'Journal entry created for general ledger testing.',
		};
	}

	private function randomMemo(): string
	{
		$m = [
			'Autogenerated memo for test coverage.',
			'Seeded memo: validate status/payment/book distributions.',
			'Memo: ensure most nullable columns are populated.',
		];
		return $m[array_rand($m)];
	}

	private function randomNotes(): string
	{
		$n = [
			'Notes: verify approvals/rejections timestamps are consistent.',
			'Notes: check foreign keys are nullable and resilient.',
			'Notes: confirm JSON payloads are well-formed.',
		];
		return $n[array_rand($n)];
	}

	private function randomRejectionReason(): string
	{
		$r = [
			'Insufficient supporting documentation.',
			'Policy validation failed during review.',
			'Rejected due to inconsistent reference linkage.',
			'Rejected by reviewer during QA checks.',
		];
		return $r[array_rand($r)];
	}

	private function randomTags(): array
	{
		$all = ['seeded', 'journal', 'finance', 'testing', 'qa', 'review', 'auto'];
		$k = random_int(1, 4);
		$out = [];
		for ($i = 0; $i < $k; $i++)
			$out[] = $all[array_rand($all)];
		$out = array_values(array_unique($out));
		return $out ?: ['seeded'];
	}
}
