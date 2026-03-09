<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Models\{Employee, Travel};
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class TravelSeeder extends Seeder
{
	use EnsuresSystemUser;

	/**
	 * Quantas viagens no máximo por colaborador nesta carga.
	 * Ajuste conforme necessário.
	 */
	private const MAX_TRIPS_PER_EMPLOYEE = 32;

	/**
	 * Janela temporal (dias) para datas de início.
	 * Serão sorteadas entre hoje-180 e hoje+60.
	 */
	private const PAST_DAYS  = 180;
	private const FUTURE_DAYS = 60;
	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$clock = microtime(true);
			$systemUserId = $this->ensureSystemUser();

			// Funcionários ativos (se seu modelo usar outra coluna/status, ajuste aqui)
			$employees = Employee::query()
				->where(UC::COL_IA, 1)
				->pluck('id')
				->all();

			if (empty($employees)) {
				Log::notice('TravelSeeder: nenhum funcionário elegível encontrado. Abortando.');
				return;
			}

			$purposes = [
				'Reunião com cliente',
				'Instalação/Implantação',
				'Treinamento',
				'Auditoria',
				'Entrega/Coleta',
				'Suporte técnico',
				'Visita comercial',
				'Vistoria de infraestrutura',
			];
			$hardCap = 3200;
			foreach ($employees as $empId) {
				if (! $hardCap || $hardCap <= 0)
					break;
				$count = random_int(0, self::MAX_TRIPS_PER_EMPLOYEE);
				if ($count === 0) {
					continue;
				}

				for ($i = 0; $i < $count; $i++) {
					if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
						Log::info('TravelSeeder: limite de tempo atingido, encerrando carga antecipadamente.');
						return;
					}
					if (!$hardCap || $hardCap <= 0)
						return;
					$hardCap--;
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Viagem ou Dispensa para funcionário ID: {$empId}");
					try {
						// Datas coerentes no fuso de São Paulo
						$now   = now('America/Sao_Paulo');
						$start = $now
							->subDays(random_int(0, self::PAST_DAYS))
							->addDays(random_int(0, self::FUTURE_DAYS));
						// duração de 1 a 14 dias
						$end   = $start->addDays(random_int(1, 14));

						// Local e propósito
						$city   = $faker->city();
						$state  = $faker->state(); // evita depender de stateAbbr no locale
						$place  = "{$city}/{$state}";
						$purpose = $faker->randomElement($purposes);

						// Evita duplicar registros iguais para o mesmo colaborador na mesma data/local/propósito
						$exists = Travel::query()
							->where(UC::COL_EMP_ID, $empId)
							->where(PJC::COL_S_DT, $start->format('Y-m-d'))
							->where(PJC::VST_PLC, $place)
							->where(PJC::VST_PPS, $purpose)
							->exists();

						if ($exists) {
							continue;
						}

						$t = new Travel();
						$t->{UC::COL_EMP_ID}  = $empId;
						$t->{PJC::COL_S_DT}   = $start->format('Y-m-d');
						$t->{PJC::COL_E_DT}   = $end->format('Y-m-d');
						$t->{PJC::VST_PLC}    = $place;
						$t->{PJC::VST_PPS}    = $purpose;
						$t->description       = $faker->boolean(55) ? $faker->sentence(12) : null;
						// a coluna "notes" existe na migração; set por atribuição direta (fora do fillable)
						$t->notes             = $faker->boolean(35) ? $faker->paragraph() : null;

						// Auditoria
						$t->{DC::COL_TABLE_CREATOR} = $systemUserId;

						try {
							$t->save();
						} catch (\Throwable $e) {
							Log::warning('TravelSeeder: falha ao salvar viagem', [
								'employee_id' => $empId,
								'start_date'  => $t->{PJC::COL_S_DT},
								'place'       => $t->{PJC::VST_PLC},
								'purpose'     => $t->{PJC::VST_PPS},
								'error'       => $e->getMessage(),
							]);
						}
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
