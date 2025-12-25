<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Enums\FinancialEstimationStatus;
use App\Models\Estimation;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class EstimationSeeder extends Seeder
{
	private const BASE_MULTIPLIER = 2;
	private const MAX_MULTIPLIER  = 16;

	public function run(): void
	{
		$faker = \Faker\Factory::create('pt_BR');

		foreach ([DC::TABLE_EST, DC::TABLE_CLIENTS] as $tbl)
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder abortado.");
				return;
			}

		$clientIds  = $this->idPool(DC::TABLE_CLIENTS);
		if (!$clientIds) {
			$this->command?->warn('Nenhum cliente encontrado; EstimationSeeder abortado.');
			return;
		}

		$projectIds     = $this->idPool(DC::TABLE_PROJECTS);
		$taxIds         = $this->idPool(DC::TABLE_TAXES);
		$categoryIds    = $this->idPool(DC::TABLE_PROD_SERV_CATS);
		$productUnitIds = $this->idPool(DC::TABLE_PROD_SERV_UNITS);
		$contractIds    = $this->idPool(DC::TABLE_CONTRACTS);
		$loanIds        = $this->idPool(DC::TABLE_LN);
		$userIds        = $this->idPool(DC::TABLE_USERS);
		$paymentIds     = $this->idPool(DC::TABLE_PAY);

		$created = 0;
		$failed  = 0;

		DB::transaction(function () use (
			$faker,
			$clientIds,
			$projectIds,
			$taxIds,
			$categoryIds,
			$productUnitIds,
			$contractIds,
			$loanIds,
			$userIds,
			$paymentIds,
			&$created,
			&$failed
		) {
			// For each client, create a random number of estimations
			foreach ($clientIds as $clientId) {
				$quadraticRange = (self::MAX_MULTIPLIER - self::BASE_MULTIPLIER + 1) ** 2;
				$estimationsForClient = self::MAX_MULTIPLIER - ((int)sqrt(random_int(1, $quadraticRange)) - 1);
				for ($i = 0; $i < $estimationsForClient; $i++) {
					try {
						$projectId = $this->maybe($projectIds);

						$issuedAt = Carbon::now()
							->subDays(random_int(0, 60))
							->setTime(random_int(8, 18), random_int(0, 59));
						$validTo  = $issuedAt->addDays(random_int(15, 45));
						$dueDate  = $issuedAt->addDays(random_int(7, 60));

						$status   = $this->randomStatus($validTo);
						$amount   = $faker->randomFloat(2, 500, 25000);
						$discountEligible = (bool) random_int(0, 1);
						$discount = $discountEligible
							? $faker->randomFloat(2, 0, max(0.0, $amount / 3))
							: 0.0;
						$svcFee   = $faker->randomFloat(2, 0, $amount * 0.05);
						$taxFee   = $faker->randomFloat(2, 0, $amount * 0.20);

						$taxes = [];
						$taxBase = [
							['ISS',    5.0],
							['ICMS',  12.0],
							['PIS',    1.65],
							['COFINS', 7.60],
						];
						foreach ($this->pickMany($taxBase, random_int(1, min(3, count($taxBase)))) as [$code, $rate]) {
							$tAmount = round(($rate / 100) * $amount, 2);
							$taxes[] = [
								'code'   => $code,
								'rate'   => $rate,
								'amount' => $tAmount,
							];
						}

						$billingName = $faker->company();
						$shipName    = $faker->company();

						$paymentsSubset = [];
						if ($paymentIds && random_int(0, 100) < 60) {
							$maxTake        = min(3, count($paymentIds));
							$paymentsSubset = $this->pickMany($paymentIds, random_int(1, $maxTake));
						}

						$issuedDate = $issuedAt->toDateString();
						do $estId = (string) Str::uuid();
						while (Estimation::where(BC::COL_EST_ID, $estId)->exists());
						$estimation = new Estimation([
							BC::COL_EST_ID       => $estId,
							PJC::COL_CLIENT_ID   => $clientId,
							PJC::COL_PJ_ID       => $projectId,
							BC::COL_TAX_ID       => $this->maybe($taxIds),

							'amount'             => $amount,
							'discount'           => $discount,
							BC::COL_CUR_ID       => 'BRL',
							BC::COL_SVC_FEE      => $svcFee,
							BC::COL_TXS_FEE      => $taxFee,
							'reference'          => 'EST-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
							'description'        => $faker->sentence(12),
							'notes'              => $faker->optional(0.35)->paragraph(),

							BC::COL_BL_NAME      => $billingName,
							BC::COL_BL_EMAIL     => $faker->companyEmail(),
							BC::COL_BL_TEL       => '+55 ' . $faker->areaCode . ' ' . $faker->cellphone(false),
							BC::COL_BL_ZIP       => $faker->postcode(),
							BC::COL_BL_ADR       => $faker->streetAddress(),
							BC::COL_BL_ST        => $faker->stateAbbr(),
							BC::COL_BL_CTY       => $faker->city(),
							BC::COL_BL_CTR       => 'BR',
							BC::COL_BL_DTL       => $faker->optional(0.3)->sentence(),

							BC::COL_SHIP_NAME    => $shipName,
							BC::COL_SHIP_EMAIL   => $faker->companyEmail(),
							BC::COL_SHIP_TEL     => '+55 ' . $faker->areaCode . ' ' . $faker->cellphone(false),
							BC::COL_SHIP_ZIP     => $faker->postcode(),
							BC::COL_SHIP_ADR     => $faker->streetAddress(),
							BC::COL_SHIP_ST      => $faker->stateAbbr(),
							BC::COL_SHIP_CTY     => $faker->city(),
							BC::COL_SHIP_CTR     => 'BR',
							BC::COL_SHIP_DTL     => $faker->optional(0.3)->sentence(),

							BC::COL_SD_DT        => $faker->optional(0.7)
								->dateTimeBetween(
									$issuedAt->subDays(5),
									$issuedAt->addDays(5)
								)
								?->format('Y-m-d'),
							PJC::COL_D_DATE      => $dueDate->toDateString(),
							BC::COL_CAT_ID       => $this->maybe($categoryIds),

							BC::COL_DSC_APL      => $discountEligible ? 1 : 0,
							'taxes'              => $taxes,

							'attachments'        => [],
							BC::COL_TC           => $faker->optional(0.4)->sentences(2, true),
							BC::COL_AUTORCC      => (bool) random_int(0, 1),
							BC::COL_RCC_RL       => $faker->optional(0.3)->randomElements(
								['auto_on_payment', 'threshold_5000', 'manual_review'],
								random_int(1, 2)
							),

							BC::COL_ISS_DT       => $issuedDate,
							BC::COL_REF_N        => $faker->optional(0.5)->bothify('REF-####-??'),
							BC::COL_VLD_TO       => $validTo,
							BC::COL_RQ_SIGN      => $faker->boolean(30),
							BC::COL_IS_SIGN      => $faker->boolean(20),
							BC::COL_SIGN_AT      => $faker->optional(0.25)
								->dateTimeBetween($issuedAt, $validTo),
							BC::COL_SIGN_BY      => $this->maybe($userIds),
							BC::COL_SIGN_BY_NAME => $faker->optional(0.5)->name(),
							BC::COL_VW_AT        => $faker->optional(0.8)
								->dateTimeBetween($issuedAt, $validTo),

							'payments'           => $paymentsSubset,
							'status'             => $status,

							'terms'              => $faker->optional(0.6)->paragraphs(2, true),

							'contract'           => $this->maybe($contractIds),
							'loan'               => $this->maybe($loanIds),
							BC::COL_PRD_SV_UNT   => $this->maybe($productUnitIds),
						]);

						$estimation->{DC::COL_TABLE_CREATOR} = $this->maybe($userIds);
						$estimation->{DC::COL_TABLE_UPDATER} = $this->maybe($userIds);
						$estimation->{DC::COL_C_AT}          = $issuedAt;
						$estimation->{DC::COL_U_AT}          = $issuedAt->addMinutes(random_int(10, 720));
						(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando relatório de Estimativa Financeira {$estId} para {$clientId} sobre{$projectId}");
						try {
							$estimation->save();
							$created++;
						} catch (\Throwable $e) {
							$failed++;
							Log::warning(self::class . ' failed to insert Estimation row', [
								'error'      => $e->getMessage(),
								'client_id'  => $clientId,
								'project_id' => $projectId,
								'status'     => (string) $status,
							]);
						}
					} catch (\Exception $e) {
						$failed++;
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					}
				}
			}
		});

		$avgPerClient = count($clientIds) > 0 ? round($created / count($clientIds), 1) : 0;
		$this->command?->info(self::class . " finished: created={$created}, failed={$failed}, clients=" . count($clientIds) . ", avg={$avgPerClient} per client");
	}

	/** @return array<int,string> */
	private function idPool(string $table): array
	{
		if (!Schema::hasTable($table)) {
			Log::warning(self::class . " idPool: tabela ausente: {$table}");
			return [];
		}

		return array_values(array_map(
			'strval',
			DB::table($table)->pluck('id')->all()
		));
	}

	/** @param array<int,mixed> $options */
	private function pick(array $options)
	{
		return $options[array_rand($options)];
	}

	/** @param array<int,string> $pool */
	private function maybe(array $pool): ?string
	{
		if (!$pool) return null;
		return $this->pick($pool);
	}

	/**
	 * @param array<int, mixed> $base
	 * @return array<int, mixed>
	 */
	private function pickMany(array $base, int $n): array
	{
		$n = max(1, min($n, count($base)));
		if ($n === 1) return [$this->pick($base)];

		$keys = array_rand($base, $n);
		if (!is_array($keys)) $keys = [$keys];

		return array_values(array_intersect_key($base, array_flip($keys)));
	}

	private function randomStatus(Carbon $validTo): FinancialEstimationStatus
	{
		$now = Carbon::now();

		if ($now->lte($validTo)) {
			$candidates = [
				FinancialEstimationStatus::Open,
				FinancialEstimationStatus::NotPaid,
				FinancialEstimationStatus::PartiallyPaid,
			];
		} else {
			$candidates = [
				FinancialEstimationStatus::NotPaid,
				FinancialEstimationStatus::PartiallyPaid,
				FinancialEstimationStatus::Paid,
				FinancialEstimationStatus::Cancelled,
			];
		}

		return $candidates[array_rand($candidates)];
	}
}
