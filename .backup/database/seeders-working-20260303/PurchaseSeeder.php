<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\PurchaseStatus;
use App\Helpers\ErrorHandler;
use App\Models\Purchase;
use App\Traits\HasProductSecurityCoverage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class PurchaseSeeder extends Seeder
{
	private ConsoleOutput $out;

	/**
	 * Error aggregator (deduplicated by ErrorHandler).
	 * Keep bounded to avoid memory ballooning in pathological runs.
	 *
	 * @var array<int, array{message:string,context?:array}>
	 */
	private array $errors = [];

	/** @var array<string, bool> */
	private array $columnCache = [];

	/**
	 * Financial/critical module: 600s default.
	 * If you reuse this pattern for non-financial modules, change to 300.
	 */
	private const SECONDS_LIMIT = 600;

	/**
	 * Overall attempt hard-cap: 512 * [1..6].
	 * Purchases are relationally dense; use 512*6 by default.
	 */
	private const HARD_CAP_ATTEMPTS = 512 * 6; // 3072

	/**
	 * Final target should be a power of 2. 2048 is 2^11.
	 * If you need fewer rows, reduce this and it will still remain a power of 2 if set accordingly.
	 */
	private const HARD_CAP_ROWS = 2048;

	/**
	 * Per-unique-field attempts.
	 */
	private const UNIQUE_ATTEMPTS = 40;

	/**
	 * Scenario reference picking attempts.
	 */
	private const REF_PICK_ATTEMPTS = 12;

	/**
	 * Raw-id prefetch cap per table (keep memory stable).
	 */
	private const PREFETCH_LIMIT = 4096;

	/**
	 * Keep percentages for nullable/optional relations and fields.
	 */
	private const KEEP_PCT_HIGH = 95;
	private const KEEP_PCT_MED  = 88;
	private const KEEP_PCT_LOW  = 80;

	/**
	 * Optional payload percentages.
	 */
	private const NFE_PCT = 12;
	private const WRT_PCT = 18;
	private const INS_PCT = 12;
	private const EXT_PCT = 9;

	/**
	 * If your Purchase model (or traits) has event recursion (e.g., saving() calling save() again),
	 * disable events during seeding and explicitly set UUID PK if present.
	 */
	private const DISABLE_MODEL_EVENTS = true;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		// Ensure query log won't accumulate in memory if enabled somewhere else.
		try {
			DB::disableQueryLog();
		} catch (\Throwable) {
		}
		Purchase::primePurchaseIdCache(chunkSize: 5000, maxRows: 200000);
		$clockStart = microtime(true);
		$seedRun = now()->format('YmdHis') . '-' . (string) Str::uuid();

		$purchasesTable = DC::TABLE_PURCHASES;

		if (!Schema::hasTable($purchasesTable)) {
			$this->out->writeln('<comment>[PurchasesSeeder]</comment> missing table: ' . $purchasesTable);
			return;
		}

		$target = $this->normalizeTargetPow2(self::HARD_CAP_ROWS, 64, self::HARD_CAP_ROWS);

		$this->out->writeln(
			"<info>[PurchasesSeeder] run={$seedRun} target={$target} time_limit=" . self::SECONDS_LIMIT .
				"s hard_cap_attempts=" . self::HARD_CAP_ATTEMPTS . "</info>"
		);

		// ---- Table names
		$usersTable     = DC::TABLE_USERS;
		$vendorsTable   = DC::TABLE_VENDORS;
		$customersTable = DC::TABLE_CUSTOMERS;

		$ordersTable = DC::TABLE_ORDERS;
		$billsTable  = DC::TABLE_BILLS;
		$invsTable   = DC::TABLE_INVS;

		$catsA = DC::TABLE_PROD_SERV_CATS;
		$catsB = DC::TABLE_PRD_CAT;
		$psA   = DC::TABLE_PROD_SERVS;
		$psB   = DC::TABLE_PRODUCTS;

		$whsTable   = DC::TABLE_WHS;
		$taxesTable = DC::TABLE_TAXES;

		// ---- Candidate pools (raw reads only)
		$vendorIds     = $this->pluckVendorIds($vendorsTable, $usersTable, self::PREFETCH_LIMIT);
		$customerIds   = $this->pluckCustomerIds($customersTable, $usersTable, self::PREFETCH_LIMIT);

		$orderIds      = $this->pluckIds($ordersTable, self::PREFETCH_LIMIT);
		$billIds       = $this->pluckIds($billsTable,  self::PREFETCH_LIMIT);
		$invIds        = $this->pluckIds($invsTable,   self::PREFETCH_LIMIT);

		$warehouseIds  = $this->pluckIds($whsTable,   self::PREFETCH_LIMIT);
		$taxIds        = $this->pluckIds($taxesTable, self::PREFETCH_LIMIT);

		$categoryAIds  = $this->pluckIds($catsA, self::PREFETCH_LIMIT);
		$categoryBIds  = $this->pluckIds($catsB, self::PREFETCH_LIMIT);
		$psAIds        = $this->pluckIds($psA,   self::PREFETCH_LIMIT);
		$psBIds        = $this->pluckIds($psB,   self::PREFETCH_LIMIT);

		if (!$vendorIds) {
			$this->out->writeln("<error>[PurchasesSeeder] No vendor candidates found. Aborting.</error>");
			return;
		}

		$payStatusCol = $this->resolvePayStatusColumn();
		$authorizedPayStatuses = $this->resolveAuthorizedPaymentValues();

		$paidOrderIds = $this->pluckIdsWhereInLower($ordersTable, $payStatusCol, $authorizedPayStatuses, self::PREFETCH_LIMIT);
		$paidBillIds  = $this->pluckIdsWhereInLower($billsTable,  $payStatusCol, $authorizedPayStatuses, self::PREFETCH_LIMIT);
		$paidInvIds   = $this->pluckIdsWhereInLower($invsTable,   $payStatusCol, $authorizedPayStatuses, self::PREFETCH_LIMIT);

		$existingMaxNb = $this->selectMaxInt($purchasesTable, BC::COL_PRC_NB);

		// ---- Controlled enumerations / distributions
		$statuses = PurchaseStatus::cases();

		$scenarios = ['invoice', 'bill', 'order', 'none'];

		$sources = [
			'shopify',
			'woocommerce',
			'shopee',
			'magento',
			'mercado_livre',
			'amazon',
			'aliexpress',
			'physical_store',
			'b2b_portal',
			'whatsapp_order',
			'email_order',
			'phone_order',
			'internal_request',
			'casas_bahia',
			'americanas',
			'fast_shop',
			'custom_website',
			'other',
		];

		// Ensure minimum coverage: each status and each scenario appears at least once early in the run.
		$plan = $this->buildCoveragePlan($statuses, $scenarios);

		// Track inserted rows from this run for optional trimming to power-of-two if an early stop occurs.
		$insertedPrimaryKeys = [];

		$created = 0;
		$attempts = 0;
		$consecutiveFailures = 0;

		while ($created < $target) {
			$attempts++;

			// ---- Global guards (hard caps)
			if ($attempts > self::HARD_CAP_ATTEMPTS) {
				$this->out->writeln('<error>[PurchasesSeeder]</error> hard-cap attempts reached, stopping to prevent infinite loop');
				break;
			}
			if ((microtime(true) - $clockStart) > self::SECONDS_LIMIT) {
				$this->out->writeln('<error>[PurchasesSeeder]</error> time limit reached, stopping early');
				break;
			}
			if ($consecutiveFailures > 256) {
				$this->out->writeln('<error>[PurchasesSeeder]</error> excessive consecutive failures, stopping early');
				break;
			}

			// Use a stable index that still changes even if some iterations fail.
			$i = $attempts - 1;

			try {
				[$status, $scenario] = $this->resolvePlanItem($plan, $statuses, $scenarios, $created);

				// Draft-like statuses must not link to finalized documents.
				if ($this->isDraftLike($status)) {
					$scenario = 'none';
				}

				$purchaseNumber = (int) $existingMaxNb + $created + 1;

				// ---- Pick mandatory and optional FKs (do not null-filter; we decide explicitly)
				$vendorId = $this->pickOne($vendorIds, $i);
				if (!is_string($vendorId) || trim($vendorId) === '') {
					$consecutiveFailures++;
					$this->out->writeln("<error>[PurchasesSeeder] invalid vendor_id picked, skipping i={$i}</error>");
					continue;
				}

				$customerId = $customerIds
					? $this->tolerant($this->pickOne($customerIds, $i + 17), self::KEEP_PCT_MED)
					: null;

				$categoryId = ($categoryAIds || $categoryBIds)
					? ($categoryAIds ? $this->pickOne($categoryAIds, $i + 31) : $this->pickOne($categoryBIds, $i + 31))
					: null;
				$categoryId = $this->tolerant($categoryId, self::KEEP_PCT_MED);

				$productServiceId = ($psAIds || $psBIds)
					? ($psAIds ? $this->pickOne($psAIds, $i + 47) : $this->pickOne($psBIds, $i + 47))
					: null;
				$productServiceId = $this->tolerant($productServiceId, self::KEEP_PCT_LOW);

				$warehouseId = $warehouseIds ? $this->tolerant($this->pickOne($warehouseIds, $i + 59), self::KEEP_PCT_MED) : null;
				$taxId       = $taxIds       ? $this->tolerant($this->pickOne($taxIds,       $i + 83), self::KEEP_PCT_MED) : null;

				// ---- Scenario references (bounded attempts)
				$orderId = null;
				$billId = null;
				$invoiceId = null;

				$this->pickScenarioReferences(
					$scenario,
					$status,
					$i,
					$orderIds,
					$billIds,
					$invIds,
					$paidOrderIds,
					$paidBillIds,
					$paidInvIds,
					$orderId,
					$billId,
					$invoiceId
				);

				// Invoice wins over bill.
				if ($invoiceId !== null) {
					$billId = null;
				}

				// ---- Dates
				$purchaseDate = now()->subDays(($i % 120))
					->setTime((int) ($i % 24), (int) (($i * 7) % 60), 0);

				$sendDate = null;
				if ($this->isDraftLike($status)) {
					$sendDate = now()->addDays(1 + ($i % 30))->setTime(9, 0, 0);
					$orderId = null;
					$billId = null;
					$invoiceId = null;
				} elseif ($this->shouldKeep(self::KEEP_PCT_LOW)) {
					$sendDate = $purchaseDate->copy()->addDays(1 + ($i % 12))->setTime(10, 0, 0);
				}

				// ---- Unique fields (bounded do/while exists-check)
				$purchaseId = $this->uniqueString(
					$purchasesTable,
					BC::COL_PRC_ID,
					fn(): string => 'PRCH-' . (string) Str::uuid() . '-' . now()->format('YmdHis'),
					self::UNIQUE_ATTEMPTS
				);

				if ($purchaseId === null) {
					$consecutiveFailures++;
					$this->out->writeln("<error>[PurchasesSeeder] unique purchase_id failed attempts=" . self::UNIQUE_ATTEMPTS . " i={$i}</error>");
					continue;
				}

				$statusLabel = $status->value;
				$statusIndex = method_exists(PurchaseStatus::class, 'getIndex')
					? (int) PurchaseStatus::getIndex($statusLabel)
					: 0;

				$discountApply   = ($i % 2) ? 1 : 0;
				$shippingDisplay = ($i % 2) ? 0 : 1;

				$svcFee = ($i % 5 === 0) ? (float) (($i % 99) + 1) : 0.0;
				$svcFee = $this->tolerant($svcFee, self::KEEP_PCT_MED);

				$source = $this->tolerant($sources[$i % max(1, count($sources))], self::KEEP_PCT_HIGH);
				$notes  = ($i % 7 === 0)
					? "seeded purchase {$i}"
					: ($this->shouldKeep(self::KEEP_PCT_MED) ? fake()->paragraph() : null);

				// Nullable columns: keep keys with null values (no null-filtering).
				$shipping = $this->makeShippingPayload($i, $shippingDisplay);
				$nfe      = $this->makeNfePayload($purchasesTable, $i);

				// taxes: list-like json field; keep array shape stable (values + unique) using Collection.
				$taxes = null;
				if ($taxIds && $this->shouldKeep(self::KEEP_PCT_MED)) {
					$maxPick = 1 + ($i % 3);
					$picked = [];
					for ($k = 0; $k < $maxPick; $k++) {
						$tid = $this->pickOne($taxIds, $i + 200 + $k);
						if (is_string($tid) && trim($tid) !== '') {
							$picked[] = $tid;
						}
					}
					$picked = collect($picked)->unique()->values()->all();
					$taxes = $picked ?: null;
				}

				$metadata = $this->tolerant([
					'seed' => [
						'run'      => $seedRun,
						'attempt'  => $attempts,
						'created'  => $created,
						'scenario' => $scenario,
					],
					'links' => [
						'order_id'   => $orderId,
						'bill_id'    => $billId,
						'invoice_id' => $invoiceId,
					],
					'flags' => [
						'shipping_display' => $shippingDisplay,
						'discount_apply'   => $discountApply,
					],
				], self::KEEP_PCT_MED);

				$delivery = $this->tolerant([
					'method' => ($i % 4 === 0) ? 'courier' : 'pickup',
					'window' => ($i % 4 === 0) ? ['from' => '09:00', 'to' => '18:00'] : null,
					'notes'  => ($i % 9 === 0) ? 'Leave at reception' : null,
				], self::KEEP_PCT_MED);

				$attachments = $this->tolerant([
					'docs' => [
						['name' => 'purchase.pdf', 'path' => '/uploads/purchases/' . $purchaseId . '.pdf'],
					],
					'images' => ($i % 6 === 0)
						? [
							['name' => 'photo.jpg', 'path' => '/uploads/purchases/' . $purchaseId . '/photo.jpg'],
						]
						: [],
				], self::KEEP_PCT_LOW);

				$coverage = $this->makeProductSecurityCoveragePayload($i);

				// ---- Required: output right before saving/creating with brief detail
				$this->out->writeln(
					"<comment>[PurchasesSeeder] saving created={$created} attempt={$attempts} prc_id={$purchaseId}" .
						" nb={$purchaseNumber} stt={$statusLabel} scenario={$scenario} vd={$vendorId}" .
						" od=" . ($orderId ?? '-') . " bl=" . ($billId ?? '-') . " inv=" . ($invoiceId ?? '-') . "</comment>"
				);

				// ---- Persist via Model (avoid raw inserts to preserve casts/booted logic)
				$m = new Purchase();

				// If events are disabled, ensure UUID PK if present.
				if ($this->hasColumnCached($purchasesTable, 'id')) {
					$m->setAttribute('id', (string) Str::uuid());
				}

				// Core identifiers
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_PRC_ID, $purchaseId);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_PRC_NB, $purchaseNumber);

				// Fees / misc
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_SVC_FEE, $svcFee);
				$this->setIfColumnExists($m, $purchasesTable, 'source', $source);
				$this->setIfColumnExists($m, $purchasesTable, 'notes', $notes);

				// Relations
				$this->setIfColumnExists($m, $purchasesTable, UC::COL_VD_ID, $vendorId);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_CST_ID, $customerId);

				$this->setIfColumnExists($m, $purchasesTable, BC::COL_CAT_ID, $categoryId);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_PRD_SV_ID, $productServiceId);

				$this->setIfColumnExists($m, $purchasesTable, BC::COL_WRH_ID, $warehouseId);

				$this->setIfColumnExists($m, $purchasesTable, BC::COL_OD_ID, $orderId);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_BL_ID, $billId);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_INV_ID, $invoiceId);

				$this->setIfColumnExists($m, $purchasesTable, BC::COL_TAX_ID, $taxId);

				// Status
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_STT_LB, $statusLabel);
				$this->setIfColumnExists($m, $purchasesTable, 'status', $statusIndex);

				// Dates
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_PRC_DT, $purchaseDate);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_SD_DT, $sendDate);

				// Flags
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_DSC_APL, $discountApply);
				$this->setIfColumnExists($m, $purchasesTable, BC::COL_SHIP_DSP, $shippingDisplay);

				// Shipping (nullable columns: keep nulls; no array_filter)
				foreach ($shipping as $col => $val) {
					$this->setIfColumnExists($m, $purchasesTable, $col, $val);
				}

				// NFE (nullable columns: keep nulls; no array_filter)
				foreach ($nfe as $col => $val) {
					$this->setIfColumnExists($m, $purchasesTable, $col, $val);
				}

				// JSON-ish payloads (rely on model casts; do not raw insert)
				$this->setIfColumnExists($m, $purchasesTable, 'metadata', $metadata);
				$this->setIfColumnExists($m, $purchasesTable, 'delivery', $delivery);
				$this->setIfColumnExists($m, $purchasesTable, 'attachments', $attachments);
				$this->setIfColumnExists($m, $purchasesTable, 'taxes', $taxes);

				// Coverage (optional columns: set only if exist)
				foreach ($coverage as $col => $val) {
					$this->setIfColumnExists($m, $purchasesTable, $col, $val);
				}

				try {
					if (self::DISABLE_MODEL_EVENTS) {
						// Purchase::withoutEvents(function () use ($m): void {
						$m->save();
						// });
					} else {
						$m->save();
					}

					$created++;
					$consecutiveFailures = 0;

					// Track PK for optional trimming
					$pk = $m->getKey();
					if (is_string($pk) && $pk !== '') {
						$insertedPrimaryKeys[] = $pk;
					} elseif (is_string($purchaseId) && $purchaseId !== '') {
						$insertedPrimaryKeys[] = $purchaseId;
					}

					// Bound PK list to avoid memory growth if something external goes wrong.
					if (count($insertedPrimaryKeys) > (self::HARD_CAP_ROWS + 128)) {
						$insertedPrimaryKeys = array_slice($insertedPrimaryKeys, -self::HARD_CAP_ROWS);
					}
				} catch (\Throwable $e) {
					$consecutiveFailures++;

					$this->boundedErrorLog([
						'message' => 'PurchasesSeeder failed saving purchase',
						'context' => [
							'attempt'     => $attempts,
							'created'     => $created,
							'purchase_id' => $purchaseId,
							'msg'         => $e->getMessage(),
							'file'        => $e->getFile(),
							'line'        => $e->getLine(),
						],
					], seed: '[PurchasesSeeder] save');

					continue;
				}
			} catch (\Throwable $e) {
				$consecutiveFailures++;

				$this->boundedErrorLog([
					'message' => 'PurchasesSeeder iteration failed',
					'context' => [
						'attempt' => $attempts,
						'created' => $created,
						'msg'     => $e->getMessage(),
						'file'    => $e->getFile(),
						'line'    => $e->getLine(),
					],
				], seed: '[PurchasesSeeder] iteration');

				continue;
			}
		}

		// ---- Ensure final count is a power of 2 (trim down if early stop)
		if ($created > 1 && !$this->isPowerOfTwo($created)) {
			$targetPow2 = $this->highestPowerOfTwo($created);
			$toDelete = $created - $targetPow2;

			if ($toDelete > 0) {
				$this->out->writeln("<comment>[PurchasesSeeder] trimming: created={$created} -> {$targetPow2} (delete {$toDelete})</comment>");

				// Best-effort delete using known PKs (fast path). Chunk to avoid large IN clauses.
				$this->deleteSeededRowsByPrimaryKeys($purchasesTable, $insertedPrimaryKeys, $toDelete);

				$created = $targetPow2;
			}
		}

		$elapsed = (float) (microtime(true) - $clockStart);
		$memMb = (int) round(memory_get_peak_usage(true) / 1024 / 1024);

		$this->out->writeln(
			"<info>[PurchasesSeeder] created={$created} target={$target} attempts={$attempts}" .
				" elapsed=" . number_format($elapsed, 2) . "s peak_mem={$memMb}MB errors=" . count($this->errors) . "</info>"
		);
	}

	// ---------------------------------------------------------------------
	// Payload builders (NO array_filter to remove nulls; keep keys)
	// ---------------------------------------------------------------------

	private function makeProductSecurityCoveragePayload(int $i): array
	{
		// Prefer trait constant if present; else fallback.
		$cols = null;
		try {
			if (\defined(HasProductSecurityCoverage::class . '::PRODUCT_SECURITY_COLUMNS')) {
				/** @var array $tmp */
				$tmp = constant(HasProductSecurityCoverage::class . '::PRODUCT_SECURITY_COLUMNS');
				if (is_array($tmp) && $tmp) {
					$cols = $tmp;
				}
			}
		} catch (\Throwable) {
		}

		if (!$cols) {
			$cols = [
				BC::COL_HAS_WRT,
				BC::COL_WRT_CST,
				BC::COL_WRT_DYS,
				BC::COL_WRT_PLC,
				BC::COL_HAS_INS,
				BC::COL_INS_CST,
				BC::COL_INS_PLC,
				BC::COL_HAS_EXT_SEC,
				BC::COL_EXT_SEC_CST,
			];
		}

		// Keep full shape.
		$out = [];
		foreach ($cols as $c) {
			$out[$c] = null;
		}

		$hasWrt = $this->shouldKeep(self::WRT_PCT);
		$hasIns = $this->shouldKeep(self::INS_PCT);
		$hasExt = $this->shouldKeep(self::EXT_PCT);

		$out[BC::COL_HAS_WRT]     = $hasWrt ? 1 : 0;
		$out[BC::COL_HAS_INS]     = $hasIns ? 1 : 0;
		$out[BC::COL_HAS_EXT_SEC] = $hasExt ? 1 : 0;

		if ($hasWrt) {
			$out[BC::COL_WRT_CST] = $this->tolerant($this->randMoney($i, 0, 2500, 2), self::KEEP_PCT_HIGH);
			$out[BC::COL_WRT_DYS] = $this->tolerant((int) (30 + ($i % 365)), self::KEEP_PCT_HIGH);
			$out[BC::COL_WRT_PLC] = $this->tolerant(fake()->paragraph(), self::KEEP_PCT_MED);
		}

		if ($hasIns) {
			$out[BC::COL_INS_CST] = $this->tolerant($this->randMoney($i + 11, 0, 8000, 2), self::KEEP_PCT_HIGH);
			$out[BC::COL_INS_PLC] = $this->tolerant(fake()->paragraph(), self::KEEP_PCT_MED);
		}

		if ($hasExt) {
			$out[BC::COL_EXT_SEC_CST] = $this->tolerant($this->randMoney($i + 23, 0, 12000, 2), self::KEEP_PCT_HIGH);
		}

		return $out;
	}

	private function makeNfePayload(string $purchasesTable, int $i): array
	{
		// Keep full shape regardless of inclusion.
		$out = [
			BC::COL_NFE_KEY      => null,
			BC::COL_NFE_NUMBER   => null,
			BC::COL_NFE_SERIES   => null,
			BC::COL_NFE_XML_PATH => null,
			BC::COL_NFE_PROTOCOL => null,
			BC::COL_NFE_AUTH_AT  => null,
		];

		if (!$this->shouldKeep(self::NFE_PCT)) {
			return $out;
		}

		$nfeKey = $this->uniqueString(
			$purchasesTable,
			BC::COL_NFE_KEY,
			function (): string {
				$digits = '';
				for ($k = 0; $k < 44; $k++) {
					try {
						$digits .= (string) random_int(0, 9);
					} catch (\Throwable) {
						$digits .= (string) (($k * 7) % 10);
					}
				}
				return $digits;
			},
			self::UNIQUE_ATTEMPTS
		);

		if ($nfeKey === null) {
			return $out;
		}

		$n = (string) (100000 + ($i % 900000));
		$series = (string) (1 + ($i % 9));
		$protocol = (string) (10000000000000000000 + ($i % 9000000));

		$out[BC::COL_NFE_KEY]      = $nfeKey;
		$out[BC::COL_NFE_NUMBER]   = $this->tolerant($n, self::KEEP_PCT_HIGH);
		$out[BC::COL_NFE_SERIES]   = $this->tolerant($series, self::KEEP_PCT_HIGH);
		$out[BC::COL_NFE_XML_PATH] = $this->tolerant('/storage/nfe/' . $nfeKey . '.xml', self::KEEP_PCT_MED);
		$out[BC::COL_NFE_PROTOCOL] = $this->tolerant($protocol, self::KEEP_PCT_MED);
		$out[BC::COL_NFE_AUTH_AT]  = $this->tolerant(now()->subDays(($i % 60)), self::KEEP_PCT_MED);

		return $out;
	}

	private function makeShippingPayload(int $i, int $shippingDisplay): array
	{
		// Keep full shape.
		$out = [
			BC::COL_SHIP_NAME  => null,
			BC::COL_SHIP_CTR   => null,
			BC::COL_SHIP_ZIP   => null,
			BC::COL_SHIP_ADR   => null,
			BC::COL_SHIP_ST    => null,
			BC::COL_SHIP_CTY   => null,
			BC::COL_SHIP_TEL   => null,
			BC::COL_SHIP_EMAIL => null,
			BC::COL_SHIP_DTL   => null,
		];

		if ($shippingDisplay === 0 && !$this->shouldKeep(self::KEEP_PCT_LOW)) {
			return $out;
		}

		$countries = ['BR', 'Brazil', 'US', 'United States', 'PT', 'Portugal'];
		$ctr = $countries[$i % count($countries)];

		$state = match (strtoupper(substr((string) $ctr, 0, 2))) {
			'BR' => ['SP', 'RJ', 'MG', 'ES'][$i % 4],
			'US' => ['CA', 'NY', 'FL', 'TX'][$i % 4],
			'PT' => ['LS', 'PT', 'MD', 'AC'][$i % 4],
			default => 'RJ',
		};

		$zip = null;
		$ctr2 = strtoupper(substr((string) $ctr, 0, 2));
		if ($ctr2 === 'BR') {
			$a = 10000 + ($i % 89999);
			$b = 100 + ($i % 899);
			$zip = str_pad((string) $a, 5, '0', STR_PAD_LEFT) . '-' . str_pad((string) $b, 3, '0', STR_PAD_LEFT);
		} elseif ($ctr2 === 'US') {
			$zip = str_pad((string) (10000 + ($i % 89999)), 5, '0', STR_PAD_LEFT);
		} else {
			$zip = (string) (1000 + ($i % 9000)) . '-' . (string) (100 + ($i % 900));
		}

		$out[BC::COL_SHIP_NAME]  = $this->tolerant(fake()->name(), self::KEEP_PCT_HIGH);
		$out[BC::COL_SHIP_CTR]   = $this->tolerant($ctr, self::KEEP_PCT_HIGH);
		$out[BC::COL_SHIP_ZIP]   = $this->tolerant($zip, self::KEEP_PCT_MED);
		$out[BC::COL_SHIP_ADR]   = $this->tolerant(fake()->streetAddress(), self::KEEP_PCT_HIGH);
		$out[BC::COL_SHIP_ST]    = $this->tolerant($state, self::KEEP_PCT_HIGH);
		$out[BC::COL_SHIP_CTY]   = $this->tolerant(fake()->city(), self::KEEP_PCT_HIGH);
		$out[BC::COL_SHIP_TEL]   = $this->tolerant(fake()->phoneNumber(), self::KEEP_PCT_MED);
		$out[BC::COL_SHIP_EMAIL] = $this->tolerant(fake()->safeEmail(), self::KEEP_PCT_MED);
		$out[BC::COL_SHIP_DTL]   = $this->tolerant(($i % 9 === 0) ? 'Gate code: 1234' : null, self::KEEP_PCT_LOW);

		return $out;
	}

	// ---------------------------------------------------------------------
	// Scenario picking (bounded loops)
	// ---------------------------------------------------------------------

	private function pickScenarioReferences(
		string $scenario,
		PurchaseStatus $status,
		int $i,
		array $orderIds,
		array $billIds,
		array $invIds,
		array $paidOrderIds,
		array $paidBillIds,
		array $paidInvIds,
		?string &$orderId,
		?string &$billId,
		?string &$invoiceId
	): void {
		$orderId = null;
		$billId = null;
		$invoiceId = null;

		$wantPaid = $this->isPaidLike($status);

		for ($attempt = 1; $attempt <= self::REF_PICK_ATTEMPTS; $attempt++) {
			// No verbose loop spam; only the "saving" line is mandated.
			if ($scenario === 'invoice') {
				if ($invIds) {
					$invoiceId = $wantPaid && $paidInvIds
						? $this->pickOne($paidInvIds, $i + $attempt)
						: $this->pickOne($invIds, $i + $attempt);
					return;
				}
				$scenario = 'none';
				continue;
			}

			if ($scenario === 'bill') {
				if ($billIds) {
					$billId = $wantPaid && $paidBillIds
						? $this->pickOne($paidBillIds, $i + $attempt)
						: $this->pickOne($billIds, $i + $attempt);
					return;
				}
				$scenario = 'none';
				continue;
			}

			if ($scenario === 'order') {
				if ($orderIds) {
					$orderId = $wantPaid && $paidOrderIds
						? $this->pickOne($paidOrderIds, $i + $attempt)
						: $this->pickOne($orderIds, $i + $attempt);
					return;
				}
				$scenario = 'none';
				continue;
			}

			// none
			return;
		}
	}

	// ---------------------------------------------------------------------
	// Raw read helpers (READ ONLY; optimized via raw SQL)
	// ---------------------------------------------------------------------

	private function pluckIds(string $table, int $limit): array
	{
		if (!is_string($table) || trim($table) === '') return [];
		if (!Schema::hasTable($table)) return [];

		$limit = max(1, (int) $limit);

		try {
			$rows = DB::select("select id from {$table} limit {$limit}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return collect($out)->unique()->values()->all();
		} catch (\Throwable $e) {
			Log::debug('PurchasesSeeder pluckIds failed', [
				'table' => $table,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function pluckIdsWhereInLower(string $table, string $col, array $values, int $limit): array
	{
		if (!Schema::hasTable($table)) return [];
		if (!Schema::hasColumn($table, $col)) return [];
		if (!$values) return [];

		$limit = max(1, (int) $limit);

		try {
			$placeholders = implode(',', array_fill(0, count($values), '?'));
			$sql = "select id from {$table} where lower(coalesce({$col}, '')) in ({$placeholders}) limit {$limit}";
			$rows = DB::select($sql, $values);

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return collect($out)->unique()->values()->all();
		} catch (\Throwable $e) {
			Log::debug('PurchasesSeeder pluckIdsWhereInLower failed', [
				'table' => $table,
				'col' => $col,
				'msg' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return [];
		}
	}

	private function selectMaxInt(string $table, string $col): int
	{
		if (!Schema::hasTable($table)) return 0;
		if (!Schema::hasColumn($table, $col)) return 0;

		try {
			$row = DB::selectOne("select coalesce(max({$col}), 0) as m from {$table}");
			return (int) ($row?->m ?? 0);
		} catch (\Throwable $e) {
			Log::debug('PurchasesSeeder selectMaxInt failed', [
				'table' => $table,
				'col' => $col,
				'msg' => $e->getMessage(),
			]);
			return 0;
		}
	}

	/**
	 * Raw exists-check (READ ONLY). Avoid Query Builder here per requirements.
	 */
	private function rawExists(string $table, string $col, string $candidate): bool
	{
		if (!Schema::hasTable($table)) return false;
		if (!Schema::hasColumn($table, $col)) return false;

		try {
			$row = DB::selectOne("select 1 as e from {$table} where {$col} = ? limit 1", [$candidate]);
			return $row !== null;
		} catch (\Throwable $e) {
			Log::warning('PurchasesSeeder rawExists failed', [
				'table' => $table,
				'col' => $col,
				'msg' => $e->getMessage(),
			]);
			return false;
		}
	}

	// ---------------------------------------------------------------------
	// Vendor/customer pools
	// ---------------------------------------------------------------------

	private function pluckVendorIds(string $vendorsTable, string $usersTable, int $limit): array
	{
		$ids = $this->pluckIds($vendorsTable, $limit);
		if ($ids) return $ids;

		$types = $this->resolveUserTypeValues(['Vendor', 'Company'], ['vendor', 'company']);
		return $this->pluckUserIdsByTypes($usersTable, $types, $limit);
	}

	private function pluckCustomerIds(string $customersTable, string $usersTable, int $limit): array
	{
		$ids = $this->pluckIds($customersTable, $limit);
		if ($ids) return $ids;

		$types = $this->resolveUserTypeValues(['Client'], ['client']);
		return $this->pluckUserIdsByTypes($usersTable, $types, $limit);
	}

	private function pluckUserIdsByTypes(string $usersTable, array $types, int $limit): array
	{
		if (!Schema::hasTable($usersTable)) return [];

		$typeCol = $this->resolveUsersTypeColumn($usersTable);
		$limit = max(1, (int) $limit);

		try {
			if ($typeCol === null || !$types) {
				return $this->pluckIds($usersTable, $limit);
			}

			$placeholders = implode(',', array_fill(0, count($types), '?'));
			$sql = "select id from {$usersTable} where {$typeCol} in ({$placeholders}) limit {$limit}";
			$rows = DB::select($sql, $types);

			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}

			return collect($out)->unique()->values()->all();
		} catch (\Throwable $e) {
			Log::debug('PurchasesSeeder pluckUserIdsByTypes failed', [
				'table' => $usersTable,
				'msg' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function resolveUsersTypeColumn(string $usersTable): ?string
	{
		$candidates = [];
		try {
			if (\defined(UC::class . '::COL_TP')) $candidates[] = UC::COL_TP;
		} catch (\Throwable) {
		}

		$candidates[] = 'type';
		$candidates[] = 'user_type';

		foreach ($candidates as $c) {
			if (is_string($c) && $c !== '' && Schema::hasColumn($usersTable, $c)) {
				return $c;
			}
		}

		return null;
	}

	private function resolveUserTypeValues(array $enumCases, array $fallback): array
	{
		$out = [];

		$enum = '\\App\\Enums\\UserType';
		if (class_exists($enum)) {
			foreach ($enumCases as $caseName) {
				try {
					$case = constant($enum . '::' . $caseName);
					$v = $case?->value ?? null;
					if (is_string($v) && trim($v) !== '') $out[] = $v;
				} catch (\Throwable) {
				}
			}
		}

		if (!$out) $out = $fallback;

		return collect($out)
			->map(fn($v) => is_string($v) ? strtolower(trim($v)) : null)
			->filter(fn($v) => is_string($v) && $v !== '')
			->unique()
			->values()
			->all();
	}

	private function resolvePayStatusColumn(): string
	{
		try {
			if (\defined(BC::class . '::COL_PAY_STT')) return BC::COL_PAY_STT;
		} catch (\Throwable) {
		}

		return 'pay_status';
	}

	private function resolveAuthorizedPaymentValues(): array
	{
		$values = ['authorized', 'completed'];

		$enum = '\\App\\Enums\\PaymentStatus';
		if (class_exists($enum)) {
			try {
				$a = constant($enum . '::Authorized')?->value ?? null;
				$c = constant($enum . '::Completed')?->value ?? null;
				$tmp = [];
				if (is_string($a) && trim($a) !== '') $tmp[] = $a;
				if (is_string($c) && trim($c) !== '') $tmp[] = $c;
				if ($tmp) $values = $tmp;
			} catch (\Throwable) {
			}
		}

		return collect($values)
			->map(fn($v) => is_string($v) ? strtolower(trim($v)) : null)
			->filter(fn($v) => is_string($v) && $v !== '')
			->unique()
			->values()
			->all();
	}

	// ---------------------------------------------------------------------
	// Uniqueness helpers (bounded do/while)
	// ---------------------------------------------------------------------

	private function uniqueString(string $table, string $col, callable $factory, int $maxAttempts): ?string
	{
		$maxAttempts = max(1, (int) $maxAttempts);

		$attempt = 0;
		do {
			$attempt++;
			if ($attempt > $maxAttempts) {
				return null;
			}

			$candidate = (string) $factory();
			if (trim($candidate) === '') {
				continue;
			}

			$exists = $this->rawExists($table, $col, $candidate);
		} while ($exists);

		return $candidate;
	}

    // ---------------------------------------------------------------------
    // Plan / distribution helpers (minimum coverage)
    // ---------------------------------------------------------------------

	/**
	 * Build a plan that guarantees at least:
	 * - one iteration for each status
	 * - one iteration for each scenario (paired with a non-draft status if possible)
	 */
	private function buildCoveragePlan(array $statuses, array $scenarios): array
	{
		$plan = [];

		// 1) each status at least once (scenario 'none' safe for all)
		foreach ($statuses as $st) {
			if ($st instanceof PurchaseStatus) {
				$plan[] = [$st, 'none'];
			}
		}

		// 2) each scenario at least once with a non-draft status (if available)
		$stableStatus = $this->pickNonDraftStatus($statuses) ?? ($statuses[0] ?? null);
		if ($stableStatus instanceof PurchaseStatus) {
			foreach ($scenarios as $sc) {
				$plan[] = [$stableStatus, (string) $sc];
			}
		}

		// Shape consistency
		return array_values($plan);
	}

	private function resolvePlanItem(array $plan, array $statuses, array $scenarios, int $created): array
	{
		if ($created < count($plan)) {
			/** @var array{0:PurchaseStatus,1:string} $item */
			$item = $plan[$created];
			return [$item[0], $item[1]];
		}

		$status = $statuses[$created % max(1, count($statuses))] ?? ($statuses[0] ?? null);
		$scenario = $scenarios[$created % max(1, count($scenarios))] ?? 'none';

		if (!$status instanceof PurchaseStatus) {
			$status = PurchaseStatus::cases()[0] ?? null;
		}

		return [$status, (string) $scenario];
	}

	private function pickNonDraftStatus(array $statuses): ?PurchaseStatus
	{
		foreach ($statuses as $st) {
			if ($st instanceof PurchaseStatus && !$this->isDraftLike($st)) {
				return $st;
			}
		}
		return null;
	}

	private function isDraftLike(PurchaseStatus $status): bool
	{
		// Prefer explicit cases if present.
		try {
			if (\defined(PurchaseStatus::class . '::Draft') && $status === PurchaseStatus::Draft) return true;
		} catch (\Throwable) {
		}
		try {
			if (\defined(PurchaseStatus::class . '::PreOrder') && $status === PurchaseStatus::PreOrder) return true;
		} catch (\Throwable) {
		}

		$v = strtolower(trim((string) $status->value));
		return in_array($v, ['draft', 'pre_order', 'preorder'], true);
	}

	private function isPaidLike(PurchaseStatus $status): bool
	{
		// Prefer explicit case if present.
		try {
			if (\defined(PurchaseStatus::class . '::Paid') && $status === PurchaseStatus::Paid) return true;
		} catch (\Throwable) {
		}

		$v = strtolower(trim((string) $status->value));
		return in_array($v, ['paid', 'completed', 'authorized'], true);
	}

	// ---------------------------------------------------------------------
	// Column / model assignment helpers
	// ---------------------------------------------------------------------

	private function hasColumnCached(string $table, string $col): bool
	{
		$key = $table . '::' . $col;
		if (array_key_exists($key, $this->columnCache)) {
			return $this->columnCache[$key];
		}

		$ok = false;
		try {
			$ok = Schema::hasTable($table) && Schema::hasColumn($table, $col);
		} catch (\Throwable) {
			$ok = false;
		}

		$this->columnCache[$key] = $ok;
		return $ok;
	}

	private function setIfColumnExists(Purchase $m, string $table, string $col, mixed $val): void
	{
		if (!$this->hasColumnCached($table, $col)) return;
		$m->setAttribute($col, $val);
	}

	// ---------------------------------------------------------------------
	// Misc helpers
	// ---------------------------------------------------------------------

	private function pickOne(array $ids, int $seed): ?string
	{
		$n = count($ids);
		if ($n === 0) return null;

		$idx = $seed % $n;
		return $ids[$idx] ?? ($ids[0] ?? null);
	}

	private function shouldKeep(int $keepPercent): bool
	{
		$keepPercent = max(0, min(100, (int) $keepPercent));
		try {
			return random_int(1, 100) <= $keepPercent;
		} catch (\Throwable $e) {
			Log::debug('PurchasesSeeder shouldKeep random_int failed', [
				'msg' => $e->getMessage(),
			]);
			return true;
		}
	}

	private function tolerant(mixed $value, int $keepPercent): mixed
	{
		if ($value === null) return null;
		return $this->shouldKeep($keepPercent) ? $value : null;
	}

	private function randMoney(int $seed, int $minCents, int $maxCents, int $decimals = 2): float
	{
		$minCents = max(0, $minCents);
		$maxCents = max($minCents, $maxCents);

		try {
			$cents = random_int($minCents, $maxCents);
		} catch (\Throwable) {
			$span = max(1, ($maxCents - $minCents + 1));
			$cents = $minCents + (($seed * 37) % $span);
		}

		$v = $cents / 100.0;
		return (float) number_format($v, $decimals, '.', '');
	}

	private function normalizeTargetPow2(int $raw, int $min, int $max): int
	{
		$min = max(2, (int) $min);
		$max = max($min, (int) $max);

		$raw = (int) $raw;
		if ($raw < $min) $raw = $min;
		if ($raw > $max) $raw = $max;

		// If raw is not power of two, round to nearest power-of-two (prefer <= max).
		if ($this->isPowerOfTwo($raw)) return $raw;

		$up = $this->nextPowerOfTwo($raw);
		if ($up <= $max) return $up;

		return $this->highestPowerOfTwo($max);
	}

	private function isPowerOfTwo(int $n): bool
	{
		if ($n <= 0) return false;
		return ($n & ($n - 1)) === 0;
	}

	private function nextPowerOfTwo(int $n): int
	{
		$n = max(1, $n);
		$p = 1;
		while ($p < $n) $p <<= 1;
		return $p;
	}

	private function highestPowerOfTwo(int $n): int
	{
		$n = max(1, $n);
		$p = 1;
		while (($p << 1) <= $n) $p <<= 1;
		return $p;
	}

	/**
	 * Use ErrorHandler for deduplicated logging; keep bounded in memory.
	 */
	private function boundedErrorLog(array $candidate, ?string $seed = null): void
	{
		// Bound aggregator size.
		if (count($this->errors) > 512) {
			$this->errors = array_slice($this->errors, -256);
		}

		ErrorHandler::evaluateExistenceToLogChannel(
			'purchases_seeder_errors',
			$candidate,
			'debug',
			'warning',
			$seed
		);
	}

	/**
	 * Best-effort delete to force final power-of-two if we early-stopped.
	 * Prefer PK deletes; if PK list is not reliable in your schema, switch to purchase_id deletes.
	 */
	private function deleteSeededRowsByPrimaryKeys(string $table, array $pks, int $toDelete): void
	{
		if ($toDelete <= 0) return;
		if (!Schema::hasTable($table)) return;
		if (!$pks) return;

		// Delete the most recent ones we tracked.
		$slice = array_slice($pks, -$toDelete);

		// Chunk deletes
		$chunks = array_chunk($slice, 250);
		foreach ($chunks as $chunk) {
			$chunk = array_values($chunk);

			try {
				// Prefer 'id' PK if exists; else attempt BC::COL_PRC_ID
				if ($this->hasColumnCached($table, 'id')) {
					DB::table($table)->whereIn('id', $chunk)->delete();
				} elseif ($this->hasColumnCached($table, BC::COL_PRC_ID)) {
					DB::table($table)->whereIn(BC::COL_PRC_ID, $chunk)->delete();
				}
			} catch (\Throwable $e) {
				Log::warning('PurchasesSeeder trimming delete failed', [
					'table' => $table,
					'msg' => $e->getMessage(),
				]);
				break;
			}
		}
	}
}
