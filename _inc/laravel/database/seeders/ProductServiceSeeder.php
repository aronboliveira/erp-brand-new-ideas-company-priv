<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC,
	SettingsConstants as SC
};
use App\Models\{
	ChartOfAccount,
	ProductService,
	ProductServiceCategory,
	ProductServiceUnit,
	Tax
};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ProductServiceSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$creator = $this->ensureSystemUser();

			$faker = \Faker\Factory::create('pt_BR');
			$tz    = 'America/Sao_Paulo';

			// Mapa de categorias code => id (exige que ProductServiceCategorySeeder já tenha rodado)
			$catIds = $this->categoryIdsByCode([
				'SWDEV',
				'ITINFRA',
				'HELPDESK',
				'RETAIL'
			]);

			// Catálogo-base (serviços + itens) com category-code e unidade principal
			$catalog = [
				// name                                   unit      primaryCat   sellPrice  costPrice
				['Consultoria de TI',                    'hour',    'ITINFRA',   220.0000, 140.0000],
				['Suporte Help Desk',                    'ticket',  'HELPDESK',   59.9000,  25.0000],
				['Visita Técnica On-site',               'visit',   'ITINFRA',   280.0000, 120.0000],
				['Administração de Servidores',          'month',   'ITINFRA',  2400.0000, 900.0000],
				['Gestão de Backups',                    'month',   'ITINFRA',   720.0000, 300.0000],
				['Monitoramento 24x7',                   'month',   'ITINFRA',  1390.0000, 450.0000],
				['Implantação de Firewall',              'service', 'ITINFRA',  3800.0000, 900.0000],
				['Auditoria de Segurança',               'service', 'ITINFRA',  4900.0000, 1200.0000],
				['Treinamento Corporativo',              'session', 'SWDEV',     690.0000, 250.0000],
				['Desenvolvimento sob Demanda',          'hour',    'SWDEV',     240.0000,  95.0000],
				['Gestão de Patches e Atualizações',     'month',   'ITINFRA',   790.0000, 300.0000],
				['Hardening de Servidores',              'service', 'ITINFRA',  4300.0000, 1100.0000],
				['Migração de E-mail Corporativo',       'service', 'ITINFRA',  2900.0000, 900.0000],
				['Projeto Next.js (Sprint)',             'sprint',  'SWDEV',    9800.0000, 2800.0000],
				['Integração API / Webhook',             'service', 'SWDEV',    1700.0000, 600.0000],
				['Plano VIP Help Desk',                  'month',   'HELPDESK', 1690.0000, 600.0000],
				['Resposta a Incidentes',                'service', 'ITINFRA',  5400.0000, 1400.0000],
				['Assessoria LGPD',                      'service', 'ITINFRA',  3990.0000, 1200.0000],
				['Planejamento de Capacidade',           'service', 'ITINFRA',  2500.0000, 800.0000],

				// Revenda / produtos
				['Notebook Empresarial i5',              'item',    'RETAIL',   4350.0000, 3200.0000],
				['Servidor Rack 2U',                     'item',    'RETAIL',  18990.0000, 13500.0000],
				['Switch Gerenciável 24p',               'item',    'RETAIL',   1790.0000, 1200.0000],
				['Switch PoE 48p',                       'item',    'RETAIL',   8290.0000, 6000.0000],
				['AP Wi-Fi Corporativo',                 'item',    'RETAIL',    890.0000,  620.0000],
				['Impressora Laser A4',                  'item',    'RETAIL',   1290.0000,  900.0000],
				['SSD NVMe 1TB',                         'item',    'RETAIL',    520.0000,  380.0000],
				['Licença Software Pro',                 'license', 'RETAIL',    990.0000,  550.0000],
				['Licença Software Std',                 'license', 'RETAIL',    590.0000,  320.0000],
				['Licença por Assento',                  'seat',    'RETAIL',     49.9000,   19.9000],
				['Firewall UTM Appliance',               'item',    'RETAIL',  11990.0000,  8700.0000],
				['Assinatura Antispam (usuário)',        'seat',    'RETAIL',      9.9000,    3.9000],
				['Backup em Nuvem (100 GB)',             'GB',      'RETAIL',      2.9000,    0.9000],
			];

			// Unidades aceitas por tipo
			$unitSets = [
				'service' => ['service', 'hour', 'session', 'sprint', 'day', 'month', 'visit', 'ticket'],
				'infra'   => ['hour', 'service', 'month', 'visit', 'ticket'],
				'dev'     => ['hour', 'session', 'sprint', 'service', 'month'],
				'retail'  => ['item', 'license', 'seat', 'GB', 'meter', 'point', 'other'],
			];

			// Auxiliares opcionais (se não existirem, ficam nulos — sem quebrar)
			$anyTaxId   = Tax::query()->value('id');
			$anySaleCoa = ChartOfAccount::query()->value('id');
			$anyExpCoa  = ChartOfAccount::query()->inRandomOrder()->value('id');

			$created = 0;
			$updated = 0;

			foreach ($catalog as [$name, $mainUnit, $catCode, $sell, $cost]) {
				$primaryCat = $catIds[$catCode] ?? null;
				if (!$primaryCat) {
					// Se por algum motivo a categoria não existir, ignora o item com log (defensivo)
					Log::warning("ProductServiceSeeder: categoria ausente para {$name} ({$catCode}), item ignorado.");
					continue;
				}

				$sku  = strtoupper(Str::slug(mb_substr($name, 0, 24), '-')) . '-' . Str::upper(Str::random(6));
				$from = now($tz)->subDays(random_int(0, 120));
				$until = (clone $from)->addDays(random_int(200, 900));

				$isRetail = in_array($mainUnit, ['item', 'license', 'seat', 'GB', 'meter', 'point'], true);
				$tags     = $isRetail ? ['revenda', 'estoque', 'hardware'] : ['serviço', 'SLA', 'projeto'];

				// accepted units
				$accepted = $isRetail ? $unitSets['retail'] : (str_contains(mb_strtolower($name), 'desenvolvimento') || str_contains(mb_strtolower($name), 'next.js')
					? $unitSets['dev'] : $unitSets['infra']);

				// Currencies aceitas (sempre inclui a default)
				$curr = ['BRL'];
				if ($isRetail && random_int(0, 1)) $curr[] = 'USD';

				// Tentativa de vincular a uma ProductServiceUnit coerente (por nome ou unidade)
				$unitId = ProductServiceUnit::query()
					->where('name', $name)->value('id');

				if (!$unitId) {
					$unitId = ProductServiceUnit::query()
						->where(AC::COL_MUNIT, $mainUnit)
						->inRandomOrder()
						->value('id');
				}

				$imgName = Str::slug($name) . '.png';
				$payload = [
					'name'                 => $name,
					'sku'                  => $sku,
					BC::COL_SL_PRC         => $this->money4($sell),
					BC::COL_PC_PRC         => $this->money4($cost),
					BC::COL_AC_CUR         => $curr,
					BC::COL_AC_MUNITS      => $accepted,
					'description'          => $faker->sentence(random_int(10, 20)),
					'attributes'           => [
						'tax_included'     => (bool) random_int(0, 1),
						'warranty_months'  => $isRetail ? [6, 12, 24][array_rand([6, 12, 24])] : null,
						'bundle'           => $isRetail && random_int(0, 1) ? $faker->word() : null,
						'service_level'    => !$isRetail ? [4, 8, 24][array_rand([4, 8, 24])] : null,
					],
					'tags'                 => $tags,
					DC::COL_PRO_IMG        => "images/products/{$imgName}",
					'icon'                 => $isRetail ? 'lucide-cpu' : 'lucide-server-cog',
					'quantity'             => $isRetail ? (float) random_int(5, 80) : 0.0,
					BC::COL_TAX_ID         => $anyTaxId,      // se não houver taxa, o model manterá null sem quebrar
					BC::COL_CAT_ID         => $primaryCat,
					'categories'           => [['id' => $primaryCat]],
					DC::COL_RL_CAT         => $this->relatedCats($primaryCat, $catIds),
					BC::COL_UNIT_ID        => $unitId,        // se não existir, model normaliza para null
					BC::COL_UNITS_SOLD     => $isRetail ? random_int(0, 500) : 0,
					BC::COL_UNITS_CNC      => $isRetail ? random_int(0, 30) : 0,
					BC::COL_UNITS_RTRN     => $isRetail ? random_int(0, 15) : 0,
					'type'                 => '0',            // campo livre legado (0..9)
					BKC::COL_SL_COA        => $anySaleCoa,
					BKC::COL_EXP_COA       => $anyExpCoa,
					AC::COL_AV_FROM        => $from,
					AC::COL_AV_UNTIL       => $until,
					AC::COL_IA             => true,
					BC::COL_ON_SALE        => (bool) random_int(0, 1),
					BC::COL_IS_LK          => false,
					BC::COL_IS_TRS         => false,
					DC::COL_TABLE_CREATOR      => $creator,
					DC::COL_TABLE_UPDATER      => $creator,
				];

				// Limpa nulls residuais de attributes
				$payload['attributes'] = array_filter(
					$payload['attributes'],
					fn($v) => $v !== null
				);

				$existing = ProductService::query()->where('name', $name)->first();
				if ($existing) {
					$existing->fill($payload)->save();
					$updated++;
				} else {
					ProductService::create($payload);
					$created++;
				}
			}

			Log::info("ProductServiceSeeder: created={$created}, updated={$updated}");
		});
	}

	private function money4(float $v): float
	{
		return (float) number_format($v, 4, '.', '');
	}

	/**
	 * Retorna mapa code => id para categorias existentes.
	 * Lança log se alguma estiver ausente (sem interromper a transação).
	 */
	private function categoryIdsByCode(array $codes): array
	{
		$rows = ProductServiceCategory::query()
			->whereIn('code', $codes)
			->get(['id', 'code'])
			->all();

		$map = [];
		foreach ($rows as $row) {
			$map[$row->code] = $row->id;
		}

		foreach ($codes as $code) {
			if (!isset($map[$code])) {
				Log::warning("ProductServiceSeeder: categoria com code={$code} não localizada.");
			}
		}
		return $map;
	}

	/**
	 * Gera pequenas relações extras para DC::COL_RL_CAT (sem repetir a primária).
	 */
	private function relatedCats(string $primaryId, array $all): array
	{
		$ids = array_values(array_unique(array_diff($all, [$primaryId])));
		shuffle($ids);
		$pick = array_slice($ids, 0, random_int(0, 2)); // 0..2 relações adicionais

		return array_map(fn($id) => ['id' => $id], $pick);
	}
}
