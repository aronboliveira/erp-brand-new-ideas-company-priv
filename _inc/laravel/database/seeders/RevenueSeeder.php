<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransferType;
use App\Models\Revenue;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenueSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY = 0.55; // antes: REVENUE_SEED_OPTIONALITY

	/**
	 * Opções CLI:
	 *  --count=N   Quantidade de registros (padrão: 60)
	 */
	public function run(): void
	{
		$faker = fake();

		$count        = (int) ($this->command && $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count') ? $this->command?->option('count') : 64);
		$optionality  = self::OPTIONALITY;

		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};

		$maybe = function (callable $producer) use ($randBool, $optionality) {
			return $randBool((int) round($optionality * 100)) ? $producer() : null;
		};

		$pickId = function (string $table): ?string {
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};

		$tables = [
			'customer'     => DC::TABLE_CUSTOMERS,
			'user'         => DC::TABLE_USERS,
			'bankAccount'  => DC::TABLE_BANK_ACC,
			'category'     => DC::TABLE_PROD_SERV_CATS,
			'invoice'      => DC::TABLE_INVS,
			'payslip'      => DC::TABLE_PAY_SLP,
			'contract'     => DC::TABLE_CONTRACTS,
			'loan'         => DC::TABLE_LN,
			'unit'         => DC::TABLE_PROD_SERV_UNITS,
		];

		$customerIdSample = $pickId($tables['customer']);
		if (!$customerIdSample) {
			$this->command?->warn('RevenueSeeder: nenhuma linha em customers; nada foi gerado.');
			return;
		}

		DB::transaction(function () use (
			$faker,
			$count,
			$maybe,
			$randBool,
			$optionality,
			$pickId,
			$tables
		) {
			for ($i = 0; $i < $count; $i++) {
				$amount  = (float) $faker->randomFloat(2, 50, 20000);
				$svcFee  = $randBool(40) ? (float) $faker->randomFloat(2, 0, min($amount * 0.04, 150)) : 0.00;
				$taxFee  = $randBool(30) ? (float) $faker->randomFloat(2, 0, min($amount * 0.06, 300)) : 0.00;
				if ($svcFee + $taxFee > $amount) {
					$excess = ($svcFee + $taxFee) - $amount + 0.01;
					$taxFee = max(0, round($taxFee - $excess, 2));
				}

				$date = $randBool(70)
					? Carbon::now()->subDays($faker->numberBetween(0, 180))->format('Y-m-d')
					: Carbon::now()->format('Y-m-d');

				$status = $randBool(80)
					? Arr::random(PaymentStatus::values())
					: PaymentStatus::Pending->value;

				$reconciledAt = null;
				if ($randBool(25) || in_array($status, [
					PaymentStatus::Completed->value,
					PaymentStatus::Refunded->value,
					PaymentStatus::PartiallyRefunded->value,
				], true)) {
					$reconciledAt = Carbon::now()->subDays($faker->numberBetween(0, 60))
						->subMinutes($faker->numberBetween(0, 1440))
						->toDateTimeString();
				}

				$optionals = [
					'company'              => $maybe(fn() => $pickId($tables['user'])),
					'user'                 => $maybe(fn() => $pickId($tables['user'])),

					BC::COL_CUR_ID        => $maybe(fn() => Arr::random(['BRL', 'USD', 'EUR'])),
					BC::COL_SVC_FEE       => $svcFee ?: null,
					BC::COL_TXS_FEE       => $taxFee ?: null,
					BC::COL_PPS_CD        => $maybe(fn() => (string) $faker->numberBetween(100, 999)),
					'reference'           => $maybe(fn() => strtoupper($faker->bothify('RVN-########'))),
					'notes'               => $maybe(fn() => $faker->realText(160)),
					'attachments'         => $maybe(fn() => [$faker->imageUrl(128, 128, 'business', true)]),
					BC::COL_TC            => $maybe(fn() => [
						'late_fee_pct' => (float) $faker->randomFloat(2, 0, 10),
						'text'         => $faker->sentence(8),
					]),
					BC::COL_AUTORCC       => $maybe(fn() => $faker->boolean()),
					BC::COL_RCC_RL        => $maybe(fn() => [
						'strategy' => Arr::random(['strict', 'loose']),
						'window_d' => $faker->numberBetween(3, 15),
					]),

					BC::COL_IS_SCD        => $maybe(fn() => $faker->boolean()),
					BC::COL_CAN_CHG_BK    => $maybe(fn() => $faker->boolean()),
					BC::COL_TRF_TP        => $maybe(fn() => Arr::random(TransferType::values())),
					BC::COL_PPS_DS        => $maybe(fn() => $faker->sentence(6)),
					BC::COL_TXS_LST       => $maybe(fn() => [
						['code' => 'ISS', 'rate' => (float) $faker->randomFloat(2, 0, 5)],
						['code' => 'PIS', 'rate' => (float) $faker->randomFloat(2, 0, 2)],
					]),
					BC::COL_PAY_MTD       => $maybe(fn() => $faker->numberBetween(0, 1)),
					BC::COL_PAY_MTD_LB    => $maybe(fn() => Arr::random(\App\Enums\PaymentMethod::values())),
					'status'              => $status,
					BC::COL_N_INTR        => $maybe(function () use ($faker) {
						$n = $faker->numberBetween(1, 12);
						return $n;
					}),
					BC::COL_CURR_N_INTR   => null,
					BC::COL_RCC_AT        => $reconciledAt,
					BC::COL_RCC_BY        => $reconciledAt ? $pickId($tables['user']) : null,

					'invoice'             => $maybe(fn() => $pickId($tables['invoice'])),
					'payslip'             => $maybe(fn() => $pickId($tables['payslip'])),
					'contract'            => $maybe(fn() => $pickId($tables['contract'])),
					'loan'                => $maybe(fn() => $pickId($tables['loan'])),
					BC::COL_PRD_SV_UNT    => $maybe(fn() => $pickId($tables['unit'])),

					BC::COL_BACC_ID       => $maybe(fn() => $pickId($tables['bankAccount'])),
					BC::COL_CAT_ID        => $maybe(fn() => $pickId($tables['category'])),
					BC::COL_ADD_RCP       => $maybe(fn() => Arr::random(['pdf', 'xml', 'none'])),
					BC::COL_RCP_MD        => $maybe(fn() => [
						'number' => $faker->numerify('RCP-#####'),
						'url'    => $faker->url(),
					]),
				];

				if (!empty($optionals[BC::COL_N_INTR])) {
					$optionals[BC::COL_CURR_N_INTR] = $randBool(80)
						? $faker->numberBetween(1, (int) $optionals[BC::COL_N_INTR])
						: 1;
				}

				$payload = array_filter(array_merge([
					'id'                  => (string) Str::uuid(),
					'date'                => $date,
					BC::COL_CST_ID        => $pickId($tables['customer']),
					'description'         => $faker->sentence(10),
					'amount'              => round($amount, 2),
				], $optionals), fn($v) => $v !== null);

				if (!empty($payload[BC::COL_RCC_AT]) && in_array(($payload['status'] ?? ''), [
					PaymentStatus::Pending->value,
					PaymentStatus::Processing->value,
					PaymentStatus::Authorized->value,
					'',
				], true)) {
					$payload['status'] = PaymentStatus::Completed->value;
				}

				Revenue::query()->create($payload);
			}
		});
	}
}
