<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Models\LoanOption;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class LoanOptionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			$rows = [
				[
					'name'          => 'Empréstimo Consignado (CLT)',
					'description'   => 'Parcelas descontadas em folha para colaboradores CLT.',
					BC::COL_EXP_BDG => 5000.00,
					BC::COL_MAX_BDG => 30000.00,
					BC::COL_MIN_ITM => 6,
					BC::COL_MAX_ITM => 72,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => true,
					BC::COL_TC      => 'Sujeito a elegibilidade do RH e margem consignável.',
				],
				[
					'name'          => 'Empréstimo Consignado (Servidor)',
					'description'   => 'Condições específicas para servidores públicos.',
					BC::COL_EXP_BDG => 8000.00,
					BC::COL_MAX_BDG => 50000.00,
					BC::COL_MIN_ITM => 6,
					BC::COL_MAX_ITM => 96,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => true,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => true,
					BC::COL_TC      => 'Requer comprovação de vínculo estatutário.',
				],
				[
					'name'          => 'Adiantamento Salarial',
					'description'   => 'Antecipação de salário com desconto na competência seguinte.',
					BC::COL_EXP_BDG => 1500.00,
					BC::COL_MAX_BDG => 5000.00,
					BC::COL_MIN_ITM => 1,
					BC::COL_MAX_ITM => 3,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => false,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => false,
					BC::COL_TC      => 'Limitado a 30% do salário líquido.',
				],
				[
					'name'          => 'Empréstimo Pessoal (FGTS como garantia parcial)',
					'description'   => 'Uso de parte do saldo de FGTS como mitigação de risco.',
					BC::COL_EXP_BDG => 4000.00,
					BC::COL_MAX_BDG => 20000.00,
					BC::COL_MIN_ITM => 6,
					BC::COL_MAX_ITM => 60,
					BC::COL_FGTS_PCT => 10,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 30,
					BC::COL_ALW_PAY_RL_DDT => true,
					BC::COL_TC      => 'FGTS bloqueado proporcional durante a vigência.',
				],
				[
					'name'          => 'Financiamento de Equipamentos (Trabalho)',
					'description'   => 'Aquisição de notebook, ferramentas e mobiliário.',
					BC::COL_EXP_BDG => 3000.00,
					BC::COL_MAX_BDG => 15000.00,
					BC::COL_MIN_ITM => 6,
					BC::COL_MAX_ITM => 36,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 15,
					BC::COL_ALW_PAY_RL_DDT => false,
					BC::COL_TC      => 'Comprovação de compra vinculada ao trabalho.',
				],
				[
					'name'          => 'Emergencial Saúde',
					'description'   => 'Cobertura de despesas médicas extraordinárias.',
					BC::COL_EXP_BDG => 2000.00,
					BC::COL_MAX_BDG => 12000.00,
					BC::COL_MIN_ITM => 3,
					BC::COL_MAX_ITM => 24,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => true,
					BC::COL_TC      => 'Necessária comprovação do evento/nota fiscal.',
				],
				[
					'name'          => 'Educação e Certificações',
					'description'   => 'Cursos, pós-graduação e certificações.',
					BC::COL_EXP_BDG => 3000.00,
					BC::COL_MAX_BDG => 20000.00,
					BC::COL_MIN_ITM => 6,
					BC::COL_MAX_ITM => 48,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => true,
					BC::COL_TC      => 'Comprovação de matrícula e desempenho quando aplicável.',
				],
				[
					'name'          => 'Renegociação Interna de Adiantamentos',
					'description'   => 'Unifica adiantamentos pendentes em novo contrato.',
					BC::COL_EXP_BDG => 0.00,
					BC::COL_MAX_BDG => 10000.00,
					BC::COL_MIN_ITM => 3,
					BC::COL_MAX_ITM => 24,
					BC::COL_FGTS_PCT => 0,
					BC::COL_SVR_GRT => false,
					BC::COL_RNGT    => true,
					BC::COL_GRC_PRD_DYS => 0,
					BC::COL_ALW_PAY_RL_DDT => false,
					BC::COL_TC      => 'Liquidação dos contratos anteriores no ato.',
				],
			];

			$created = 0;
			$updated = 0;

			foreach ($rows as $data) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Tipo de Empréstimo: {$data['name']}");
					// saneamento complementar no seeder
					if (
						isset($data[BC::COL_MIN_ITM], $data[BC::COL_MAX_ITM])
						&& $data[BC::COL_MIN_ITM] > $data[BC::COL_MAX_ITM]
					) {
						[$data[BC::COL_MIN_ITM], $data[BC::COL_MAX_ITM]] =
							[$data[BC::COL_MAX_ITM], $data[BC::COL_MIN_ITM]];
					}
					if (isset($data[BC::COL_FGTS_PCT])) {
						$data[BC::COL_FGTS_PCT] = max(0, min(50, (int) $data[BC::COL_FGTS_PCT]));
					}

					$model = LoanOption::firstOrNew(['name' => $data['name']]);
					$model->fill($data);

					if (!$model->exists && empty($model->{DC::COL_TABLE_CREATOR})) {
						// atribuição direta (guardado contra mass-assignment)
						$model->{DC::COL_TABLE_CREATOR} = $systemUserId;
					}

					$model->save();
					$model->wasRecentlyCreated ? $created++ : $updated++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info('LoanOptionSeeder concluído', compact('created', 'updated'));
		}, 3);
	}
}
