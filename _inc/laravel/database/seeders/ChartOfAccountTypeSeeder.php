<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ChartsConstants as CHTC,
	DatabaseConstants as DC,
	SettingsConstants as SC
};
use App\Models\ChartOfAccountType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ChartOfAccountTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			$tz  = 'America/Sao_Paulo';
			$now = now($tz);

			// Eixo X padrão (meses, MM)
			$monthsMM = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

			// Helpers para amostras
			$moneySeries = fn(float $min, float $max, int $n = 12): array =>
			array_map(static fn() => self::rndMoney($min, $max), range(1, $n));

			$intSeries = fn(int $min, int $max, int $n = 12): array =>
			array_map(static fn() => random_int($min, $max), range(1, $n));

			$pctSeries = fn(int $min = 96, int $max = 100, int $n = 12): array =>
			array_map(static fn() => (float) number_format(random_int($min * 100, $max * 100) / 100, 2, '.', ''), range(1, $n));

			$rows = [
				[
					CHTC::COL_CD => 'REV_M',
					CHTC::COL_NM => 'Receita Mensal',
					'category'   => 'finance',
					'description' => 'Total de receitas por mês.',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'emerald',
						'y_format'   => 'currency',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => SC::DEF_SITE_CURRENCY_SB, 'values' => $moneySeries(15000, 90000)],
					],
				],
				[
					CHTC::COL_CD => 'EXP_M',
					CHTC::COL_NM => 'Despesas Mensais',
					'category'   => 'finance',
					'description' => 'Despesas operacionais por mês.',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'rose',
						'y_format'   => 'currency',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => SC::DEF_SITE_CURRENCY_SB, 'values' => $moneySeries(10000, 65000)],
					],
				],
				[
					CHTC::COL_CD => 'PROFIT_M',
					CHTC::COL_NM => 'Lucro Mensal',
					'category'   => 'finance',
					'description' => 'Lucro estimado (receita – despesa).',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'indigo',
						'y_format'   => 'currency',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => SC::DEF_SITE_CURRENCY_SB, 'values' => $moneySeries(2000, 35000)],
					],
				],
				[
					CHTC::COL_CD => 'CASH_BAL',
					CHTC::COL_NM => 'Saldo de Caixa',
					'category'   => 'finance',
					'description' => 'Saldo de caixa/contas ao fim de cada mês.',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'sky',
						'y_format'   => 'currency',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => SC::DEF_SITE_CURRENCY_SB, 'values' => $moneySeries(20000, 250000)],
					],
				],
				[
					CHTC::COL_CD => 'HC_M',
					CHTC::COL_NM => 'Headcount Mensal',
					'category'   => 'operations',
					'description' => 'Quantidade de colaboradores ativos ao final do mês.',
					'rules' => [
						'decimals'   => 0,
						'palette'    => 'amber',
						'y_format'   => 'integer',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => null, 'values' => $intSeries(8, 42)],
					],
				],
				[
					CHTC::COL_CD => 'TCK_M',
					CHTC::COL_NM => 'Chamados (Mensal)',
					'category'   => 'support',
					'description' => 'Volume de chamados recebidos por mês.',
					'rules' => [
						'decimals'   => 0,
						'palette'    => 'violet',
						'y_format'   => 'integer',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => null, 'values' => $intSeries(25, 320)],
					],
				],
				[
					CHTC::COL_CD => 'UPTIME_M',
					CHTC::COL_NM => 'Uptime Mensal',
					'category'   => 'operations',
					'description' => 'Percentual de disponibilidade por mês.',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'emerald',
						'y_format'   => 'percentage',
						'stacked'    => false,
					],
					'units'      => [
						'X' => ['type' => 'timestamp', 'unit' => 'MM', 'values' => $monthsMM],
						'Y' => ['type' => 'value',     'unit' => '%', 'values' => $pctSeries(99, 100)],
					],
				],
				[
					CHTC::COL_CD => 'AR_AGING',
					CHTC::COL_NM => 'Aging de Recebíveis',
					'category'   => 'finance',
					'description' => 'Distribuição de contas a receber por faixa de atraso.',
					'rules' => [
						'decimals'   => 2,
						'palette'    => 'cyan',
						'y_format'   => 'currency',
						'stacked'    => true,
					],
					'units'      => [
						'X' => [
							'type'   => 'bucket',
							'unit'   => 'days',
							'values' => ['0-30', '31-60', '61-90', '90+'],
						],
						'Y' => [
							'type'   => 'value',
							'unit'   => SC::DEF_SITE_CURRENCY_SB,
							'values' => [
								self::rndMoney(5000, 25000),
								self::rndMoney(3000, 15000),
								self::rndMoney(1000, 10000),
								self::rndMoney(1000, 12000),
							],
						],
					],
				],
			];

			$created = 0;
			$updated = 0;

			foreach ($rows as $data) {
				// Garantia de CODE único, compacto
				$data[CHTC::COL_CD] = strtoupper(Str::snake($data[CHTC::COL_CD]));
				$data[DC::TABLE_CREATOR] = $systemUserId;

				/** @var ChartOfAccountType $model */
				$model = ChartOfAccountType::query()
					->where(CHTC::COL_CD, $data[CHTC::COL_CD])
					->first();

				if ($model) {
					$model->fill($data)->save();
					$updated++;
				} else {
					ChartOfAccountType::create($data);
					$created++;
				}
			}

			Log::info("ChartOfAccountTypeSeeder: created={$created}, updated={$updated}");
		});
	}

	private static function rndMoney(float $min, float $max): float
	{
		$v = mt_rand((int) round($min * 100), (int) round($max * 100)) / 100;
		return (float) number_format($v, 2, '.', '');
	}
}
