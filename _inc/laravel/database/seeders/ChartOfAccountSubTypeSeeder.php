<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ChartsConstants as CHTC,
	DatabaseConstants as DC
};
use App\Models\{ChartOfAccountSubType, ChartOfAccountType};
use App\Traits\EnsuresSystemUser;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ChartOfAccountSubTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();
			$faker        = Faker::create('pt_BR');

			/** @var \Illuminate\Support\Collection<string,\App\Models\ChartOfAccountType> $typesByCode */
			$typesByCode = ChartOfAccountType::query()
				->get()
				->keyBy(CHTC::COL_CD);

			// Helper para construir subtipos
			$mk = function (
				ChartOfAccountType $type,
				string $code,
				string $name,
				string $chartType = 'line',
				array $colors = [],
				bool $needApproval = false,
				bool $allowManual  = true,
				array $ccRules = [],
				array $valRules = []
			) use ($faker, $systemUserId): array {
				$colors = $colors ?: [
					'primary'   => $faker->hexColor(),
					'secondary' => $faker->hexColor(),
					'tertiary'  => $faker->hexColor(),
				];

				$draw = [
					'type'    => $chartType,
					'colors'  => $colors,
					'options' => [
						'x_unit'   => data_get($type->units, 'X.unit'),
						'x_values' => (array) data_get($type->units, 'X.values', []),
						'y_unit'   => data_get($type->units, 'Y.unit'),
						'y_values' => (array) data_get($type->units, 'Y.values', []),
						'legend'   => true,
						'smooth'   => in_array($chartType, ['line', 'area'], true),
					],
				];

				return [
					CHTC::COL_CD            => strtoupper(Str::snake($type->{CHTC::COL_CD} . '_' . $code)),
					CHTC::COL_NM            => $name,
					'description'           => $faker->sentence(10),
					CHTC::COL_TP            => $type->id,
					CHTC::COL_TP_NM         => $type->{CHTC::COL_NM},
					CHTC::COL_DR_TP         => $draw,
					CHTC::COL_CC_RL         => $ccRules ?: [
						'cost_center' => $faker->randomElement(['ADM', 'OPS', 'TI', 'COM']),
					],
					CHTC::COL_VL_RL         => $valRules ?: [
						'min' => $faker->numberBetween(0, 1000),
						'max' => $faker->numberBetween(2000, 50000),
					],
					CHTC::COL_RQ_APV        => $needApproval,
					CHTC::COL_ALW_MNL_ENT   => $allowManual,
					'rules'            => [
						// 'category' => (string) ($type->category ?? 'general'),
						'source'   => 'seeder',
					],
					DC::COL_TABLE_CREATOR       => $systemUserId,
				];
			};

			$rows = [];

			// FINANCE: Receita, Despesa, Lucro, Caixa
			if ($t = $typesByCode->get('REV_M')) {
				$rows[] = $mk($t, 'revenue_products',  'Receita - Produtos',  'bar',  [], false, true);
				$rows[] = $mk($t, 'revenue_services',  'Receita - Serviços',  'bar',  [], false, true);
				$rows[] = $mk($t, 'revenue_mrr',       'Receita - MRR',       'line', [], false, false);
			}
			if ($t = $typesByCode->get('EXP_M')) {
				$rows[] = $mk($t, 'expense_payroll',   'Despesa - Folha',     'area', [], true,  false, ['cost_center' => 'ADM']);
				$rows[] = $mk($t, 'expense_cloud',     'Despesa - Nuvem',     'area', [], true,  true,  ['cost_center' => 'TI']);
				$rows[] = $mk($t, 'expense_marketing', 'Despesa - Marketing', 'area', [], true,  true,  ['cost_center' => 'COM']);
			}
			if ($t = $typesByCode->get('PROFIT_M')) {
				$rows[] = $mk($t, 'profit_gross',      'Lucro Bruto',         'line');
				$rows[] = $mk($t, 'profit_net',        'Lucro Líquido',       'line');
			}
			if ($t = $typesByCode->get('CASH_BAL')) {
				$rows[] = $mk($t, 'cash_main',         'Saldo Caixa - Conta Principal', 'line');
				$rows[] = $mk($t, 'cash_reserve',      'Saldo Caixa - Reserva',         'line');
			}

			// OPERATIONS: Headcount, Uptime
			if ($t = $typesByCode->get('HC_M')) {
				$rows[] = $mk($t, 'hc_dev',            'Headcount - Dev',     'bar', [], false, false);
				$rows[] = $mk($t, 'hc_ops',            'Headcount - Ops',     'bar', [], false, false);
			}
			if ($t = $typesByCode->get('UPTIME_M')) {
				$rows[] = $mk($t, 'uptime_dc1',        'Uptime - DC1',        'line', [], false, false);
				$rows[] = $mk($t, 'uptime_sp',         'Uptime - São Paulo',  'line', [], false, false);
			}

			// SUPPORT: Tickets
			if ($t = $typesByCode->get('TCK_M')) {
				$rows[] = $mk($t, 'tickets_opened',    'Chamados Abertos',    'bar');
				$rows[] = $mk($t, 'tickets_closed',    'Chamados Fechados',   'bar');
			}

			// FINANCE: Aging de Recebíveis
			if ($t = $typesByCode->get('AR_AGING')) {
				$rows[] = $mk($t, 'ar_0_30',           'AR 0-30',             'bar', [], false, false);
				$rows[] = $mk($t, 'ar_31_60',          'AR 31-60',            'bar', [], false, false);
				$rows[] = $mk($t, 'ar_61_90',          'AR 61-90',            'bar', [], false, false);
				$rows[] = $mk($t, 'ar_90_plus',        'AR 90+',              'bar', [], false, false);
			}

			$created = 0;
			$updated = 0;

			foreach ($rows as $data) {
				/** @var ChartOfAccountSubType $model */
				$model = ChartOfAccountSubType::query()
					->where(CHTC::COL_CD, $data[CHTC::COL_CD])
					->first();

				if ($model) {
					$model->fill($data)->save();
					$updated++;
				} else {
					ChartOfAccountSubType::create($data);
					$created++;
				}
			}

			Log::info("ChartOfAccountSubTypeSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
