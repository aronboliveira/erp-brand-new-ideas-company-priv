<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{Employee, Termination, TerminationType};
use App\Traits\EnsuresSystemUser;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class TerminationSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			// funcionários já desligados (há unique em employee_id)
			$terminatedIds = Termination::query()
				->pluck(UC::COL_EMP_ID)
				->all();

			// candidatos a receber registro de desligamento
			$candidateIds = Employee::query()
				->whereNotIn('id', $terminatedIds)
				->pluck('id')
				->all();

			if (empty($candidateIds)) {
				Log::notice('No eligible employees found for termination seeding. Skipping.');
				return;
			}

			// tipos de desligamento existentes (pode ser null se inexistentes)
			$typeIds = TerminationType::query()->pluck('id')->all();

			// semear ~20% dos candidatos (mín. 3, máx. total)
			$targetCount = max(3, (int) floor(count($candidateIds) * 0.2));
			$pickIds = collect($candidateIds)->shuffle()->take($targetCount);

			foreach ($pickIds as $empId) {
				try {
					// segurança: revalida unicidade por funcionário
					if (Termination::where(UC::COL_EMP_ID, $empId)->exists()) {
						continue;
					}
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Demissão para funcionário ID: {$empId}");
					// datas coerentes: aviso ∈ [hoje-12m, hoje], desligamento ∈ [aviso+7, aviso+60]
					$noticeDate = now('America/Sao_Paulo')->subDays(random_int(0, 365));
					$termDate   = (clone $noticeDate)->addDays(random_int(7, 60));

					$termination              = new Termination();
					$termination->{UC::COL_EMP_ID}          = $empId;
					$termination->{UC::COL_TERMINATION_NDT} = $noticeDate->format('Y-m-d');
					$termination->{UC::COL_TERMINATION_DT}  = $termDate->format('Y-m-d');
					$termination->{UC::COL_TERMINATION_TP}  = $typeIds ? $faker->randomElement($typeIds) : null;
					$termination->description               = $faker->boolean(50) ? $faker->sentence(10) : null;

					// created_by é guarded — atribuição direta antes do save
					$termination->{DC::COL_TABLE_CREATOR} = $systemUserId;

					try {
						$termination->save();
					} catch (\Throwable $e) {
						// trata colisões do unique/concorrência e segue com o próximo
						Log::warning('Failed to seed termination for employee', [
							'employee_id' => $empId,
							'error'       => $e->getMessage(),
						]);
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
