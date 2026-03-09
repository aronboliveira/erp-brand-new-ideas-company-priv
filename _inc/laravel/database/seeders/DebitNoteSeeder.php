<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\MonthName;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransferType;
use App\Models\{Bill, DebitNote, Invoice};
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DebitNoteSeeder extends Seeder
{
	// --- Parâmetros fixos (sem env) ---
	private const COUNT            = 2; // was 120
	private const OPTIONALITY      = 0.92;
	private const BILL_RATIO       = 0.70; // viés para bill (débito)
	private const CARD_RATIO       = 0.95;
	private const ATTACH_RATIO     = 0.78;
	private const RECONCILE_RATIO  = 0.38;

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_DB_NOTES)) {
			$this->command?->warn('Tabela de debit notes ausente. Abortando.');
			return;
		}

		$customers  = $this->pluckIds(DC::TABLE_CUSTOMERS ?? 'customers');
		$vendors    = $this->pluckIds(DC::TABLE_VENDORS ?? 'vendors');
		$invoices   = Invoice::pluck('id')->all();
		$bills      = Bill::where('type', 'bill')
			->where(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID)
			->pluck('id')
			->all()
			?: Bill::where(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID)
			->pluck('id')
			->all()
			?: Bill::pluck('id')->all();
		$accounts   = $this->pluckIds(DC::TABLE_BANK_ACC ?? 'bank_accounts');
		$categories = $this->pluckIds(DC::TABLE_PROD_SERV_CATS ?? 'product_service_categories');
		$users      = $this->pluckIds(DC::TABLE_USERS ?? 'users');
		$contracts  = $this->pluckIds(DC::TABLE_CONTRACTS ?? 'contracts');
		$loans      = $this->pluckIds(DC::TABLE_LN ?? 'loans');
		$units      = $this->pluckIds(DC::TABLE_PROD_SERV_UNITS ?? 'product_service_units');
		$payslips   = $this->pluckIds(DC::TABLE_PAY_SLP ?? 'payslips');

		if (!$customers) {
			$this->command?->warn('Sem customers. Abortando DebitNoteSeeder.');
			return;
		}
		if (!$vendors) {
			$this->command?->warn('Sem vendors. Abortando DebitNoteSeeder.');
			return;
		}
		if (!$invoices && !$bills) {
			$this->command?->warn('Sem invoices/bills. Abortando DebitNoteSeeder.');
			return;
		}

		$statusVals = array_map(fn($c) => $c->value, PaymentStatus::cases());
		$methodVals = array_map(fn($c) => $c->value, PaymentMethod::cases());
		$trfVals    = array_map(fn($c) => $c->value, TransferType::cases());
		$monthVals  = array_map(fn($c) => $c->value, MonthName::cases());

		$today   = Carbon::today();
		$fromDay = $today->subDays(150);

		$seenRef   = [];
		$seenFiles = [];
		$seenPan   = [];
		$seenLast4 = [];

		DB::transaction(function () use (
			$customers,
			$vendors,
			$invoices,
			$bills,
			$accounts,
			$categories,
			$users,
			$contracts,
			$loans,
			$units,
			$payslips,
			$statusVals,
			$methodVals,
			$trfVals,
			$monthVals,
			$fromDay,
			$today,
			&$seenRef,
			&$seenFiles,
			&$seenPan,
			&$seenLast4
		) {
			for ($i = 1; $i <= self::COUNT; $i++) {
				try {
					$customerId = Arr::random($customers);
					$vendorId   = Arr::random($vendors);

					$invoiceId = null;
					$billId = null;

					// Always assign a bill — the debit_notes index view requires
					// the parent bill relationship to display rows.
					if (!empty($bills)) {
						$billId = Arr::random($bills);
					}

					if (!empty($invoices) && fake()->boolean((int) round(self::BILL_RATIO * 100))) {
						$invoiceId = Arr::random($invoices);
					}

					if (!$billId && !empty($invoices)) {
						$invoiceId = $invoiceId ?: Arr::random($invoices);
					}

					if (!$invoiceId && !$billId) {
						Log::warning('DebitNoteSeeder: No valid invoice or bill found for iteration ' . $i);
						continue;
					}

					$date = fake()->dateTimeBetween($fromDay, $today)->format('Y-m-d');
					$ref  = $this->uniqueRef("DBN-{$date}-", $seenRef);

					$amount   = (float) fake()->randomFloat(2, 80, 9000);
					$discount = (float) fake()->randomFloat(2, 0, round($amount * 0.18, 2));
					$svcFee   = (float) fake()->randomFloat(2, 0, round($amount * 0.025, 2));
					$taxFee   = (float) fake()->randomFloat(2, 0, round($amount * 0.11, 2));
					$currency = config('app.currency', 'BRL');

					$status  = Arr::random($statusVals);
					$pmLbl   = Arr::random($methodVals);
					$pmInt   = fake()->boolean() ? 1 : 0;
					$trfType = Arr::random($trfVals);
					$ppsCode = Arr::random(['300', '101', '202', '710', '905']);

					$taxList     = fake()->boolean((int) round(self::OPTIONALITY * 100)) ? $this->makeTaxList($ref) : null;
					$attachments = fake()->boolean((int) round(self::ATTACH_RATIO * 100)) ? $this->makeAttachments($ref, $seenFiles) : null;
					$terms       = fake()->boolean((int) round(self::OPTIONALITY * 100))
						? ['Scope: debit note adjustment/charge.', 'Subject to reconciliation and settlement windows.', 'Non-transferable.']
						: null;

					$autoRcc  = fake()->boolean((int) round(self::OPTIONALITY * 100));
					$rccAt    = $autoRcc && fake()->boolean((int) round(self::RECONCILE_RATIO * 100))
						? now()->subMinutes(fake()->numberBetween(5, 900))
						: null;
					$rccBy    = $rccAt && $users ? Arr::random($users) : null;
					$rccRules = $autoRcc ? ['match' => 'amount+last4', 'window_days' => 3] : null;

					$accountId  = $accounts ? Arr::random($accounts) : null;
					$categoryId = $categories ? Arr::random($categories) : null;
					$contract   = $contracts ? Arr::random($contracts) : null;
					$loan       = $loans     ? Arr::random($loans)     : null;
					$unit       = $units     ? Arr::random($units)     : null;
					$payslip    = $payslips  ? Arr::random($payslips)  : null;

					$nInst   = fake()->numberBetween(1, 12);
					$curInst = fake()->numberBetween(1, $nInst);

					$card = null;
					if (fake()->boolean((int) round(self::CARD_RATIO * 100))) {
						$flag   = Arr::random(['visa', 'mastercard', 'amex', 'elo', 'hipercard']);
						$holder = strtoupper(fake()->firstName() . ' ' . fake()->lastName());
						$year   = (int) now()->format('Y') + fake()->numberBetween(0, 5);
						$month  = Arr::random($monthVals);

						$pan   = $this->uniquePan(fake()->numerify('5###############'), $seenPan);
						$last4 = substr(preg_replace('/\D+/', '', $pan), -4) ?: str_pad((string) fake()->randomNumber(4), 4, '0', STR_PAD_LEFT);
						$this->ensureUnique($last4, $seenLast4);

						$card = compact('flag', 'holder', 'year', 'month', 'pan', 'last4');
					}

					$note = new DebitNote();

					$payload = [
						BC::COL_CST_ID       => $customerId,
						'invoice'            => $invoiceId,
						'bill'               => $billId,

						'date'               => $date,
						'amount'             => $amount,
						'discount'           => $discount,
						BC::COL_CUR_ID       => $currency,
						'reference'          => $ref,
						'description'        => "Debit note {$ref}",
						'notes'              => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? fake()->sentence(12) : null,
						'attachments'        => $attachments,
						BC::COL_TC           => $terms,

						'status'             => $status,
						BC::COL_N_INTR       => $nInst,
						BC::COL_CURR_N_INTR  => $curInst,

						BC::COL_BACC_ID      => $accountId,
						BC::COL_CAT_ID       => $categoryId,
						BC::COL_ADD_RCP      => 'receipt_attached',

						BC::COL_SVC_FEE      => $svcFee,
						BC::COL_TXS_FEE      => $taxFee,
						BC::COL_TXS_LST      => $taxList,

						BC::COL_PAY_MTD      => $pmInt,
						BC::COL_PAY_MTD_LB   => $pmLbl,
						BC::COL_TRF_TP       => $trfType,
						BC::COL_PPS_CD       => $ppsCode,
						BC::COL_PPS_DS       => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? 'charge/adjustment' : null,

						BC::COL_IS_SCD       => fake()->boolean(9),
						BC::COL_CAN_CHG_BK   => fake()->boolean(5),
						BC::COL_AUTORCC      => $autoRcc,
						BC::COL_RCC_RL       => $rccRules,
						BC::COL_RCC_AT       => $rccAt,
						BC::COL_RCC_BY       => $rccBy,

						'contract'           => $contract,
						'loan'               => $loan,
						BC::COL_PRD_SV_UNT   => $unit,
						'payslip'            => $payslip,

						\App\Config\Constants\UsersConstants::COL_VD_ID => $vendorId,
					];
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Nota de Débito para Cliente {$customerId} com conta {$accountId}");
					$note = DebitNote::create($payload);
					if ($card) {
						$note->{BC::COL_CD_FLG} = $card['flag'];
						$note->{BC::COL_CD_HNM} = $card['holder'];
						$note->{BC::COL_CD_EX_Y} = (string) $card['year'];
						$note->{BC::COL_CD_EX_M} = $card['month'];
						$note->{BC::COL_CD_NB}   = $card['pan'];
						$note->{BC::COL_CD_DG}   = $card['last4'];
					}

					// Novos nomes de coluna
					if (Schema::hasColumn(DC::TABLE_DB_NOTES, DC::COL_TABLE_CREATOR)) {
						$note->{DC::COL_TABLE_CREATOR} = DC::DEFAULT_UUID;
					}
					if (Schema::hasColumn(DC::TABLE_DB_NOTES, DC::COL_TABLE_UPDATER)) {
						$note->{DC::COL_TABLE_UPDATER} = DC::DEFAULT_UUID;
					}

					$note->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		});

		$this->command?->info("DebitNoteSeeder: " . self::COUNT . " registros inseridos.");
	}

	private function pluckIds(string $table): array
	{
		if (!Schema::hasTable($table)) return [];
		return DB::table($table)->limit(5124)->pluck('id')->filter()->values()->all();
	}

	private function uniqueRef(string $prefix, array &$seen): string
	{
		do {
			$ref = $prefix . substr(Str::uuid()->toString(), 0, 10);
		} while (isset($seen[$ref]));
		$seen[$ref] = true;
		return $ref;
	}

	private function makeTaxList(string $seed): array
	{
		$items = [];
		$n = fake()->numberBetween(1, 3);
		for ($i = 0; $i < $n; $i++) {
			$label = 'TAX-' . substr(sha1($seed . $i . Str::uuid()->toString()), 0, 6);
			$items[] = ['name' => $label, 'rate' => fake()->randomFloat(2, 0.5, 7.5)];
		}
		return $items;
	}

	private function makeAttachments(string $seed, array &$seenFiles): array
	{
		$out = [];
		$n = fake()->numberBetween(1, 3);
		for ($i = 0; $i < $n; $i++) {
			do {
				$name = 'rcpt-' . $i . '-' . substr(sha1($seed . $i . Str::uuid()->toString()), 0, 10) . '.pdf';
			} while (isset($seenFiles[$name]));
			$seenFiles[$name] = true;

			$out[] = [
				'path'      => "/docs/debit-notes/{$name}",
				'extension' => 'pdf',
				'label'     => 'Receipt PDF',
			];
		}
		return $out;
	}

	private function uniquePan(string $pattern, array &$seenPan): string
	{
		do {
			$candidate = preg_replace('/\D+/', '', fake()->numerify($pattern)) ?: fake()->numerify('5###############');
		} while (isset($seenPan[$candidate]));
		$seenPan[$candidate] = true;
		return $candidate;
	}

	private function ensureUnique(string $key, array &$set): void
	{
		if (!isset($set[$key])) {
			$set[$key] = true;
			return;
		}
		$c = 1;
		while (isset($set[$key . $c])) $c++;
		$set[$key . $c] = true;
	}
}
