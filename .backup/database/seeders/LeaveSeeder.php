<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Models\{
	Employee,
	Leave,
	LeaveType
};
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class LeaveSeeder extends Seeder
{
	use EnsuresSystemUser;
	private $clock = 0;
	// private const SECONDS_LIMIT = 4 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	public function run(): void
	{
		DB::transaction(function (): void {
			$this->clock = microtime(true);
			$tz           = 'America/Sao_Paulo';
			$today        = CarbonImmutable::now($tz)->startOfDay();
			$faker        = \Faker\Factory::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			// 1) Garantir LeaveTypes disponíveis (fallback seguro)
			if (LeaveType::query()->count() === 0) {
				try {
					// tenta executar o seeder oficial, se existir
					(new \Database\Seeders\LeaveTypeSeeder())->run();
				} catch (\Throwable $e) {
					Log::warning('LeaveSeeder: LeaveTypeSeeder indisponível, criando tipo mínimo.', [
						'error' => $e->getMessage(),
					]);
					LeaveType::query()->updateOrCreate(
						['title' => 'Férias'],
						[
							'days'                                    => 30,
							PJC::COL_EXT_DY                           => 0,
							'paid'                                    => true,
							PJC::COL_HLT_RL                           => false,
							PJC::COL_SL_MIN_DD_PCT                    => 0,
							PJC::COL_SL_MAX_DD_PCT                    => 0,
							'description'                             => 'Tipo mínimo gerado automaticamente.',
							'categories'                              => ['administrativo'],
							'conditions'                              => [],
							'attachments'                             => [],
							DC::COL_TABLE_CREATOR                          => $systemUserId,
						]
					);
				}
			}

			// 2) Selecionar colaboradores
			$employees = Employee::query()
				->select(['id'])
				->inRandomOrder()
				->limit(256)
				->get();

			if ($employees->isEmpty()) {
				Log::info('LeaveSeeder: nenhum Employee encontrado — nada a semear.');
				return;
			}

			$leaveTypes = LeaveType::query()->get(['id', 'title', 'days', PJC::COL_EXT_DY, 'paid', PJC::COL_SL_MIN_DD_PCT, PJC::COL_SL_MAX_DD_PCT]);
			if ($leaveTypes->isEmpty()) {
				Log::warning('LeaveSeeder: nenhum LeaveType disponível após tentativa de fallback.');
				return;
			}

			// Status válidos de acordo com ProjectsConstants::$projectStatus
			$validStatuses = array_keys(PJC::$projectStatus ?? []) ?: [PJC::STT_INP_K, PJC::STT_ONH_K, PJC::STT_CPT_K, PJC::STT_CCL_K];

			$created = 0;
			$updated = 0;

			foreach ($employees as $emp) {
				// Cada colaborador recebe 1–3 afastamentos coerentes
				$count = random_int(1, 3);

				for ($i = 0; $i < $count; $i++) {
					try {
						/** @var LeaveType $lt */
						if ((microtime(true) - $this->clock) > self::SECONDS_LIMIT) {
							Log::warning('LeaveSeeder: tempo de execução excedeu limite seguro, abortando semeadura restante.');
							return;
						}
						$lt = $leaveTypes->random();
						$ref = $emp instanceof Employee ? ($emp->name ?? $emp->id) : (Employee::query()->where('id', $emp)->value('name') ?? $emp);
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Licença de {$lt->title} para {$ref}");

						$baseDays = max(0, (int) ($lt->days ?? 0));
						$extDays  = max(0, (int) ($lt->{PJC::COL_EXT_DY} ?? 0));
						$allowed  = max(1, $baseDays + $extDays);

						// janelas futuras realistas
						$start = $today->addDays(random_int(0, 30));
						$span  = random_int(1, $allowed); // respeita limite
						$end   = $start->addDays($span - 1);

						// desconto conforme política do tipo
						$minPct = (int) ($lt->{PJC::COL_SL_MIN_DD_PCT} ?? 0);
						$maxPct = (int) ($lt->{PJC::COL_SL_MAX_DD_PCT} ?? ($lt->paid ? 0 : 100));
						$minPct = max(0, min(100, $minPct));
						$maxPct = max(0, min(100, $maxPct));
						if ($minPct > $maxPct) $minPct = $maxPct;

						$discount = $lt->paid ? 0 : random_int($minPct, $maxPct);

						// status coerente com datas (afastamentos futuros raramente "complete")
						$status = $validStatuses[random_int(0, count($validStatuses) - 1)];
						if ($start->greaterThan($today) && $status === PJC::STT_CPT_K) {
							$status = PJC::STT_INP_K;
						}

						$reason = $faker->randomElement([
							'Motivos pessoais',
							'Acompanhamento médico',
							'Compromissos legais',
							'Treinamento interno',
							'Mudança de residência',
							'Convalescença',
						]);

						$uniqueKey = [
							'employee_id'             => $emp->id,
							CC::COL_LV_TP_ID          => $lt->id,
							PJC::COL_S_DT             => $start->toDateString(),
						];

						$payload = [
							PJC::COL_APL_ON           => $today->toDateString(),
							PJC::COL_E_DT             => $end->toDateString(),
							PJC::COL_TT_LV_DY         => (string) $span,
							PJC::COL_LV_RS            => $reason,
							'remark'                  => $faker->optional(0.5)->sentence(),
							PJC::COL_STATUS           => $status,
							'discount'                => $discount,
							'attachments'             => [
								// exemplo de metadado de anexo; o Model normaliza arrays
								['type' => 'pdf', 'label' => 'Comprovante', 'required' => false],
							],
							'conditions'              => [
								'aceite_do_gestor' => $faker->boolean(80),
								'pode_ser_remoto'  => $faker->boolean(40),
							],
							DC::COL_TABLE_CREATOR         => $systemUserId,
						];

						// Upsert reexecutável
						$instance = Leave::query()->where($uniqueKey)->first();

						if ($instance) {
							$instance->fill($payload)->save();
							$updated++;
						} else {
							Leave::create($uniqueKey + $payload);
							$created++;
						}
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}

			Log::info("LeaveSeeder: created={$created}, updated={$updated}");
		});
	}
}
