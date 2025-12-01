<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\{PaymentMethod, PaymentStatus, TransferType};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaymentSeeder extends Seeder
{
	private const TOTAL = 40;

	public function run(): void
	{
		$faker = \Faker\Factory::create('pt_BR');

		DB::transaction(function () use ($faker) {
			$bankAccIds = $this->idPool(DC::TABLE_BANK_ACC);
			$coaIds     = $this->idPool(DC::TABLE_COAS);
			$vendorIds  = $this->idPool(DC::TABLE_VENDORS);
			$catIds     = $this->idPool(DC::TABLE_PROD_SERV_CATS);
			$userIds    = $this->idPool(DC::TABLE_USERS);

			$created = 0;
			$failed = 0;

			for ($i = 0; $i < self::TOTAL; $i++) {
				// Datas coerentes
				$createdAt  = Carbon::now()->subDays(random_int(0, 90))->setTime(random_int(8, 20), random_int(0, 59));
				$date       = $createdAt->toDateString();

				// Método, status e tipo de transferência
				$method  = $this->pick(PaymentMethod::values());
				$status  = $this->pick(PaymentStatus::values());
				$trfType = $this->pick(TransferType::values());

				// Montantes
				$principal = $this->money(random_int(10_00, 9_000_00)); // R$10–R$9.000
				$interest  = $this->money(random_int(0, 400_00));      // R$0–R$400
				$discount  = $this->money(random_int(0, 300_00));      // R$0–R$300
				$svcFee    = $this->money(random_int(0, 150_00));      // R$0–R$150
				$taxFee    = $this->money(random_int(0, 200_00));      // R$0–R$200

				// Parcelas (apenas quando fizer sentido)
				$nInstallments   = in_array($method, [PaymentMethod::CardCredit->value], true)
					? $this->pick([1, 2, 3, 4, 6, 8, 10, 12])
					: 1;
				$currInstallment = $nInstallments > 1 ? random_int(1, $nInstallments) : 1;

				// Contas origem/destino (evita repetir a mesma)
				$fromAcc = $this->maybe($bankAccIds);
				$toAcc   = $this->maybe($bankAccIds);
				if ($fromAcc !== null && $toAcc === $fromAcc && count($bankAccIds) > 1) {
					// escolhe outra conta diferente
					do {
						$toAcc = $this->maybe($bankAccIds);
					} while ($toAcc === $fromAcc);
				}

				// Rótulos legíveis
				$methodLabel = (PaymentMethod::tryFrom($method) ?? PaymentMethod::Other)->label();
				$statusLabel = (PaymentStatus::tryFrom($status) ?? PaymentStatus::Undefined)->labels();

				// Recorrência e agendamentos
				$recurring   = $this->pick([null, 'monthly', 'weekly', 'bimonthly', null, null]);
				$scheduledTs = $recurring ? (clone $createdAt)->addDays($this->pick([7, 15, 30])) : null;

				// Flags de segurança/chargeback
				$isSecured      = $this->pick([true, false, false]); // maioria false
				$canChargeBack  = in_array($method, [PaymentMethod::CardCredit->value, PaymentMethod::CardDebit->value], true);

				// Impostos detalhados (JSON)
				$taxList = [];
				foreach ($this->pickMany([['ISS', 5], ['ICMS', 12], ['PIS', 1.65], ['COFINS', 7.6]], random_int(1, 3)) as [$taxName, $rate]) {
					$amount = round(($rate / 100) * ($principal / 100), 2);
					$taxList[] = ['name' => $taxName, 'rate' => $rate, 'amount' => $amount];
				}

				// Metadados de comprovante (JSON)
				$rcpMeta = [
					'gateway'         => $this->pick(['internal', 'stripe', 'pagarme', 'cielo', 'stone']),
					'transaction_id'  => strtoupper(Str::random(12)),
					'receipt_url'     => 'https://example.test/r/' . Str::random(10),
				];

				// Datas por status
				$executedAt  = in_array($status, [PaymentStatus::Processing->value, PaymentStatus::Completed->value, PaymentStatus::Authorized->value], true)
					? (clone $createdAt)->addMinutes(random_int(1, 120)) : null;
				$cancelledAt = in_array($status, [PaymentStatus::Cancelled->value], true)
					? (clone $createdAt)->addMinutes(random_int(5, 240)) : null;
				$completedAt = in_array($status, [PaymentStatus::Completed->value], true)
					? (clone $createdAt)->addMinutes(random_int(3, 180)) : null;

				// Billing (normalizável no modelo)
				$blName  = $faker->company();
				$blMail  = $faker->companyEmail();
				$blTel   = '+55 ' . $faker->areaCode . ' ' . $faker->cellphone(false);
				$blZip   = $faker->postcode();
				$blAddr  = $faker->streetAddress();
				$blCity  = $faker->city();
				$blState = $faker->stateAbbr();
				$blCtr   = 'BR';
				$blDtl   = $faker->optional(0.3)->sentence();

				$data = [
					'id'             => (string) Str::uuid(),
					'date'           => $date,
					'discount'       => $discount / 100,
					'recurring'      => $recurring,

					// FKs opcionais
					BC::COL_BACC_ID  => $this->maybe($bankAccIds), // conta "principal" relacionada
					BC::COL_ACC_TO   => $toAcc,
					BKC::COL_COA     => $this->maybe($coaIds),
					UC::COL_VD_ID    => $this->maybe($vendorIds),
					BC::COL_CAT_ID   => $this->maybe($catIds),

					// Colunas de pagamento (trait HasPaymentColumns)
					BC::COL_PAY_MTD      => $method,
					BC::COL_PAY_STT      => $status,
					BC::COL_STT_LB       => $statusLabel,
					BC::COL_PAY_MTD_LB   => $methodLabel,
					BC::COL_ACC_FROM     => $fromAcc,
					BC::COL_SVC_FEE      => $svcFee / 100,
					BC::COL_TXS_FEE      => $taxFee / 100,
					BC::COL_TXS_LST      => $taxList,
					BC::COL_SCHD_TRF_TS  => $scheduledTs,
					BC::COL_IS_SCD       => $isSecured,
					BC::COL_CAN_CHG_BK   => $canChargeBack,
					BC::COL_EXC_AT       => $executedAt,
					BC::COL_CNC_AT       => $cancelledAt,
					BC::COL_CMP_AT       => $completedAt,
					BC::COL_CNC_RS       => $cancelledAt ? $this->pick(['user_request', 'fraud_suspected', 'insufficient_funds']) : null,
					BC::COL_PPS_CD       => $this->pick(['SRV', 'PRC', 'INV', 'SAL', 'TAX']),
					BC::COL_TRF_TP       => $trfType,
					BC::COL_PPS_DS       => $this->pick(['Service fee', 'Product purchase', 'Invoice payment', 'Salary', 'Taxes', 'Transfer']),

					BC::COL_PRC_AMT      => $principal / 100,
					BC::COL_INTR_AMT     => $interest / 100,
					BC::COL_N_INTR       => $nInstallments,
					BC::COL_CURR_N_INTR  => $currInstallment,
					BC::COL_SL_PRC       => $this->maybeMoney(),
					BC::COL_PC_PRC       => $this->maybeMoney(),

					// Comprovante
					BC::COL_ADD_RCP      => null,     // string livre (mantido nulo)
					BC::COL_RCP_MD       => $rcpMeta, // JSON

					// Status "externo" (coluna 'status' do modelo)
					'status'             => $status,

					// Billing
					BC::COL_BL_NAME  => $blName,
					BC::COL_BL_EMAIL => $blMail,
					BC::COL_BL_TEL   => $blTel,
					BC::COL_BL_ZIP   => $blZip,
					BC::COL_BL_ADR   => $blAddr,
					BC::COL_BL_ST    => $blState,
					BC::COL_BL_CTY   => $blCity,
					BC::COL_BL_CTR   => $blCtr,
					BC::COL_BL_DTL   => $blDtl,

					// Rastreamento de falhas (preenche apenas em estados problemáticos)
					DC::COL_FL_AT      => in_array($status, [PaymentStatus::Failed->value, PaymentStatus::Declined->value], true) ? (clone $createdAt)->addMinutes(random_int(1, 60)) : null,
					DC::COL_FLD_RS     => in_array($status, [PaymentStatus::Failed->value, PaymentStatus::Declined->value, PaymentStatus::Disputed->value], true) ? $this->pick(['timeout', 'insufficient_funds', 'gateway_error', 'dispute']) : null,
					DC::COL_RTR_CT     => in_array($status, [PaymentStatus::Failed->value, PaymentStatus::Processing->value], true) ? random_int(0, 3) : 0,
					DC::COL_LST_RTR_AT => in_array($status, [PaymentStatus::Failed->value, PaymentStatus::Processing->value], true) ? (clone $createdAt)->addMinutes(random_int(2, 180)) : null,
					DC::COL_ER_LG      => in_array($status, [PaymentStatus::Failed->value], true) ? [['code' => 'GW-' . random_int(100, 999), 'msg' => 'Gateway error']] : null,

					// Auditoria mínima
					DC::COL_TABLE_CREATOR => $this->maybe($userIds),
					DC::COL_TABLE_UPDATER => $this->maybe($userIds),
					DC::COL_C_AT      => $createdAt,
					DC::COL_U_AT      => $createdAt->copy()->addMinutes(random_int(5, 400)),
				];

				try {
					DB::table(DC::TABLE_PAY)->insert($data);
					$created++;
				} catch (\Throwable $e) {
					$failed++;
					Log::warning(self::class . ' failed to insert Payment row', [
						'error'   => $e->getMessage(),
						'method'  => $method,
						'status'  => $status,
						'trfType' => $trfType,
					]);
				}
			}

			Log::info(self::class . " finished: created={$created}, failed={$failed}");
		});
	}

	/** @return array<int,string> */
	private function idPool(string $table): array
	{
		return array_values(array_map('strval', DB::table($table)->pluck('id')->all()));
	}

	/** Escolhe um item de um array não-vazio. */
	private function pick(array $options)
	{
		return $options[array_rand($options)];
	}

	/** Retorna item aleatório ou null se pool vazio. */
	private function maybe(array $pool): ?string
	{
		if (!$pool) return null;
		return $pool[array_rand($pool)];
	}

	/** Montante em centavos, como float com 2 casas. */
	private function money(int $cents): int
	{
		return max(0, $cents);
	}

	/** Valor monetário opcional, como float de 2 casas ou null. */
	private function maybeMoney(): ?float
	{
		return $this->pick([null, null, round($this->money(random_int(500, 20_000)) / 100, 2)]);
	}

	/**
	 * Retorna N itens distintos aleatórios de um array base.
	 * @param array<int, mixed> $base
	 * @return array<int, mixed>
	 */
	private function pickMany(array $base, int $n): array
	{
		$n = max(1, min($n, count($base)));
		$keys = array_rand($base, $n);
		if (!is_array($keys)) $keys = [$keys];
		return array_values(array_intersect_key($base, array_flip($keys)));
	}
}
