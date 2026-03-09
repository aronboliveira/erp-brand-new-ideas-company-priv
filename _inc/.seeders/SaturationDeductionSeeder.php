<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{DeductionOption, Employee, SaturationDeduction};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class SaturationDeductionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$employees = Employee::query()->select('id')->get();
			if ($employees->isEmpty()) {
				Log::info('SaturationDeductionSeeder: nenhum employee encontrado.');
				return;
			}

			$titles = [
				'Desconto por falta injustificada',
				'Atraso recorrente',
				'Adiantamento não quitado',
				'Penalidade por conduta',
				'Ajuste corretivo',
			];

			$created = 0;
			$updated = 0;

			foreach ($employees as $emp) {
				try {
					// 0..2 deduções por empregado
					$qty = random_int(0, 32);
					if ($qty === 0) {
						continue;
					}

					$picked = collect($titles)->shuffle()->take($qty);

					foreach ($picked as $title) {
						$ref = $emp instanceof Employee ? ($emp->name ?? $emp->id) : (Employee::query()->where('id', $emp)->value('name') ?? $emp);
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Dedução Saturada para Funcionário {$ref}");
						$usePercentage = (random_int(0, 1) === 1);
						$type = $usePercentage ? 'percentage' : 'fixed';

						// Valor plausível; o model normaliza e aplica limites se houver DeductionOption
						$amount = $usePercentage
							? (float) random_int(1, 30)      // 1..30%
							: (float) random_int(50, 1500);  // R$ 50..1500

						// Opcionalmente vincula uma DeductionOption existente
						$optId = null;
						if (random_int(0, 1) === 1) {
							$opt = DeductionOption::query()->inRandomOrder()->select('id')->first();
							if ($opt) {
								$optId = $opt->id;
							}
						}

						// Idempotência: chave natural [employee_id, title]
						$model = SaturationDeduction::updateOrCreate(
							[UC::COL_EMP_ID => $emp->id, 'title' => $title],
							[
								UC::COL_EMP_ID    => $emp->id,
								BC::COL_DD_OPT    => $optId,
								'title'           => $title,
								'amount'          => $amount,
								'type'            => $type,            // 'fixed' | 'percentage'
								DC::COL_TABLE_CREATOR => $systemUserId,
							]
						);

						$model->wasRecentlyCreated ? $created++ : $updated++;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("SaturationDeductionSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
