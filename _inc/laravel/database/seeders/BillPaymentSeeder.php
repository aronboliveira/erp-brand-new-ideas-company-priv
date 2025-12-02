<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{PaymentMethod, PaymentStatus, PaymentType};
use App\Enums\TransferType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BillPaymentSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY  = 0.65;     // probabilidade média de opcionais
	private const PER_BILL_MIN = 0;        // mín. de pagamentos por bill
	private const PER_BILL_MAX = 2;        // máx. de pagamentos por bill
	private const MAX_AMOUNT   = 5000.00;  // teto para amount

	/**
	 * Opções CLI:
	 *  --count=INT   Limite aproximado de pagamentos totais (opcional).
	 */
	public function run(): void
	{
		// Sanidade de tabelas essenciais
		foreach ([DC::TABLE_BL_PAY, DC::TABLE_BILLS, DC::TABLE_BANK_ACC] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder interrompido.");
				return;
			}
		}

		// Coleta de FKs obrigatórias e opcionais
		$bills = DB::table(DC::TABLE_BILLS)->select('id', BC::COL_CUR_ID . ' as currency_id')->get();
		if ($bills->isEmpty()) {
			$this->command?->warn('Nenhuma conta a pagar encontrada em ' . DC::TABLE_BILLS . '.');
			return;
		}

		$bankAccounts = DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all();
		if (!$bankAccounts) {
			$this->command?->warn('Nenhuma conta bancária em ' . DC::TABLE_BANK_ACC . '.');
			return;
		}

		$users       = Schema::hasTable(DC::TABLE_USERS)            ? DB::table(DC::TABLE_USERS)->pluck('id')->all() : [];
		$orders      = Schema::hasTable(DC::TABLE_ORDERS)           ? DB::table(DC::TABLE_ORDERS)->pluck('id')->all() : [];
		$invoices    = Schema::hasTable(DC::TABLE_INVS)             ? DB::table(DC::TABLE_INVS)->pluck('id')->all() : [];
		$payslips    = Schema::hasTable(DC::TABLE_PAY_SLP)          ? DB::table(DC::TABLE_PAY_SLP)->pluck('id')->all() : [];
		$categories  = Schema::hasTable(DC::TABLE_PROD_SERV_CATS)   ? DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id')->all() : [];
		$prodUnits   = Schema::hasTable(DC::TABLE_PROD_SERV_UNITS)  ? DB::table(DC::TABLE_PROD_SERV_UNITS)->pluck('id')->all() : [];

		// Parâmetros de geração (fixos)
		$opt        = self::OPTIONALITY;
		$perMin     = self::PER_BILL_MIN;
		$perMax     = self::PER_BILL_MAX;
		$maxAmount  = self::MAX_AMOUNT;
		$target     = (int) ($this->command && $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count') ? $this->command?->option('count') : 64);

		if ($perMin < 0) $perMin = 0;
		if ($perMax < $perMin) $perMax = $perMin;

		$maybe = fn(callable $fn) => fake()->boolean((int) round($opt * 100)) ? $fn() : null;
		$json  = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$rows = [];
		$totalPlanned = 0;
		$now = Carbon::now();

		foreach ($bills as $bill) {
			$paymentsForBill = fake()->numberBetween($perMin, $perMax);

			if ($target > 0 && $totalPlanned + $paymentsForBill > $target) {
				$paymentsForBill = max(0, $target - $totalPlanned);
			}
			if ($paymentsForBill === 0) {
				continue;
			}

			for ($i = 0; $i < $paymentsForBill; $i++) {
				if ($target > 0 && $totalPlanned >= $target) {
					break 2;
				}

				// Valores e taxas
				$amount   = round(fake()->randomFloat(2, 50.00, $maxAmount), 2);
				$discount = $maybe(function () use ($amount) {
					$cap = min($amount, $amount * fake()->randomFloat(2, 0.00, 0.25));
					return $cap > 0 ? round(fake()->randomFloat(2, 0.00, $cap), 2) : 0.0;
				}) ?? 0.0;

				$svcFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.03), 2));
				$taxFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.05), 2));

				// Status e reconciliação
				$status       = Arr::random(PaymentStatus::values());
				$paymentType  = Arr::random(PaymentType::values());
				$methodLabel  = Arr::random(PaymentMethod::values());
				$methodCode   = fake()->randomElement([0, 1]); // compat. COL_PAY_MTD (tinyint)
				$isCompleted  = in_array($status, [
					PaymentStatus::Completed->value,
					PaymentStatus::Refunded->value,
					PaymentStatus::PartiallyRefunded->value,
				], true);

				$rccAt = $maybe(fn() => $isCompleted ? $now->subDays(fake()->numberBetween(0, 30))->subMinutes(fake()->numberBetween(0, 1440)) : null);
				$rccBy = $rccAt && $users ? Arr::random($users) : null;

				// Datas
				$createdAt = $now->subDays(fake()->numberBetween(0, 90))->subMinutes(fake()->numberBetween(0, 720));
				$date      = $createdAt->addMinutes(fake()->numberBetween(0, 4320)); // data do pagamento
				$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 10080));

				// Campos text/JSON opcionais
				$ppsCode    = $maybe(fn() => fake()->randomElement(['300', '301', '302']));
				$trfType    = $maybe(fn() => Arr::random(TransferType::values()));
				$taxesList  = $maybe(function () {
					$sample = [
						['name' => 'ISS',    'rate' => 2.00],
						['name' => 'PIS',    'rate' => 1.65],
						['name' => 'COFINS', 'rate' => 7.60],
					];
					return Arr::random($sample, fake()->numberBetween(1, 2));
				});
				$attachments = $maybe(fn() => [
					['name' => fake()->lexify('boleto-????.pdf'), 'url' => fake()->url()],
					['name' => fake()->lexify('comprovante-????.png'), 'url' => fake()->url()],
				]);
				$reconcileRules = $maybe(fn() => [
					'match_reference' => true,
					'tolerance_days'  => fake()->randomElement([0, 1, 3]),
					'amount_delta'    => fake()->randomFloat(2, 0, 5.00),
				]);
				$receiptMeta = $maybe(fn() => [
					'issuer'   => fake()->company(),
					'authCode' => strtoupper(Str::random(10)),
				]);

				// FKs opcionais
				$orderId     = $maybe(fn() => $orders     ? Arr::random($orders) : null);
				$invoiceId   = $maybe(fn() => $invoices   ? Arr::random($invoices) : null);
				$payslipId   = $maybe(fn() => $payslips   ? Arr::random($payslips) : null);
				$categoryId  = $maybe(fn() => $categories ? Arr::random($categories) : null);
				$unitId      = $maybe(fn() => $prodUnits  ? Arr::random($prodUnits) : null);
				$accountId   = Arr::random($bankAccounts); // obrigatório

				$currency = $bill->currency_id ?: 'BRL'; // espelha no legacy 'currency'

				$row = [
					'id'                   => (string) Str::uuid(),
					'code'                 => (string) Str::uuid(),
					BC::COL_BL_ID          => $bill->id,
					BC::COL_OD_ID          => $orderId,

					// emissão financeira
					'amount'               => $amount,
					'discount'             => $discount,
					BC::COL_CUR_ID         => $currency,
					BC::COL_SVC_FEE        => $svcFee,
					BC::COL_TXS_FEE        => $taxFee,
					'reference'            => $maybe(fn() => 'REF-' . strtoupper(Str::random(8))),
					'description'          => 'Pagamento de título gerado pelo seeder',
					'notes'                => $maybe(fn() => fake()->realText(120)),
					'attachments'          => $json($attachments),
					BC::COL_TC             => $json($maybe(fn() => ['terms' => fake()->sentence()])),

					// pagamento (HasPaymentColumns)
					'date'                 => $date->toDateString(),
					BC::COL_PPS_CD         => $ppsCode,
					BC::COL_TRF_TP         => $trfType,
					BC::COL_TXS_LST        => $json($taxesList),
					BC::COL_PAY_MTD        => $methodCode,
					BC::COL_PAY_MTD_LB     => $methodLabel,
					'status'               => $status,
					BC::COL_N_INTR         => $maybe(fn() => fake()->numberBetween(1, 6)),
					BC::COL_CURR_N_INTR    => $maybe(fn() => fake()->numberBetween(1, 6)),
					BC::COL_AUTORCC        => $maybe(fn() => fake()->boolean(30)),
					BC::COL_RCC_RL         => $json($reconcileRules),
					BC::COL_RCC_AT         => $rccAt?->toDateTimeString(),
					BC::COL_RCC_BY         => $rccBy,

					// conclusão
					BC::COL_BACC_ID        => $accountId,
					BC::COL_CAT_ID         => $categoryId,
					BC::COL_ADD_RCP        => $maybe(fn() => fake()->boolean(20) ? 'yes' : null),
					BC::COL_RCP_MD         => $json($receiptMeta),

					// vínculos opcionais
					'invoice'              => $invoiceId,
					'payslip'              => $payslipId,
					BC::COL_PRD_SV_UNT     => $unitId,

					// legado
					'currency'             => $currency,
					BC::COL_PAY_TP         => $paymentType,
					'receipt'              => $maybe(fn() => fake()->lexify('rcpt-????????')),

					// auditoria
					DC::COL_TABLE_CREATOR  => $maybe(fn() => $users ? Arr::random($users) : null),
					DC::COL_TABLE_UPDATER  => $maybe(fn() => $users ? Arr::random($users) : null),
					'created_at'           => $createdAt->toDateTimeString(),
					'updated_at'           => $updatedAt->toDateTimeString(),
				];

				// Remover apenas nulls; manter 0/false
				$rows[] = array_filter($row, static fn($v) => $v !== null);
				$totalPlanned++;
			}
		}

		if (!$rows) {
			$this->command?->info('BillPaymentSeeder: nada a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 1000) as $chunk) {
				DB::table(DC::TABLE_BL_PAY)->insert($chunk);
			}
		});

		$this->command?->info("BillPaymentSeeder: {$totalPlanned} pagamentos inseridos em " . DC::TABLE_BL_PAY . ".");
	}
}
