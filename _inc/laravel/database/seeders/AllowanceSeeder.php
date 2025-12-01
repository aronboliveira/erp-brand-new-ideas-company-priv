<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AllowanceType;
use App\Models\{Allowance, AllowanceOption, Employee};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class AllowanceSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$employees = Employee::query()->select(['id'])->get();
			if ($employees->isEmpty()) {
				Log::info('AllowanceSeeder: nenhum employee encontrado.');
				return;
			}

			$options = AllowanceOption::query()
				->select(['id', 'name'])
				->get();

			$created = 0;
			$updated = 0;

			foreach ($employees as $emp) {
				// cada funcionário pode receber de 1 a 3 allowances
				$count = random_int(1, 3);

				// embaralha opções disponíveis (se houver)
				$pick = $options->shuffle()->take($count);

				// se não há opções cadastradas, cria registros “genéricos”
				if ($pick->isEmpty()) {
					for ($i = 0; $i < $count; $i++) {
						$type = [AllowanceType::Fixed, AllowanceType::Percentage][array_rand([0, 1])];

						[$title, $amount] = $type === AllowanceType::Percentage
							? ['Benefício Percentual', random_int(5, 20)]                    // 5–20%
							: ['Benefício Fixo', round(random_int(80, 400) * 1.0, 2)];       // R$ 80–400

						$model = Allowance::updateOrCreate(
							[UC::COL_EMP_ID => $emp->id, 'title' => $title],
							[
								UC::COL_EMP_ID    => $emp->id,
								BC::COL_ALW_OPT   => null,
								'title'           => $title,
								'type'            => $type->value,
								'amount'          => $amount,
								DC::COL_TABLE_CREATOR => $systemUserId,
							]
						);

						$model->wasRecentlyCreated ? $created++ : $updated++;
					}

					continue;
				}

				// usa opções existentes
				foreach ($pick as $opt) {
					$type = [AllowanceType::Fixed, AllowanceType::Percentage][array_rand([0, 1])];

					[$title, $amount] = $type === AllowanceType::Percentage
						? [$opt->name . ' (%)', random_int(5, 20)]                         // 5–20%
						: [$opt->name . ' (Fixo)', round(random_int(120, 800) * 1.0, 2)];  // R$ 120–800

					$model = Allowance::updateOrCreate(
						[UC::COL_EMP_ID => $emp->id, 'title' => $title],
						[
							UC::COL_EMP_ID    => $emp->id,
							BC::COL_ALW_OPT   => $opt->id,
							'title'           => $title,
							'type'            => $type->value,
							'amount'          => $amount,
							DC::COL_TABLE_CREATOR => $systemUserId,
						]
					);

					$model->wasRecentlyCreated ? $created++ : $updated++;
				}
			}

			Log::info("AllowanceSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
