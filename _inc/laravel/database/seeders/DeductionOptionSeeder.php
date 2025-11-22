<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{CalculationBase, Frequency, DeductionType};
use App\Models\DeductionOption;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class DeductionOptionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$rows = [
				[
					'name'            => 'INSS',
					'code'            => 'INSS',
					BC::COL_DD_TYPE   => DeductionType::Legal,
					BC::COL_CCL_BS    => CalculationBase::ContributionSalary,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 14,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'IRRF',
					'code'            => 'IRRF',
					BC::COL_DD_TYPE   => DeductionType::Legal,
					BC::COL_CCL_BS    => CalculationBase::ProgressiveTable,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 27,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'Contribuição Sindical',
					'code'            => 'SIND-001',
					BC::COL_DD_TYPE   => DeductionType::Syndical,
					BC::COL_CCL_BS    => CalculationBase::GrossSalary,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 2,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'Coparticipação Saúde',
					'code'            => 'HEALTH-CO',
					BC::COL_DD_TYPE   => DeductionType::Benefit,
					BC::COL_CCL_BS    => CalculationBase::SpecificAmount,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 50,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'Pensão Judicial',
					'code'            => 'PENSAO-JUD',
					BC::COL_DD_TYPE   => DeductionType::Judicial,
					BC::COL_CCL_BS    => CalculationBase::NetSalary,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 33,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'Empréstimo Consignado',
					'code'            => 'LOAN-CONS',
					BC::COL_DD_TYPE   => DeductionType::Loan,
					BC::COL_CCL_BS    => CalculationBase::Percentage,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 30,
					'frequency'       => Frequency::Monthly,
					BC::COL_MDAY_LMT  => 2,
				],

				[
					'name'            => 'Adiantamento Salarial',
					'code'            => 'ADVANCE-SAL',
					BC::COL_DD_TYPE   => DeductionType::Advance,
					BC::COL_CCL_BS    => CalculationBase::GrossSalary,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 40,
					'frequency'       => Frequency::Semimonthly,
					BC::COL_MDAY_LMT  => 2,
				],

				[
					'name'            => 'Doação Voluntária',
					'code'            => 'VOL-CHARITY',
					BC::COL_DD_TYPE   => DeductionType::Voluntary,
					BC::COL_CCL_BS    => CalculationBase::SpecificAmount,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 100,
					'frequency'       => Frequency::Once,
					BC::COL_MDAY_LMT  => 1,
				],

				[
					'name'            => 'Outras Deduções',
					'code'            => 'OTHER-DED',
					BC::COL_DD_TYPE   => DeductionType::Other,
					BC::COL_CCL_BS    => CalculationBase::Mixed,
					BC::COL_MIN_PCT   => 0,
					BC::COL_MAX_PCT   => 100,
					'frequency'       => Frequency::Variable,
					BC::COL_MDAY_LMT  => 12,
				],
			];

			$created = 0;
			$updated = 0;

			foreach ($rows as $data) {
				$key = !empty($data['code'])
					? ['code' => $data['code']]
					: ['name' => $data['name']];

				$model = DeductionOption::updateOrCreate(
					$key,
					array_merge($data, [DC::TABLE_CREATOR => $systemUserId])
				);

				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("DeductionOptionSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
