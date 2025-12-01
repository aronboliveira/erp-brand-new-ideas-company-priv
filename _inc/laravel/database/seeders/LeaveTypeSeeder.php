<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Models\LeaveType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class LeaveTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = \Faker\Factory::create('pt_BR');
			$tz           = 'America/Sao_Paulo';
			$systemUserId = $this->ensureSystemUser();

			// Catálogo base (reexecutável; títulos funcionam como chave lógica)
			$catalog = [
				// title, days, extensible_days, paid, is_health_related, [min%, max%]
				['Férias',                         30,  0, true,  false, [0, 0]],
				['Licença Médica Curta (Atestado)', 3,  2, true,  true,  [0, 0]],
				['Licença Médica Prolongada',      15, 15, true,  true,  [0, 0]],
				['Licença Maternidade',           120,  0, true,  true,  [0, 0]],
				['Licença Paternidade',             5,  0, true,  true,  [0, 0]],
				['Luto (Falecimento na Família)',   2,  1, true,  false, [0, 0]],
				['Casamento',                       3,  0, true,  false, [0, 0]],
				['Doação de Sangue',                1,  0, true,  true,  [0, 0]],
				['Júri/Convocação Judicial',        2,  3, true,  false, [0, 0]],
				['Serviço Militar / Convocação',   10, 20, true,  false, [0, 0]],
				['Treinamento/Capacitação',         2,  5, true,  false, [0, 0]],
				['Afastamento Não Remunerado',     10, 20, false, false, [0, 100]],
				['Acompanhamento de Familiar',      2,  3, true,  true,  [0, 0]],
				['Mudança de Residência',           1,  0, true,  false, [0, 0]],
			];

			$created = 0;
			$updated = 0;

			foreach ($catalog as [$title, $days, $ext, $paid, $isHealth, $pct]) {
				[$minPct, $maxPct] = $pct;

				// Hardening de limites
				$days   = max(0, (int) $days);
				$ext    = max(0, (int) $ext);
				$minPct = max(0, min(100, (int) $minPct));
				$maxPct = max(0, min(100, (int) $maxPct));
				if ($minPct > $maxPct) $minPct = $maxPct;

				// Metadados sugestivos
				$categories  = $isHealth
					? ['saúde', 'comprovável', 'documento_médico']
					: ['administrativo', 'benefício', 'RH'];
				$conditions  = $isHealth
					? ['exigir_atestado' => true, 'min_horas' => 4]
					: ['exigir_aviso_previo' => true];
				$attachments = $isHealth
					? [['type' => 'pdf', 'required' => true, 'label' => 'Atestado/Laudo']]
					: [['type' => 'pdf', 'required' => false, 'label' => 'Comprovante']];

				$payload = [
					'title'                           => $title,
					'days'                            => $days,
					PJC::COL_EXT_DY                   => $ext,
					'paid'                            => (bool) $paid,
					PJC::COL_HLT_RL                   => (bool) $isHealth,
					PJC::COL_SL_MIN_DD_PCT            => $minPct,
					PJC::COL_SL_MAX_DD_PCT            => $maxPct,
					'description'                     => $faker->sentence(random_int(8, 18)),
					'categories'                      => $categories,
					'conditions'                      => $conditions,
					'attachments'                     => $attachments,
					DC::COL_TABLE_CREATOR                 => $systemUserId,
				];

				// Upsert por título
				$model = LeaveType::query()->where('title', $title)->first();

				if ($model) {
					$model->fill($payload)->save();
					$updated++;
				} else {
					LeaveType::create($payload);
					$created++;
				}
			}

			// Extras opcionais para variedade controlada
			$extra = 4;
			for ($i = 0; $i < $extra; $i++) {
				$isHealth = (bool) random_int(0, 1);
				$base     = $isHealth ? 'Licença Especial de Saúde' : 'Licença Administrativa';
				$title    = $base . ' ' . ($i + 1);

				$days   = max(0, random_int(1, 10));
				$ext    = max(0, random_int(0, 5));
				$paid   = (bool) random_int(0, 1);

				// Dedução salarial: 0% se pago; até 100% se não remunerado
				if ($paid) {
					$minPct = 0;
					$maxPct = 0;
				} else {
					$maxPct = random_int(30, 100);
					$minPct = random_int(0, $maxPct);
				}

				$payload = [
					'title'                           => $title,
					'days'                            => $days,
					PJC::COL_EXT_DY                   => $ext,
					'paid'                            => $paid,
					PJC::COL_HLT_RL                   => $isHealth,
					PJC::COL_SL_MIN_DD_PCT            => $minPct,
					PJC::COL_SL_MAX_DD_PCT            => $maxPct,
					'description'                     => $faker->sentence(random_int(8, 18)),
					'categories'                      => $isHealth ? ['saúde'] : ['administrativo'],
					'conditions'                      => $isHealth ? ['exigir_atestado' => true] : ['exigir_aviso_previo' => true],
					'attachments'                     => $isHealth
						? [['type' => 'pdf', 'required' => true, 'label' => 'Atestado']]
						: [['type' => 'pdf', 'required' => false, 'label' => 'Comprovante']],
					DC::COL_TABLE_CREATOR                 => $systemUserId,
				];

				LeaveType::updateOrCreate(['title' => $title], $payload);
				$created++;
			}

			Log::info('LeaveTypeSeeder: created=' . $created . ', updated=' . $updated);
		});
	}
}
