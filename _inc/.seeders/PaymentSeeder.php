<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\{PaymentMethod, PaymentStatus, TransferType};
use App\Helpers\ErrorHandler;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class PaymentSeeder extends Seeder
{
	private const PER_BANK_ACCOUNT = 32;
	// private const HARD_CAP = 2048;
	private const HARD_CAP = 8;
	// private const SECONDS_LIMIT = 6 * 10 ** 2;
	private const SECONDS_LIMIT = 120;
	private array $errorsData = [];
	public function run(): void
	{
		$clock = microtime(true);
		if (!Schema::hasTable(DC::TABLE_PAY)) {
			$this->command?->warn('Tabela de pagamentos ausente. Seeder abortado.');
			return;
		}

		$faker = \Faker\Factory::create('pt_BR');

		$bankAccIds   = $this->idPool(DC::TABLE_BANK_ACC);
		$coaIds       = $this->idPool(DC::TABLE_COAS);
		$vendorIds    = $this->idPool(DC::TABLE_VENDORS);
		$catIds       = $this->idPool(DC::TABLE_PROD_SERV_CATS);
		$userIds      = $this->idPool(DC::TABLE_USERS);
		$contractIds  = $this->idPool(DC::TABLE_CONTRACTS);
		$loanIds      = $this->idPool(DC::TABLE_LN);
		$unitIds      = $this->idPool(DC::TABLE_PROD_SERV_UNITS);
		$invoiceIds   = $this->idPool(DC::TABLE_INVS);
		$payslipIds   = $this->idPool(DC::TABLE_PAY_SLP);

		$base   = max(1, count($bankAccIds) ?: count($vendorIds) ?: 1);
		$target = self::PER_BANK_ACCOUNT * $base;

		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$target = $opt;
			}
		}

		$created = 0;
		$failed  = 0;

		DB::transaction(function () use (
			$faker,
			$bankAccIds,
			$coaIds,
			$vendorIds,
			$catIds,
			$userIds,
			$contractIds,
			$loanIds,
			$unitIds,
			$invoiceIds,
			$payslipIds,
			$target,
			&$created,
			&$failed,
			&$clock
		) {
			$taxUniverse = [
				['ISS', 5.0],
				['ICMS', 12.0],
				['PIS', 1.65],
				['COFINS', 7.6],
			];

			$statusCol = Schema::hasColumn(DC::TABLE_PAY, BC::COL_PAY_STT)
				? BC::COL_PAY_STT
				: 'status';
			$cap = self::HARD_CAP;
			$targetResult = min($target, $cap);
			for ($i = 0; $i < $target; $i++) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					break;
				}
				if ($cap <= 0 || !$cap) break;
				$cap--;
				try {
					$createdAt = Carbon::now()
						->subDays(random_int(0, 90))
						->setTime(random_int(8, 20), random_int(0, 59));

					$date = $faker->dateTimeBetween(
						$createdAt->copy()->subDays(730),
						$createdAt->copy()->addDays(730)
					)->format('Y-m-d');

					$methodEnum = $this->randomEnum(PaymentMethod::cases());
					$statusEnum = $this->randomEnum(PaymentStatus::cases());
					$trfEnum    = $this->randomEnum(TransferType::cases());

					$amount   = $this->money(random_int(1_000, 900_000));
					$discount = $this->money(random_int(0, 30_000));
					if ($discount > $amount) {
						$discount = $amount;
					}

					$svcFee = $this->money(random_int(0, 15_000));
					$taxFee = $this->money(random_int(0, 20_000));

					$nInstallments = $methodEnum->isCard()
						? $this->pick([1, 2, 3, 4, 6, 8, 10, 12])
						: 1;
					$currInstallment = $nInstallments > 1
						? random_int(1, $nInstallments)
						: 1;

					$fromAcc = $this->maybe($bankAccIds);
					$toAcc   = $this->maybe($bankAccIds);

					if ($fromAcc !== null && $toAcc === $fromAcc && count($bankAccIds) > 1) {
						do {
							$toAcc = $this->maybe($bankAccIds);
						} while ($toAcc === $fromAcc);
					}

					$recurring   = $this->pick([null, 'monthly', 'weekly', 'bimonthly', null]);
					$scheduledTs = $recurring
						? $createdAt->copy()->addDays($this->pick([7, 15, 30]))
						: null;

					$isSecured     = $this->pick([true, false, false]);
					$canChargeBack = $methodEnum->isCard();

					$taxList = [];
					foreach ($this->pickMany($taxUniverse, random_int(1, 3)) as [$taxName, $rate]) {
						$tAmount   = round(($rate / 100) * $amount, 2);
						$taxList[] = [
							'name'   => $taxName,
							'rate'   => $rate,
							'amount' => $tAmount,
						];
					}

					$rcpMeta = [
						'gateway'        => $this->pick(['internal', 'stripe', 'pagarme', 'cielo', 'stone']),
						'transaction_id' => strtoupper(Str::random(12)),
						'receipt_url'    => 'https://example.test/r/' . Str::random(10),
					];

					$executedAt = in_array(
						$statusEnum->value,
						[
							PaymentStatus::Processing->value,
							PaymentStatus::Completed->value,
							PaymentStatus::Authorized->value,
						],
						true
					) ? $createdAt->copy()->addMinutes(random_int(1, 120)) : null;

					$cancelledAt = $statusEnum === PaymentStatus::Cancelled
						? $createdAt->copy()->addMinutes(random_int(5, 240))
						: null;

					$completedAt = $statusEnum === PaymentStatus::Completed
						? $createdAt->copy()->addMinutes(random_int(3, 180))
						: null;

					$reconciledAt = $this->pick([null, $createdAt->copy()->addDays(random_int(1, 10))]);

					$blName  = $faker->company();
					$blMail  = $faker->companyEmail();
					$blTel   = '+55 ' . $faker->areaCode . ' ' . $faker->cellphone(false);
					$blZip   = $faker->postcode();
					$blAddr  = $faker->streetAddress();
					$blCity  = $faker->city();
					$blState = $faker->stateAbbr();
					$blCtr   = 'BR';
					$blDtl   = $faker->optional(0.3)->sentence();

					$failureStatusValues = [
						PaymentStatus::Failed->value,
						PaymentStatus::Declined->value,
						PaymentStatus::Disputed->value,
					];

					$failedLikeStatusValues = [
						PaymentStatus::Failed->value,
						PaymentStatus::Processing->value,
					];

					$failedAt = in_array($statusEnum->value, [
						PaymentStatus::Failed->value,
						PaymentStatus::Declined->value,
					], true)
						? $createdAt->copy()->addMinutes(random_int(1, 60))
						: null;

					$failureReason = in_array($statusEnum->value, $failureStatusValues, true)
						? $this->pick(['timeout', 'insufficient_funds', 'gateway_error', 'dispute'])
						: null;

					$retryCount = in_array($statusEnum->value, $failedLikeStatusValues, true)
						? random_int(0, 3)
						: 0;

					$lastRetryAt = in_array($statusEnum->value, $failedLikeStatusValues, true)
						? $createdAt->copy()->addMinutes(random_int(2, 180))
						: null;

					$errorLog = $statusEnum === PaymentStatus::Failed
						? [['code' => 'GW-' . random_int(100, 999), 'msg' => 'Gateway error']]
						: null;

					$reconcileRules = $this->pick([null, ['rule' => 'auto', 'strategy' => 'basic']]);

					$data = [
						'date'     => $date,
						'amount'   => $amount,
						'discount' => $discount,
						'recurring' => $recurring,

						BC::COL_CUR_ID  => 'BRL',

						BC::COL_BACC_ID => $fromAcc,
						BC::COL_ACC_TO  => $toAcc,
						BKC::COL_COA    => $this->maybe($coaIds),
						UC::COL_VD_ID   => $this->maybe($vendorIds),
						BC::COL_CAT_ID  => $this->maybe($catIds),

						BC::COL_SVC_FEE => $svcFee,
						BC::COL_TXS_FEE => $taxFee,
						'reference'     => $faker->optional(0.4)->bothify('REF-####-????'),
						'description'   => $faker->sentence(8),
						'notes'         => $faker->optional(0.3)->paragraph(),
						'attachments'   => $this->encodeJson([]),
						BC::COL_TC      => $this->encodeJson([]),

						BC::COL_AUTORCC => $this->pick([false, false, true]),
						BC::COL_RCC_RL  => $this->encodeJson($reconcileRules),
						BC::COL_RCC_AT  => $reconciledAt,
						BC::COL_RCC_BY  => $this->maybe($userIds),

						'contract'         => $this->maybe($contractIds),
						'loan'             => $this->maybe($loanIds),
						BC::COL_PRD_SV_UNT => $this->maybe($unitIds),

						BC::COL_IS_SCD      => $isSecured,
						BC::COL_CAN_CHG_BK  => $canChargeBack,
						BC::COL_PPS_CD      => $this->pick(['SRV', 'PRC', 'INV', 'SAL', 'TAX']),
						BC::COL_TRF_TP      => $trfEnum->value,
						BC::COL_PPS_DS      => $this->pick(['Service fee', 'Product purchase', 'Invoice payment', 'Salary', 'Taxes', 'Transfer']),
						BC::COL_TXS_LST     => $this->encodeJson($taxList),

						// tinyint "opaco"
						BC::COL_PAY_MTD     => random_int(0, 1),
						// enum real
						BC::COL_PAY_MTD_LB  => $methodEnum->value,

						BC::COL_N_INTR      => $nInstallments,
						BC::COL_CURR_N_INTR => $currInstallment,

						BC::COL_ADD_RCP     => null,
						BC::COL_RCP_MD      => $this->encodeJson($rcpMeta),

						$statusCol          => $statusEnum->value,
						BC::COL_SCHD_TRF_TS => $scheduledTs,
						BC::COL_EXC_AT      => $executedAt,
						BC::COL_CNC_AT      => $cancelledAt,
						BC::COL_CMP_AT      => $completedAt,
						BC::COL_CNC_RS      => $cancelledAt
							? $this->pick(['user_request', 'fraud_suspected', 'insufficient_funds'])
							: null,

						BC::COL_BL_NAME  => $blName,
						BC::COL_BL_EMAIL => $blMail,
						BC::COL_BL_TEL   => $blTel,
						BC::COL_BL_ZIP   => $blZip,
						BC::COL_BL_ADR   => $blAddr,
						BC::COL_BL_ST    => $blState,
						BC::COL_BL_CTY   => $blCity,
						BC::COL_BL_CTR   => $blCtr,
						BC::COL_BL_DTL   => $blDtl,

						DC::COL_FL_AT      => $failedAt,
						DC::COL_FLD_RS     => $failureReason,
						DC::COL_RTR_CT     => $retryCount,
						DC::COL_LST_RTR_AT => $lastRetryAt,
						DC::COL_ER_LG      => $this->encodeJson($errorLog),

						'invoice'          => $this->maybe($invoiceIds),

						DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
						DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
						DC::COL_C_AT          => $createdAt,
						DC::COL_U_AT          => $createdAt->copy()->addMinutes(random_int(5, 400)),
					];
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("({$i}/{$targetResult}) Criando Pagamento de {$fromAcc} para {$toAcc} com método {$methodEnum->value} no valor de {$amount}");
					try {
						Payment::query()->create($data);
						$created++;
					} catch (\Throwable $e) {
						$failed++;
						ErrorHandler::evaluateExistenceToLogChannel(
							'payment_seeder_errors',
							candidate: [
								'message' => self::class . ' failed to insert Payment row',
								'context' => [
									'error'   => $e->getMessage(),
									'method'  => $methodEnum->value,
									'status'  => $statusEnum->value,
									'trfType' => $trfEnum->value,
								],
							],
							seed: "from={$fromAcc},to={$toAcc},i={$i}"
						);
					}
				} catch (\Exception $e) {
					ErrorHandler::evaluateExistenceToLogChannel(
						'payment_seeder_errors',
						candidate: [
							'message' => self::class . ' failed during Payment seeding loop',
							'context' => [
								'error' => $e->getMessage(),
							],
						],
						seed: "i={$i}"
					);
					$failed++;
					continue;
				}
			}
			Log::info(self::class . " finished: created={$created}, failed={$failed}");
		});
	}

	/** @return array<int,string> */
	private function idPool(string $table): array
	{
		if (!Schema::hasTable($table)) {
			return [];
		}

		return array_values(array_map('strval', DB::table($table)->pluck('id')->all()));
	}

	private function pick(array $options)
	{
		return $options[array_rand($options)];
	}

	private function maybe(array $pool): ?string
	{
		if (!$pool) return null;
		return $pool[array_rand($pool)];
	}

	private function money(int $cents): float
	{
		return round(max(0, $cents) / 100, 2);
	}

	/**
	 * @template T of \BackedEnum
	 * @param array<int,T> $cases
	 * @return T
	 */
	private function randomEnum(array $cases)
	{
		return $cases[array_rand($cases)];
	}

	/**
	 * @param array<int, array{0:string,1:float}> $base
	 * @return array<int, array{0:string,1:float}>
	 */
	private function pickMany(array $base, int $n): array
	{
		$n = max(1, min($n, count($base)));
		$keys = array_rand($base, $n);
		if (!is_array($keys)) {
			$keys = [$keys];
		}
		return array_values(array_intersect_key($base, array_flip($keys)));
	}

	private function encodeJson(mixed $value): ?string
	{
		if ($value === null) {
			return null;
		}

		return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
}
