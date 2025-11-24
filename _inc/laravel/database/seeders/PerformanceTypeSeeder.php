<?php

namespace Database\Seeders;

use App\Config\Constants\ProjectsConstants as PJC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Models\PerformanceType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PerformanceTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = \Faker\Factory::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			// Catálogos de métricas por domínio
			$catalog = [
				'Suporte' => [
					'sla_atingido',
					'tempo_medio_resposta',
					'tempo_medio_atendimento',
					'taxa_reabertura',
					'satisfacao_cliente',
					'chamados_resolvidos_dia'
				],
				'Infraestrutura' => [
					'disponibilidade',
					'mttr',
					'mtbf',
					'capacidade_utilizada',
					'tempo_provisionamento',
					'incidentes_criticos_mes'
				],
				'Desenvolvimento' => [
					'lead_time',
					'cycle_time',
					'implantes_sem_falhas',
					'taxa_retrabalho',
					'cobertura_testes',
					'bugs_pos_release'
				],
				'Seguranca' => [
					'incidentes_detectados',
					'mttd',
					'mttr_sec',
					'vulnerabilidades_abertas',
					'tempo_correcao_criticas',
					'conformidade_politicas'
				],
				'Vendas/Atendimento' => [
					'taxa_conversao',
					'ticket_medio',
					'nps',
					'tempo_primeiro_contato',
					'churn',
					'followups_medio'
				],
				'Operacoes' => [
					'otd',
					'oee',
					'backlog',
					'tempo_ciclo_operacional',
					'eficiencia_recursos',
					'conformidade_procedimentos'
				],
				'UX/Produto' => [
					'adocao_funcionalidades',
					'retencao',
					'csat',
					'tempo_tarefa',
					'erros_usabilidade',
					'feature_success_rate'
				],
			];

			// Definições com main_metric explícita
			$rows = [
				[
					'name'        => 'Performance de Suporte',
					'category'    => 'Suporte',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'sla_atingido',
					PJC::COL_CRT      => true,
				],
				[
					'name'        => 'Performance de Infraestrutura',
					'category'    => 'Infraestrutura',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'disponibilidade',
					PJC::COL_CRT      => true,
				],
				[
					'name'        => 'Performance de Desenvolvimento',
					'category'    => 'Desenvolvimento',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'lead_time',
					PJC::COL_CRT      => false,
				],
				[
					'name'        => 'Performance de Segurança',
					'category'    => 'Seguranca',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'mttd',
					PJC::COL_CRT      => true,
				],
				[
					'name'        => 'Performance Comercial',
					'category'    => 'Vendas/Atendimento',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'taxa_conversao',
					PJC::COL_CRT      => false,
				],
				[
					'name'        => 'Performance Operacional',
					'category'    => 'Operacoes',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'otd',
					PJC::COL_CRT      => false,
				],
				[
					'name'        => 'Performance de UX/Produto',
					'category'    => 'UX/Produto',
					'description' => $faker->sentence(),
					PJC::COL_M_METRIC => 'adocao_funcionalidades',
					PJC::COL_CRT      => false,
				],
			];

			$created = 0;
			$updated = 0;

			foreach ($rows as $r) {
				$cat = $r['category'];
				$pool = $catalog[$cat] ?? $catalog['Operacoes'];

				// escolhe 3 a 6 métricas do catálogo e garante a principal
				$pick = $faker->randomElements($pool, $faker->numberBetween(3, 6));
				if (!in_array($r[PJC::COL_M_METRIC], $pick, true)) {
					$pick[] = $r[PJC::COL_M_METRIC];
				}
				// remove duplicatas e embaralha levemente
				$metrics = array_values(array_unique($pick));
				shuffle($metrics);

				/** @var \App\Models\PerformanceType $model */
				$model = PerformanceType::query()->updateOrCreate(
					['name' => $r['name']],
					[
						'description'         => $r['description'],
						'category'            => $cat,
						PJC::COL_M_METRIC     => $r[PJC::COL_M_METRIC],
						'metrics'             => $metrics,
						PJC::COL_CRT          => (bool) $r[PJC::COL_CRT],
					]
				);

				// Força o criador (pois HasAuditFields depende de auth())
				if (empty($model->{DC::TABLE_CREATOR})) {
					$model->{DC::TABLE_CREATOR} = $systemUserId;
					$model->save();
				}

				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("PerformanceTypeSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
