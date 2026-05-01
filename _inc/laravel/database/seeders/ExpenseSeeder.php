<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC
};
use App\Enums\{AppModuleType, EvaluationStatus, MonthName, TransferType, UserType};
use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class ExpenseSeeder extends Seeder
{
	private ConsoleOutput $out;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 120;

	public function run(): void
	{
		$this->out = new ConsoleOutput();
		$clock = microtime(true);
		$table = DC::TABLE_EXP;

		$branchIds = $this->pluckIds(DC::TABLE_BRANCHES);
		$companyUserIds = $this->pluckIdsByUserType(DC::TABLE_USERS, UserType::Company->value);
		$projectIds = $this->pluckIds(DC::TABLE_PROJECTS);

		$vendorIds = $this->pluckIds(DC::TABLE_VENDORS);
		$posIds    = $this->pluckIds(DC::TABLE_POS);
		$billIds   = $this->pluckIds(DC::TABLE_BILLS);
		$orderIds  = $this->pluckIds(DC::TABLE_ORDERS);

		$departmentIds = $this->pluckIds(DC::TABLE_DEPARTMENTS);
		$projectTaskIds = $this->pluckIds(DC::TABLE_PROJ_TSKS);
		$taskIds = $this->pluckIds(DC::TABLE_TASKS);

		$taxIds = $this->maybePluckIds('taxes'); // if your table name differs, keep empty and it still works.

		$modules = AppModuleType::cases();
		if (!$modules) {
			$this->out->writeln("<comment>[{$table}] skipped: no AppModuleType cases</comment>");
			return;
		}

		// $hardCap = 32000;
		$hardCap = 8;

		/*
		 * Target calculation:
		 * 1) Baseline per module: 2..64
		 * 2) Coverage expansions:
		 *    - every branch id gets >= 1 per module
		 *    - every company user id gets >= 1 per module
		 *    - every project id gets >= 2 per module
		 * 3) Round up to 64 multiple
		 */
		$baseline = 0;
		foreach ($modules as $m)
			$baseline += mt_rand(2, 64);

		$coveragePerModule =
			(count($branchIds) ?: 0)
			+ (count($companyUserIds) ?: 0)
			+ ((count($projectIds) ?: 0) * 2);

		$rawTotal = min($hardCap, $baseline + ($coveragePerModule * count($modules)));
		$targetTotal = min($hardCap, $this->roundUpTo64(max(64, $rawTotal)));

		$this->out->writeln(sprintf(
			"[expenses] baseline=%d coverage_per_module=%d modules=%d raw_total=%d target_total=%d hard_cap=%d",
			$baseline,
			$coveragePerModule,
			count($modules),
			$rawTotal,
			$targetTotal,
			$hardCap
		));

		$created = 0;
		$attempts = 0;
		$maxAttempts = max(4096, $targetTotal * 10);

		foreach ($modules as $moduleCase) {
			if ($created >= $targetTotal)
				break;

			$moduleValue = $moduleCase->value;

			/*
			 * Phase A: ensure baseline diversity for the module (2..64 rows)
			 */
			$moduleBaseline = mt_rand(2, 64);
			for ($i = 0; $i < $moduleBaseline && $created < $targetTotal && $attempts < $maxAttempts; $i++) {
				$attempts++;
				$this->createOneExpenseVariation(
					$moduleValue,
					$branchIds,
					$companyUserIds,
					$projectIds,
					$vendorIds,
					$posIds,
					$billIds,
					$orderIds,
					$departmentIds,
					$projectTaskIds,
					$taskIds,
					$taxIds
				) && $created++;
			}

			/*
			 * Phase B: branch coverage (>= 1 per branch per module)
			 */
			foreach ($branchIds as $branchId) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				if ($created >= $targetTotal || $attempts >= $maxAttempts)
					break;

				$attempts++;
				$this->createOneExpenseVariation(
					$moduleValue,
					$branchIds,
					$companyUserIds,
					$projectIds,
					$vendorIds,
					$posIds,
					$billIds,
					$orderIds,
					$departmentIds,
					$projectTaskIds,
					$taskIds,
					$taxIds,
					forced: [
						'branch' => $branchId,
						'company' => $companyUserIds ? $companyUserIds[array_rand($companyUserIds)] : null,
					]
				) && $created++;
			}

			/*
			 * Phase C: company user coverage (>= 1 per company user per module)
			 */
			foreach ($companyUserIds as $companyId) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				if ($created >= $targetTotal || $attempts >= $maxAttempts)
					break;

				$attempts++;
				$this->createOneExpenseVariation(
					$moduleValue,
					$branchIds,
					$companyUserIds,
					$projectIds,
					$vendorIds,
					$posIds,
					$billIds,
					$orderIds,
					$departmentIds,
					$projectTaskIds,
					$taskIds,
					$taxIds,
					forced: [
						'company' => $companyId,
						'branch'  => $branchIds ? $branchIds[array_rand($branchIds)] : null,
					]
				) && $created++;
			}

			/*
			 * Phase D: project coverage (>= 2 per project per module)
			 */
			foreach ($projectIds as $projectId) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				for ($j = 0; $j < 2; $j++) {
					if ($created >= $targetTotal || $attempts >= $maxAttempts)
						break;

					$attempts++;
					$this->createOneExpenseVariation(
						$moduleValue,
						$branchIds,
						$companyUserIds,
						$projectIds,
						$vendorIds,
						$posIds,
						$billIds,
						$orderIds,
						$departmentIds,
						$projectTaskIds,
						$taskIds,
						$taxIds,
						forced: [
							PJC::COL_PJ_ID => $projectId,
							'company'      => $companyUserIds ? $companyUserIds[array_rand($companyUserIds)] : null,
							'branch'       => $branchIds ? $branchIds[array_rand($branchIds)] : null,
						]
					) && $created++;
				}
			}
		}

		/*
		 * Phase E: top-up to hit the exact target (multiple of 64), preserving diversity across modules.
		 */
		while ($created < $targetTotal && $attempts < $maxAttempts) {

			if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
				Log::warning(self::class . ' seeding time limit reached, stopping early');
				return;
			}
			$attempts++;
			$moduleValue = $modules[$created % count($modules)]->value;

			$this->createOneExpenseVariation(
				$moduleValue,
				$branchIds,
				$companyUserIds,
				$projectIds,
				$vendorIds,
				$posIds,
				$billIds,
				$orderIds,
				$departmentIds,
				$projectTaskIds,
				$taskIds,
				$taxIds
			) && $created++;
		}

		if ($attempts >= $maxAttempts && $created < $targetTotal) {
			$this->out->writeln("<comment>[{$table}] break-out: attempt limit reached (created {$created}/{$targetTotal})</comment>");
		} else {
			$this->out->writeln("<info>[{$table}] done: created {$created} rows</info>");
		}
	}

	private function createOneExpenseVariation(
		string $moduleValue,
		array $branchIds,
		array $companyUserIds,
		array $projectIds,
		array $vendorIds,
		array $posIds,
		array $billIds,
		array $orderIds,
		array $departmentIds,
		array $projectTaskIds,
		array $taskIds,
		array $taxIds,
		array $forced = []
	): bool {
		$now = CarbonImmutable::now('America/Sao_Paulo');

		$company = $forced['company'] ?? ($companyUserIds ? $companyUserIds[array_rand($companyUserIds)] : null);
		$branch  = $forced['branch'] ?? ($branchIds ? $branchIds[array_rand($branchIds)] : null);

		$project = $forced[PJC::COL_PJ_ID] ?? ($projectIds && mt_rand(1, 100) <= 70 ? $projectIds[array_rand($projectIds)] : null);

		$department = $forced['department'] ?? ($departmentIds && mt_rand(1, 100) <= 55 ? $departmentIds[array_rand($departmentIds)] : null);
		$vendor = $forced['vendor'] ?? ($vendorIds && mt_rand(1, 100) <= 65 ? $vendorIds[array_rand($vendorIds)] : null);

		$pos   = $forced['pos'] ?? ($posIds && mt_rand(1, 100) <= 40 ? $posIds[array_rand($posIds)] : null);
		$bill  = $forced['bill'] ?? ($billIds && mt_rand(1, 100) <= 35 ? $billIds[array_rand($billIds)] : null);
		$order = $forced['order'] ?? ($orderIds && mt_rand(1, 100) <= 35 ? $orderIds[array_rand($orderIds)] : null);

		$pjTask = $forced[PJC::COL_PJ_TSK_ID] ?? ($projectTaskIds && mt_rand(1, 100) <= 40 ? $projectTaskIds[array_rand($projectTaskIds)] : null);
		$task   = $forced[AC::COL_TSK_ID] ?? ($taskIds && mt_rand(1, 100) <= 40 ? $taskIds[array_rand($taskIds)] : null);

		// name is NOT nullable; keep deterministic-ish and indexed
		$name = sprintf(
			"Expense %s / %s",
			strtoupper($moduleValue),
			bin2hex(random_bytes(4))
		);

		$dueDate = $now->subDays(mt_rand(0, 180))->toDateString();

		$baseCurrency = strtoupper(trim((string) (SC::DEF_SITE_CURRENCY_ID ?? 'BRL')));
		$currency = $baseCurrency;
		if (mt_rand(1, 100) <= 15) {
			$currency = Arr::random(['BRL', 'USD', 'EUR', $baseCurrency]);
			$currency = strtoupper(trim($currency));
		}

		$exchangeRate = $currency === $baseCurrency ? 1.000000 : (float) number_format(mt_rand(100000, 800000) / 100000, 6, '.', '');

		// Taxes JSON: array of ids and/or names (keep as array; model normalizes)
		$taxes = null;
		if (mt_rand(1, 100) <= 55) {
			$taxes = [];
			$taxCount = mt_rand(0, 4);
			for ($i = 0; $i < $taxCount; $i++) {
				if ($taxIds && mt_rand(1, 100) <= 70) {
					$taxes[] = $taxIds[array_rand($taxIds)];
				} else {
					$taxes[] = Arr::random(['ISS', 'ICMS', 'IPI', 'PIS', 'COFINS', 'IOF']);
				}
			}
			$taxes = array_values(array_unique($taxes));
			if ($taxes === [])
				$taxes = null;
		}

		$evaluation = array_column(EvaluationStatus::cases(), 'value');
		$eval = $evaluation ? $evaluation[array_rand($evaluation)] : EvaluationStatus::Pending->value;

		$attachment = mt_rand(1, 100) <= 45 ? ('att://' . bin2hex(random_bytes(10))) : null;
		$receipt    = mt_rand(1, 100) <= 55 ? ('rcpt://' . bin2hex(random_bytes(10))) : null;

		// Optional NFe fields (unique key must be unique if present)
		$nfeKey = null;
		if (mt_rand(1, 100) <= 18)
			$nfeKey = $this->makeUniqueNfeKey(DC::TABLE_EXP, BC::COL_NFE_KEY);

		// Credit card fields: occasionally present; keep month/year coherent-ish
		$ccFlag = mt_rand(1, 100) <= 22 ? Arr::random(['Visa', 'Mastercard', 'Elo', 'Amex']) : null;
		$ccNum  = $ccFlag ? (string) mt_rand(4000000000000000, 4999999999999999) : null;
		$ccDig  = $ccFlag ? str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT) : null;
		$ccName = $ccFlag ? Arr::random(['ARON OLIVEIRA', 'NOVA BRAND NEW IDEAS COMPANY', 'CLIENTE TESTE', 'FULANO DE TAL']) : null;

		$expMonth = MonthName::values();
		$ccExpM = $ccFlag && $expMonth ? $expMonth[array_rand($expMonth)] : null;
		$ccExpY = $ccFlag ? (string) ($now->year + mt_rand(0, 5)) : null;

		// Transfer type is present in basic payment columns
		$trfTypes = TransferType::values();
		$trfType = $trfTypes ? $trfTypes[array_rand($trfTypes)] : TransferType::Other->value;

		// total_amount column exists and defaults; keep a reasonable number (model may reconcile later)
		$totalAmount = (float) number_format(mt_rand(500, 250000) / 100, 2, '.', '');

		$metadata = mt_rand(1, 100) <= 35 ? [
			'source' => Arr::random(['seed', 'import', 'api', 'manual']),
			'tags'   => array_values(array_unique([
				Arr::random(['office', 'infra', 'travel', 'services', 'tax']),
				Arr::random(['project', 'ops', 'finance', 'billing']),
			])),
		] : null;

		// $this->out->writeln(sprintf(
		// 	"[expense] module=%s company=%s branch=%s project=%s total=%.2f cur=%s ex=%.6f eval=%s taxes=%s nfe=%s att=%s rcpt=%s",
		// 	$moduleValue,
		// 	$company ?? 'null',
		// 	$branch ?? 'null',
		// 	$project ?? 'null',
		// 	$totalAmount,
		// 	$currency,
		// 	$exchangeRate,
		// 	$eval,
		// 	$taxes ? (string) count($taxes) : '0',
		// 	$nfeKey ? 'yes' : 'no',
		// 	$attachment ? 'yes' : 'no',
		// 	$receipt ? 'yes' : 'no'
		// ));

		try {
			Expense::query()->create([
				'module'      => $moduleValue,
				'company'     => $company,
				'branch'      => $branch,
				'department'  => $department,

				// accountant: leave null; trait/model may enforce user type later
				'accountant'  => $forced['accountant'] ?? null,

				'vendor'      => $vendor,

				'name'        => $name,
				'date'        => $dueDate,
				BC::COL_TTL_AMT => $totalAmount,

				// Basic payment columns (subset; traits will normalize status/method if present on schema)
				BC::COL_TRF_TP   => $trfType,

				'pos'         => $pos,
				'bill'        => $bill,
				'order'       => $order,

				'currency'    => $currency,
				BC::COL_EXC_RT => $exchangeRate,

				'evaluation'  => $eval,

				'attachment'  => $attachment,
				PJC::COL_PJ_ID => $project,
				PJC::COL_PJ_TSK_ID => $pjTask,
				AC::COL_TSK_ID => $task,

				'receipt'     => $receipt,
				'taxes'       => $taxes,

				BC::COL_NFE_KEY      => $nfeKey,
				BC::COL_NFE_NUMBER   => $nfeKey ? (string) mt_rand(1, 999999) : null,
				BC::COL_NFE_SERIES   => $nfeKey ? (string) mt_rand(1, 999) : null,
				BC::COL_NFE_XML_PATH => $nfeKey ? ('nfe://xml/' . bin2hex(random_bytes(8))) : null,
				BC::COL_NFE_PROTOCOL => $nfeKey ? (string) mt_rand(1000000000, 9999999999) : null,
				BC::COL_NFE_AUTH_AT  => $nfeKey ? $now->subDays(mt_rand(0, 30)) : null,

				BC::COL_CD_FLG  => $ccFlag,
				BC::COL_CD_NB   => $ccNum,
				BC::COL_CD_DG   => $ccDig,
				BC::COL_CD_HNM  => $ccName,
				BC::COL_CD_EX_M => $ccExpM,
				BC::COL_CD_EX_Y => $ccExpY,

				'metadata'    => $metadata,

				DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
				DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
			]);

			return true;
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed creating Expense', [
				'table' => DC::TABLE_EXP,
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
				'error' => $e->getMessage(),
				'module' => $moduleValue,
				'company' => $company,
				'branch'  => $branch,
				'project' => $project,
			]);
			return false;
		}
	}

	private function pluckIds(string $table): array
	{
		try {
			$rows = DB::select("select id from {$table}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::error(self::class . ' pluckIds failed', [
				'table' => $table,
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function pluckIdsByUserType(string $table, string $type): array
	{
		try {
			$rows = DB::select("select id from {$table} where type = ?", [$type]);
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::error(self::class . ' pluckIdsByUserType failed', [
				'table' => $table,
				'type'  => $type,
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	private function maybePluckIds(string $table): array
	{
		try {
			$rows = DB::select("select id from {$table}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable) {
			return [];
		}
	}

	private function makeUniqueNfeKey(string $table, string $col): string
	{
		$attempt = 0;
		$max = 128;

		do {
			$attempt++;
			$candidate = $this->randomDigits(44);

			try {
				$exists = DB::table($table)->where($col, $candidate)->exists();
				if (!$exists)
					return $candidate;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' makeUniqueNfeKey exists-check failed', [
					'table' => $table,
					'col'   => $col,
					'file'  => $e->getFile(),
					'line'  => $e->getLine(),
					'error' => $e->getMessage(),
				]);
				return $candidate;
			}
		} while ($attempt < $max);

		return $this->randomDigits(44);
	}

	private function randomDigits(int $len): string
	{
		$out = '';
		for ($i = 0; $i < $len; $i++)
			$out .= (string) mt_rand(0, 9);
		return $out;
	}

	private function roundUpTo64(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}
}
