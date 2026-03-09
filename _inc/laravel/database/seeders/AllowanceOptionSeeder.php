<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Models\AllowanceOption;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class AllowanceOptionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			$tz     = 'America/Sao_Paulo';
			$anchor = now($tz); // âncora única; use (clone $anchor) para não mutá-la

			$rows = [
				[
					'name'          => 'Vale-Transporte',
					'description'   => 'Auxílio para deslocamento urbano/intermunicipal.',
					BC::COL_EXP_BDG => 220.00,
					BC::COL_MAX_BDG => 600.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(6)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addYear()->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Vale-Refeição (VR)',
					'description'   => 'Auxílio refeição em restaurantes.',
					BC::COL_EXP_BDG => 650.00,
					BC::COL_MAX_BDG => 1200.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(3)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addYear()->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Vale-Alimentação (VA)',
					'description'   => 'Auxílio compras em supermercados.',
					BC::COL_EXP_BDG => 450.00,
					BC::COL_MAX_BDG => 1000.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(9)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(15)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Plano de Saúde (Subsídio)',
					'description'   => 'Custeio parcial do plano de saúde.',
					BC::COL_EXP_BDG => 300.00,
					BC::COL_MAX_BDG => 1500.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(12)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(18)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Auxílio Internet/Telefonia',
					'description'   => 'Reembolso de conectividade para trabalho remoto.',
					BC::COL_EXP_BDG => 120.00,
					BC::COL_MAX_BDG => 300.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(2)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addYear()->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Auxílio Home Office',
					'description'   => 'Ajuda de custo para setup e manutenção do escritório remoto.',
					BC::COL_EXP_BDG => 200.00,
					BC::COL_MAX_BDG => 800.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonth()->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(24)->format('Y-m-d'),
					'renews'        => false,
				],
				[
					'name'          => 'Ajuda de Combustível',
					'description'   => 'Reembolso mensal para uso de veículo próprio.',
					BC::COL_EXP_BDG => 350.00,
					BC::COL_MAX_BDG => 1200.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(4)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(20)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Educação/Treinamentos',
					'description'   => 'Cursos, certificações e eventos.',
					BC::COL_EXP_BDG => 300.00,
					BC::COL_MAX_BDG => 3000.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(10)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(14)->format('Y-m-d'),
					'renews'        => false,
				],
				[
					'name'          => 'Adicional de Periculosidade',
					'description'   => 'Complemento para atividades perigosas (conforme legislação).',
					BC::COL_EXP_BDG => 0.00,
					BC::COL_MAX_BDG => 9999999999.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(18)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(6)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Adicional de Insalubridade',
					'description'   => 'Complemento para ambientes insalubres (conforme legislação).',
					BC::COL_EXP_BDG => 0.00,
					BC::COL_MAX_BDG => 9999999999.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(18)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(6)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Refeição Extra (Hora Extra/Plantão)',
					'description'   => 'Auxílio alimentação vinculado a jornadas estendidas.',
					BC::COL_EXP_BDG => 80.00,
					BC::COL_MAX_BDG => 300.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(5)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(12)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Ajuda de Custo/Realocação',
					'description'   => 'Mudança de cidade/região e instalação.',
					BC::COL_EXP_BDG => 1500.00,
					BC::COL_MAX_BDG => 8000.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(8)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(16)->format('Y-m-d'),
					'renews'        => false,
				],
				[
					'name'          => 'Auxílio Creche/Educação Infantil',
					'description'   => 'Custeio parcial para filhos de empregados.',
					BC::COL_EXP_BDG => 400.00,
					BC::COL_MAX_BDG => 1200.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(7)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(13)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Auxílio Saúde Mental',
					'description'   => 'Custeio parcial para terapias e atividades de bem-estar.',
					BC::COL_EXP_BDG => 200.00,
					BC::COL_MAX_BDG => 800.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(11)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(11)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Auxílio Cultura/Lazer',
					'description'   => 'Incentivo para atividades culturais e de lazer.',
					BC::COL_EXP_BDG => 150.00,
					BC::COL_MAX_BDG => 500.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(3)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(9)->format('Y-m-d'),
					'renews'        => true,
				],
				[
					'name'          => 'Auxílio Óculos de Grau',
					'description'   => 'Reembolso parcial para aquisição de óculos de grau.',
					BC::COL_EXP_BDG => 100.00,
					BC::COL_MAX_BDG => 400.00,
					BC::COL_VLD_FRM => (clone $anchor)->subMonths(14)->format('Y-m-d'),
					BC::COL_VLD_TO  => (clone $anchor)->addMonths(10)->format('Y-m-d'),
					'renews'        => false,
				]
			];

			$HARD_CAP = 2;
			$created = 0;
			$updated = 0;

			foreach ($rows as $payload) {
				if ($created >= $HARD_CAP) break;
				try {
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Tipo de Reserva: {$payload['name']}");
					$model = AllowanceOption::updateOrCreate(
						['name' => $payload['name']],
						$payload + [DC::COL_TABLE_CREATOR => $systemUserId]
					);
					$model->wasRecentlyCreated ? $created++ : $updated++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("AllowanceOptionSeeder completed", [
				'created' => $created,
				'updated' => $updated,
			]);
		}, 3);
	}
}
