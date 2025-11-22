<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{Commission, Employee};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class CommissionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$employees = Employee::query()->select(['id'])->get();
			if ($employees->isEmpty()) {
				Log::info('CommissionSeeder: nenhum employee encontrado.');
				return;
			}

			$titles = [
				'Comissão de Vendas',
				'Comissão por Indicação',
				'Bônus de Projeto',
				'Incentivo Comercial',
				'Comissão de Renovação',
			];

			$created = 0;
			$updated = 0;

			foreach ($employees as $emp) {
				// 0..2 comissões por funcionário
				$qty = random_int(0, 2);
				if ($qty === 0) {
					continue;
				}

				// Embaralha possíveis títulos para este funcionário
				$picked = collect($titles)->shuffle()->take($qty);

				foreach ($picked as $title) {
					// Escolhe tipo (fixed|percentage)
					$type = (random_int(0, 1) === 1) ? 'percentage' : 'fixed';

					// Gera amount coerente com o tipo
					if ($type === 'percentage') {
						// 3%..18%
						$amount = (float) random_int(3, 18);
					} else {
						// R$ 100,00 .. R$ 2.500,00
						$amount = (float) (random_int(100, 2500));
					}

					// Sanitiza defensivamente
					$amount = max(0.0, $amount);
					if ($type === 'percentage') {
						$amount = min(100.0, $amount);
					}

					// Evita duplicidade por employee+title
					$model = Commission::updateOrCreate(
						[UC::COL_EMP_ID => $emp->id, 'title' => $title],
						[
							UC::COL_EMP_ID    => $emp->id,
							'title'           => $title,
							'type'            => $type,
							'amount'          => $amount,
							DC::TABLE_CREATOR => $systemUserId,
						]
					);

					$model->wasRecentlyCreated ? $created++ : $updated++;
				}
			}

			Log::info("CommissionSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
