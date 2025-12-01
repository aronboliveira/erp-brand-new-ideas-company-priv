<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Models\ContractType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ContractTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			// Faixas realistas de valores e prazos (em meses) para cenários comuns no Brasil.
			// Ajuste conforme sua política interna, evitando números absurdos.
			$rows = [
				[
					'name'        => 'Prestação de Serviço',
					'description' => 'Serviços pontuais ou contínuos prestados por terceiros com escopo e SLA definidos.',
					'category'    => 'service',
					BC::COL_MIN_V => 1000.00,
					BC::COL_MAX_V => 500000.00,  // teto realista para contratos de serviço típicos
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 24,
					BC::COL_TC    => 'Escopo, entregáveis, prazos e indicadores de qualidade definidos contratualmente.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Outsourcing',
					'description' => 'Terceirização de processos/áreas com metas e níveis de serviço.',
					'category'    => 'service',
					BC::COL_MIN_V => 20000.00,
					BC::COL_MAX_V => 1500000.00, // projetos corporativos em escala moderada
					BC::COL_MIN_M => 6,
					BC::COL_MAX_M => 60,
					BC::COL_TC    => 'Contrato por capacidade/resultado com fiscalização de obrigações e SLAs.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Funcionário PJ (Pessoa Jurídica)',
					'description' => 'Prestação individual por pessoa jurídica (contratação PJ).',
					'category'    => 'employment_pj',
					BC::COL_MIN_V => 4000.00,
					BC::COL_MAX_V => 80000.00,   // teto mensal realista para PJ individual qualificado
					BC::COL_MIN_M => 3,
					BC::COL_MAX_M => 36,
					BC::COL_TC    => 'Escopo individual, cronograma e aceite por entregáveis.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Sócio',
					'description' => 'Acordo societário com pró-labore e distribuição de resultados.',
					'category'    => 'equity',
					BC::COL_MIN_V => 1320.00,    // pró-labore de referência
					BC::COL_MAX_V => 100000.00,  // pró-labore alto em estruturas enxutas
					BC::COL_MIN_M => 0,          // sem prazo determinado
					BC::COL_MAX_M => 0,
					BC::COL_TC    => 'Regras societárias e de governança; pró-labore e distribuição conforme acordo.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Venda de Insumos',
					'description' => 'Fornecimento recorrente ou pontual de materiais/insumos.',
					'category'    => 'supply',
					BC::COL_MIN_V => 1000.00,
					BC::COL_MAX_V => 1500000.00, // fornecimento com volume/regularidade típicos
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 24,
					BC::COL_TC    => 'Condições comerciais, prazos de entrega e garantias de qualidade.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,    // garantias usuais de qualidade/entrega
					BC::COL_RNGT     => true,
				],
				// EMPREGO (CLT)
				[
					'name'        => 'CLT - Prazo indeterminado',
					'description' => 'Contrato celetista sem prazo determinado.',
					'category'    => 'employment',
					BC::COL_MIN_V => 1320.00,   // ~salário mínimo referência (ajustável)
					BC::COL_MAX_V => 60000.00,  // teto mensal realista para maioria dos cargos
					BC::COL_MIN_M => 0,
					BC::COL_MAX_M => 0,         // sem término pré-definido
					BC::COL_TC    => null,
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,
					BC::COL_RNGT     => false,
				],
				[
					'name'        => 'CLT - Prazo determinado',
					'description' => 'Contrato a termo (até 24 meses).',
					'category'    => 'employment',
					BC::COL_MIN_V => 1320.00,
					BC::COL_MAX_V => 60000.00,
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 24,
					BC::COL_TC    => 'Vigência limitada e justificável por necessidade transitória.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,
					BC::COL_RNGT     => false,
				],
				[
					'name'        => 'Temporário (Lei 6.019/74)',
					'description' => 'Necessidade transitória; geralmente até 9 meses.',
					'category'    => 'temporary',
					BC::COL_MIN_V => 1320.00,
					BC::COL_MAX_V => 45000.00,
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 9,
					BC::COL_TC    => 'Regido por empresa de trabalho temporário; prazos controlados.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,
					BC::COL_RNGT     => false,
				],
				[
					'name'        => 'Intermitente',
					'description' => 'Períodos alternados de trabalho e inatividade.',
					'category'    => 'employment',
					BC::COL_MIN_V => 10.00,     // valor por hora/convocação pode ser baixo
					BC::COL_MAX_V => 30000.00,  // teto mensal prático para jornadas intermitentes
					BC::COL_MIN_M => 0,
					BC::COL_MAX_M => 0,
					BC::COL_TC    => 'Convocações avulsas; pagamento ao final da prestação.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,
					BC::COL_RNGT     => true,
				],

				// FORMAÇÃO / ENTRADA
				[
					'name'        => 'Estágio',
					'description' => 'Ato educativo escolar supervisionado (até 24 meses).',
					'category'    => 'internship',
					BC::COL_MIN_V => 400.00,    // bolsas típicas
					BC::COL_MAX_V => 3000.00,
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 24,
					BC::COL_TC    => 'Termo de compromisso obrigatório; supervisão prevista.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => false,
				],
				[
					'name'        => 'Jovem Aprendiz',
					'description' => 'Aprendizagem técnico-profissional (até 24 meses).',
					'category'    => 'apprentice',
					BC::COL_MIN_V => 600.00,
					BC::COL_MAX_V => 3000.00,
					BC::COL_MIN_M => 12,
					BC::COL_MAX_M => 24,
					BC::COL_TC    => 'Plano de aprendizagem; frequência escolar.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => true,
					BC::COL_RNGT     => false,
				],

				// PRESTAÇÃO DE SERVIÇOS
				[
					'name'        => 'Pessoa Jurídica (PJ)',
					'description' => 'Prestação de serviços por pessoa jurídica.',
					'category'    => 'service',
					BC::COL_MIN_V => 2000.00,      // contratos PJ tendem a ser mais altos
					BC::COL_MAX_V => 1200000.00,   // teto anual para projetos médios/grandes
					BC::COL_MIN_M => 1,
					BC::COL_MAX_M => 36,          // 1 a 36 meses é comum
					BC::COL_TC    => 'Escopo, entregáveis e SLAs definidos.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Freelancer/Autônomo',
					'description' => 'Pessoa física sem habitualidade/subordinação.',
					'category'    => 'service',
					BC::COL_MIN_V => 300.00,
					BC::COL_MAX_V => 150000.00,   // teto por projeto individual
					BC::COL_MIN_M => 0,
					BC::COL_MAX_M => 12,
					BC::COL_TC    => 'Por tarefa/projeto com marco de aceite.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Terceirizado',
					'description' => 'Serviços via empresa especializada (BPO/outsourcing).',
					'category'    => 'service',
					BC::COL_MIN_V => 5000.00,
					BC::COL_MAX_V => 2000000.00,  // contratos corporativos maiores
					BC::COL_MIN_M => 6,
					BC::COL_MAX_M => 60,
					BC::COL_TC    => 'Fiscalização de obrigações; níveis de serviço.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
				[
					'name'        => 'Cooperado',
					'description' => 'Intermediação por cooperativa de trabalho.',
					'category'    => 'cooperative',
					BC::COL_MIN_V => 1000.00,
					BC::COL_MAX_V => 500000.00,
					BC::COL_MIN_M => 6,
					BC::COL_MAX_M => 48,
					BC::COL_TC    => 'Remuneração conforme regras da cooperativa.',
					BC::COL_DEF_TRMC => true,
					BC::COL_SVR_GRT  => false,
					BC::COL_RNGT     => true,
				],
			];


			$created = 0;
			$updated = 0;

			foreach ($rows as $data) {
				$model = ContractType::updateOrCreate(
					['name' => $data['name']],
					$data + [DC::COL_TABLE_CREATOR => $systemUserId]
				);
				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("ContractTypeSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
