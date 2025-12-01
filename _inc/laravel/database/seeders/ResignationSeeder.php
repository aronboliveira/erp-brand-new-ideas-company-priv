<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{Employee, Resignation};
use App\Traits\EnsuresSystemUser;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class ResignationSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			// Já existentes (há UNIQUE em employee_id)
			$alreadyResigned = Resignation::query()
				->pluck(UC::COL_EMP_ID)
				->all();

			// Funcionários elegíveis (sem registro em resignations)
			$eligible = Employee::query()
				->whereNotIn('id', $alreadyResigned)
				->pluck('id')
				->all();

			if (empty($eligible)) {
				Log::notice('No eligible employees found for resignation seeding. Skipping.');
				return;
			}

			// Semeia ~20% dos elegíveis (mín. 3)
			$targetCount = max(3, (int) floor(count($eligible) * 0.20));
			$sample = collect($eligible)->shuffle()->take($targetCount);

			foreach ($sample as $empId) {
				// Defesa extra contra corrida
				if (Resignation::where(UC::COL_EMP_ID, $empId)->exists()) {
					continue;
				}

				// Data de aviso ∈ [hoje-6m, hoje], data efetiva ∈ [aviso+7, aviso+60]
				$notice = now('America/Sao_Paulo')->subDays(random_int(0, 180));
				$effective = (clone $notice)->addDays(random_int(7, 60));

				$r = new Resignation();
				$r->{UC::COL_EMP_ID}             = $empId;
				$r->{UC::COL_RESIGNATION_NDT}    = $notice->format('Y-m-d');
				$r->{UC::COL_RESIGNATION_DT}     = $effective->format('Y-m-d');
				$r->description                   = $faker->boolean(60) ? $faker->sentence(12) : null;
				$r->notes                         = $faker->boolean(30) ? $faker->paragraph() : null;

				// Guardado, mas permitido via atribuição direta antes do save
				$r->{DC::COL_TABLE_CREATOR} = $systemUserId;

				try {
					$r->save();
				} catch (\Throwable $e) {
					Log::warning('Failed to seed resignation', [
						'employee_id' => $empId,
						'error'       => $e->getMessage(),
					]);
				}
			}
		}, 3);
	}
}
