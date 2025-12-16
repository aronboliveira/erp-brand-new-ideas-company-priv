<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	DatabaseConstants as DC
};
use App\Enums\ConsumableType;
use App\Models\ProductServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Log};
use Faker\Factory as Faker;

class ProductServiceCategorySeeder extends Seeder
{
	public function run(): void
	{
		$faker = Faker::create('pt_BR');

		// Mapeamento estável de índice para cada label de tipo
		$labelIndex = [
			ConsumableType::Product->value          => 0,
			ConsumableType::Service->value          => 1,
			ConsumableType::Income->value           => 2,
			ConsumableType::Expense->value          => 3,
			ConsumableType::Asset->value            => 4,
			ConsumableType::Liability->value        => 5,
			ConsumableType::Equity->value           => 6,
			ConsumableType::CostsOfGoodsSold->value => 7,
			ConsumableType::Other->value            => 8,
		];

		// Catálogo principal informado
		$catalog = [
			[
				'name'        => 'Desenvolvimento de Software',
				'code'        => 'SWDEV',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#2563eb',
				'icon'        => 'lucide:code',
			],
			[
				'name'        => 'Infraestrutura de TI',
				'code'        => 'ITINFRA',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#0ea5e9',
				'icon'        => 'lucide:server',
			],
			[
				'name'        => 'Suporte / Help Desk',
				'code'        => 'HELPDESK',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#10b981',
				'icon'        => 'lucide:headphones',
			],
			[
				'name'        => 'Revenda de Produtos de Tecnologia',
				'code'        => 'RETAIL',
				DC::COL_TP_LB => ConsumableType::Product->value,
				'color'       => '#f59e0b',
				'icon'        => 'lucide:shopping-cart',
			],
		];

		// Categorias genéricas para cobrir todas as labels do enum
		$typedGenerics = [
			[
				'name'        => 'Receitas Diversas',
				'code'        => 'INCOME_MISC',
				DC::COL_TP_LB => ConsumableType::Income->value,
				'color'       => '#22c55e',
				'icon'        => 'lucide:trending-up',
			],
			[
				'name'        => 'Despesas Operacionais',
				'code'        => 'OPER_EXP',
				DC::COL_TP_LB => ConsumableType::Expense->value,
				'color'       => '#ef4444',
				'icon'        => 'lucide:trending-down',
			],
			[
				'name'        => 'Ativos',
				'code'        => 'ASSETS',
				DC::COL_TP_LB => ConsumableType::Asset->value,
				'color'       => '#14b8a6',
				'icon'        => 'lucide:wallet',
			],
			[
				'name'        => 'Passivos',
				'code'        => 'LIABIL',
				DC::COL_TP_LB => ConsumableType::Liability->value,
				'color'       => '#a855f7',
				'icon'        => 'lucide:scale',
			],
			[
				'name'        => 'Patrimônio Líquido',
				'code'        => 'EQUITY',
				DC::COL_TP_LB => ConsumableType::Equity->value,
				'color'       => '#8b5cf6',
				'icon'        => 'lucide:banknote',
			],
			[
				'name'        => 'Custos de Mercadorias/Serviços Vendidos',
				'code'        => 'COGS',
				DC::COL_TP_LB => ConsumableType::CostsOfGoodsSold->value,
				'color'       => '#f97316',
				'icon'        => 'lucide:factory',
			],
			[
				'name'        => 'Outros',
				'code'        => 'OTHER',
				DC::COL_TP_LB => ConsumableType::Other->value,
				'color'       => '#6b7280',
				'icon'        => 'lucide:circle',
			],
		];

		foreach (array_merge($catalog, $typedGenerics) as $row) {
			try {
				(new \Symfony\Component\Console\Output\ConsoleOutput
				)->writeln("Criando Categoria de Produto/Serviço: {$row['name']}");
				$label = $row[DC::COL_TP_LB];
				$typeIndex = $labelIndex[$label] ?? 0;

				ProductServiceCategory::updateOrCreate(
					['code' => $row['code']],
					[
						// Mantém ID estável em primeira criação; em updates, o DB ignorará mudança de PK
						'id'            => (string) Str::uuid(),
						'name'          => $row['name'],
						// Índice compatível (0..9) alinhado ao label escolhido
						'type'          => $typeIndex,
						DC::COL_TP_LB   => $label,
						BKC::COL_COA    => null,
						'color'         => $row['color'] ?? '#fc544b',
						'icon'          => $row['icon'] ?? null,
						'attributes'    => [
							'visibility'  => 'public',
							'label_key'   => $label,
							'label_index' => $typeIndex,
							'tags'        => [$row['code'], 'catalog'],
						],
						'description'   => $faker->sentences(2, true),
						DC::COL_RL_CAT  => [],
						'notes'         => $faker->sentence(),
						AC::COL_IA      => true,
					]
				);
			} catch (\Exception $e) {
				Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
				continue;
			}
		}
	}
}
