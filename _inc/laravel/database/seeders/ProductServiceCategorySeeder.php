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
use Faker\Factory as Faker;

class ProductServiceCategorySeeder extends Seeder
{
	public function run(): void
	{
		$faker = Faker::create('pt_BR');

		// Categorias principais
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

		foreach ($catalog as $row) {
			ProductServiceCategory::updateOrCreate(
				['code' => $row['code']],
				[
					'id'            => (string) Str::uuid(),
					'name'          => $row['name'],
					'type'          => 0, // compatibilidade
					DC::COL_TP_LB   => $row[DC::COL_TP_LB],
					BKC::COL_COA    => null,
					'color'         => $row['color'],
					'icon'          => $row['icon'],
					'attributes'    => [
						'visibility' => 'public',
						'tags'       => [$row['code'], 'catalog'],
					],
					'description'   => $faker->sentences(2, true),
					DC::COL_RL_CAT  => [],
					'notes'         => $faker->sentence(),
					AC::COL_IA      => true,
				]
			);
		}
	}
}
