<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{PaymentMethod, PaymentStatus, PaymentType};
use App\Enums\TransferType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
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
		$bills = DB::table(DC::TABLE_BILLS)
			->select('id', BC::COL_CUR_ID . ' as currency_id')
			->get();

		if ($bills->isEmpty()) {
			$this->command?->warn('Nenhuma conta a pagar encontrada em ' . DC::TABLE_BILLS . '.');
			return;
		}

		$bankAccounts = DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all();
		if (!$bankAccounts) {
			$this->command?->warn('Nenhuma conta bancária em ' . DC::TABLE_BANK_ACC . '.');
			return;
		}

		$users      = Schema::hasTable(DC::TABLE_USERS)          ? DB::table(DC::TABLE_USERS)->pluck('id')->all()        : [];
		$orders     = Schema::hasTable(DC::TABLE_ORDERS)         ? DB::table(DC::TABLE_ORDERS)->pluck('id')->all()       : [];
		$invoices   = Schema::hasTable(DC::TABLE_INVS)           ? DB::table(DC::TABLE_INVS)->pluck('id')->all()         : [];
		$payslips   = Schema::hasTable(DC::TABLE_PAY_SLP)        ? DB::table(DC::TABLE_PAY_SLP)->pluck('id')->all()      : [];
		$categories = Schema::hasTable(DC::TABLE_PROD_SERV_CATS) ? DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id')->all() : [];
		$prodUnits  = Schema::hasTable(DC::TABLE_PROD_SERV_UNITS) ? DB::table(DC::TABLE_PROD_SERV_UNITS)->pluck('id')->all() : [];

		// Parâmetros de geração
		$opt       = self::OPTIONALITY;
		$perMin    = max(0, self::PER_BILL_MIN);
		$perMax    = max($perMin, self::PER_BILL_MAX);
		$maxAmount = self::MAX_AMOUNT;
		$target    = (int) (
			$this->command instanceof \Illuminate\Console\Command
			&& $this->command->hasOption('count')
			? $this->command->option('count')
			: 64
		);

		$maybe = function (callable $fn) use ($opt) {
			return fake()->boolean((int) round($opt * 100)) ? $fn() : null;
		};

		$json = static function ($v) {
			return $v === null
				? null
				: json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		};

		$now        = Carbon::now();
		$inserted   = 0;

		DB::beginTransaction();

		try {
			foreach ($bills as $bill) {
				try {
					if ($target > 0 && $inserted >= $target) {
						break;
					}

					$paymentsForBill = fake()->numberBetween($perMin, $perMax);

					if ($target > 0 && $inserted + $paymentsForBill > $target) {
						$paymentsForBill = max(0, $target - $inserted);
					}
					if ($paymentsForBill === 0) {
						continue;
					}

					for ($i = 0; $i < $paymentsForBill; $i++) {
						if ($target > 0 && $inserted >= $target) {
							break 2;
						}

						// Valores base
						$amount = round(fake()->randomFloat(2, 50.00, $maxAmount), 2);

						$discount = $maybe(function () use ($amount) {
							$maxDisc = min($amount, $amount * fake()->randomFloat(2, 0.00, 0.25));
							return $maxDisc > 0
								? round(fake()->randomFloat(2, 0.00, $maxDisc), 2)
								: 0.0;
						}) ?? 0.0;

						$svcFee = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.03), 2));
						$taxFee = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.05), 2));

						// Status / método / tipo
						$status      = Arr::random(PaymentStatus::values());
						$paymentType = Arr::random(PaymentType::values());
						$methodLabel = Arr::random(PaymentMethod::values());
						$methodCode  = fake()->randomElement([0, 1]); // compat tinyint payment_method

						$isFinalized = in_array($status, [
							PaymentStatus::Completed->value,
							PaymentStatus::Refunded->value,
							PaymentStatus::PartiallyRefunded->value,
							PaymentStatus::Cancelled->value,
							PaymentStatus::Failed->value,
						], true);

						// Datas base
						$createdAt = $now
							->copy()
							->subDays(fake()->numberBetween(0, 90))
							->subMinutes(fake()->numberBetween(0, 720));

						$date = $createdAt
							->copy()
							->addMinutes(fake()->numberBetween(0, 4320));

						$updatedAt = $createdAt
							->copy()
							->addMinutes(fake()->numberBetween(0, 10080));

						// Reconciliação
						$reconciledAt = $maybe(function () use ($isFinalized, $now) {
							if (!$isFinalized) {
								return null;
							}
							return $now
								->copy()
								->subDays(fake()->numberBetween(0, 30))
								->subMinutes(fake()->numberBetween(0, 1440))
								->toDateTimeString();
						});

						$reconciledBy = $reconciledAt && $users
							? Arr::random($users)
							: null;

						// Campos text/JSON opcionais
						$ppsCode = $maybe(fn() => fake()->randomElement(['300', '301', '302']));
						$trfType = $maybe(fn() => Arr::random(TransferType::values()));

						$taxesList = $maybe(function () {
							$sample = [
								['name' => 'ISS',    'rate' => 2.00],
								['name' => 'PIS',    'rate' => 1.65],
								['name' => 'COFINS', 'rate' => 7.60],
							];
							return Arr::random($sample, fake()->numberBetween(1, 2));
						});

						$attachments = $maybe(function () {
							return [
								[
									'name' => fake()->lexify('boleto-????.pdf'),
									'url'  => fake()->url(),
								],
								[
									'name' => fake()->lexify('comprovante-????.png'),
									'url'  => fake()->url(),
								],
							];
						});

						$reconcileRules = $maybe(function () {
							return [
								'match_reference' => true,
								'tolerance_days'  => fake()->randomElement([0, 1, 3]),
								'amount_delta'    => fake()->randomFloat(2, 0, 5.00),
							];
						});

						$receiptMeta = $maybe(function () {
							return [
								'issuer'   => fake()->company(),
								'authCode' => strtoupper(Str::random(10)),
							];
						});

						$terms = $maybe(fn() => ['terms' => fake()->sentence()]);

						// FKs opcionais
						$orderId    = $maybe(fn() => $orders     ? Arr::random($orders)     : null);
						$invoiceId  = $maybe(fn() => $invoices   ? Arr::random($invoices)   : null);
						$payslipId  = $maybe(fn() => $payslips   ? Arr::random($payslips)   : null);
						$categoryId = $maybe(fn() => $categories ? Arr::random($categories) : null);
						$unitId     = $maybe(fn() => $prodUnits  ? Arr::random($prodUnits)  : null);
						$accountId  = Arr::random($bankAccounts); // obrigatório

						$currency = $bill->currency_id ?: 'BRL';

						// número de parcelas
						$numberOfInstallments = $maybe(fn() => fake()->numberBetween(1, 6));
						$currentInstallment   = $numberOfInstallments
							? fake()->numberBetween(1, $numberOfInstallments)
							: null;

						// auditoria
						$creator = $maybe(fn() => $users ? Arr::random($users) : null);
						$updater = $maybe(fn() => $users ? Arr::random($users) : null);

						// Linha COMPLETA e consistente
						$row = [
							'id'                   => (string) Str::uuid(),
							'code'                 => (string) Str::uuid(),

							BC::COL_BL_ID          => $bill->id,
							BC::COL_OD_ID          => $orderId,

							// emissão financeira (HasFinancialIssuingColumns)
							BC::COL_CUR_ID         => $currency,
							'amount'               => $amount,
							'discount'             => $discount,
							BC::COL_SVC_FEE        => $svcFee ?? 0.00,
							BC::COL_TXS_FEE        => $taxFee ?? 0.00,
							'reference'            => $maybe(fn() => 'REF-' . strtoupper(Str::random(8))),
							'description'          => 'Pagamento de título gerado pelo seeder',
							'notes'                => $maybe(fn() => fake()->realText(160)),
							'attachments'          => $json($attachments),
							BC::COL_TC             => $json($terms),
							BC::COL_AUTORCC        => $maybe(fn() => fake()->boolean(30)) ?? false,
							BC::COL_RCC_RL         => $json($reconcileRules),
							'contract'             => $maybe(fn() => null), // se quiser popular, use contracts
							'loan'                 => $maybe(fn() => null), // idem loans
							BC::COL_PRD_SV_UNT     => $unitId,

							// pagamento (HasPaymentColumns)
							BC::COL_IS_SCD         => $maybe(fn() => fake()->boolean()) ?? false,
							BC::COL_CAN_CHG_BK     => $maybe(fn() => fake()->boolean()) ?? false,
							BC::COL_PPS_CD         => $ppsCode,
							BC::COL_TRF_TP         => $trfType,
							BC::COL_PPS_DS         => $maybe(fn() => fake()->sentence(6)),
							BC::COL_TXS_LST        => $json($taxesList),
							BC::COL_PAY_MTD        => $methodCode,
							BC::COL_PAY_MTD_LB     => $methodLabel,
							'status'               => $status,
							BC::COL_N_INTR         => $numberOfInstallments ?? 1,
							BC::COL_CURR_N_INTR    => $currentInstallment ?? 1,
							BC::COL_RCC_AT         => $reconciledAt,
							BC::COL_RCC_BY         => $reconciledBy,
							'invoice'              => $invoiceId,
							'payslip'              => $payslipId,

							// conclusão (HasPaymentConclusionColumns)
							'date'                 => $date->toDateString(),
							BC::COL_RCP_MD         => $json($receiptMeta),
							BC::COL_BACC_ID        => $accountId,
							BC::COL_CAT_ID         => $categoryId,
							BC::COL_ADD_RCP        => $maybe(fn() => fake()->boolean(20) ? 'yes' : null),

							// tipo de pagamento
							BC::COL_PAY_TP         => $paymentType,

							// auditoria (HasNullableAuditColumns)
							DC::COL_TABLE_CREATOR  => $creator,
							DC::COL_TABLE_UPDATER  => $updater,
							'created_at'           => $createdAt->toDateTimeString(),
							'updated_at'           => $updatedAt->toDateTimeString(),
						];

						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Pagamento de Conta relacionada a Conta a Pagar {$bill->id} e Pedido {$orderId} com método {$methodLabel} no valor de {$amount}");

						DB::table(DC::TABLE_BL_PAY)->insert($row);
						$inserted++;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			DB::commit();
			$this->command?->info("BillPaymentSeeder: {$inserted} pagamentos inseridos em " . DC::TABLE_BL_PAY . '.');
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('BillPaymentSeeder falhou: ' . $e->getMessage());
			throw $e;
		}
	}
}
