<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	DatabaseConstants as DC
};
use App\Models\ProductServiceUnit;
use App\Models\ProductServiceCategory;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

final class ProductServiceUnitSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			$faker = \Faker\Factory::create('pt_BR');
			$tz    = 'America/Sao_Paulo';
			$now   = now($tz);

			// Garante categorias base e obtém seus IDs
			$catIds = $this->ensureCategories($systemUserId);

			// Catálogo base (mistura produtos/serviços) — mantido e ampliado
			$catalog = [
				// Serviços (originais)
				['Consultoria de TI',                 'hour',     180.00],
				['Suporte Help Desk',                 'ticket',    55.00],
				['Visita Técnica On-site',            'visit',    250.00],
				['Administração de Servidores',       'month',   2200.00],
				['Gestão de Backups',                 'month',    690.00],
				['Monitoramento 24x7',                'month',   1290.00],
				['Implantação de Firewall',           'service', 3500.00],
				['Auditoria de Segurança',            'service', 4800.00],
				['Treinamento Corporativo',           'session',  650.00],
				['Desenvolvimento sob Demanda',       'hour',     220.00],
				// Produtos/Unidades (originais)
				['Licença Software Pro',              'license',  990.00],
				['Licença Software Std',              'license',  590.00],
				['Switch Gerenciável 24p',            'item',    1790.00],
				['AP Wi-Fi Corporativo',              'item',     890.00],
				['Nobreak 1500VA',                    'item',    1390.00],
				['SSD NVMe 1TB',                      'item',     520.00],
				['Ampliação de Storage (100 GB)',     'GB',         2.90],
				['Tráfego Adicional (100 GB)',        'GB',         1.70],
				['Licença por Assento',               'seat',      49.90],
				['Plano Antivírus Endpoint',          'month',     14.90],

				// ===== Itens adicionais (novos) =====
				['Gestão de Patches e Atualizações',  'month',    750.00],
				['Hardening de Servidores',           'service', 4200.00],
				['Migração de E-mail Corporativo',    'service', 2800.00],
				['Projeto Next.js (Sprint)',          'sprint',  9600.00],
				['Integração API / Webhook',          'service', 1600.00],
				['Consultoria em Cloud (AWS/Azure)',  'hour',     260.00],
				['Plano VIP Help Desk',               'month',   1590.00],
				['Resposta a Incidentes',             'service', 5200.00],
				['Assessoria LGPD',                   'service', 3900.00],
				['Planejamento de Capacidade',        'service', 2400.00],
				['Notebook Empresarial i5',           'item',    4350.00],
				['Servidor Rack 2U',                  'item',   18990.00],
				['Switch PoE 48p',                    'item',    8290.00],
				['Impressora Laser A4',               'item',    1290.00],
				['Licença Office 365 Business',       'seat',      42.90],
				['Firewall UTM Appliance',            'item',   11990.00],
				['Assinatura Antispam (usuário)',     'seat',       9.90],
				['Rede Cabeada (por ponto)',          'point',    160.00],
				['Cabeamento Estruturado (metro)',    'meter',      6.90],
				['Backup em Nuvem (100 GB)',          'GB',         2.90],
			];

			// Estados permitidos (tendência a 'active')
			$statusPool = [
				'active',
				'active',
				'active',
				'active',
				'paused',
				'inactive'
			];

			// Métricas de atributos opcionais
			$tagsPool = [
				'SLA',
				'Crítico',
				'Prioridade Alta',
				'Remoto',
				'On-site',
				'Cloud',
				'Hardware',
				'Software',
				'Assinatura',
				'Projeto'
			];

			$created = 0;
			$updated = 0;

			foreach ($catalog as [$name, $unit, $basePrice]) {
				// Código único e consistente
				$code = strtoupper(Str::slug(mb_substr($name, 0, 30), '_')) . '-' . Str::upper(Str::random(4));

				// Janelas de disponibilidade realistas
				$from  = now($tz)->subDays(random_int(0, 120));
				$until = (clone $from)->addDays(random_int(120, 720));

				// Status
				$status = $statusPool[array_rand($statusPool)];

				// Quantidade padrão (p/ unidades vendidas em pacote)
				$quantity = match ($unit) {
					'GB'      => [100, 200, 500, 1000][array_rand([100, 200, 500, 1000])],
					'seat'    => [1, 5, 10, 25, 50][array_rand([1, 5, 10, 25, 50])],
					'ticket'  => [1, 5, 10][array_rand([1, 5, 10])],
					'session' => [1, 2, 4][array_rand([1, 2, 4])],
					default   => 1,
				};

				// Preço base com 4 casas
				$price = $this->money4($basePrice + mt_rand(0, 3000) / 100);

				// Categoria vinculada (obrigatória e existente)
				$catCode = $this->guessCategoryCode($name, $unit);
				$catId   = $catIds[$catCode] ?? $catIds['ITINFRA']; // fallback seguro

				// Atributos extras
				$attributes = [
					'tax_included'   => (bool) random_int(0, 1),
					'warranty_months' => in_array($unit, ['item', 'license'], true) ? [6, 12, 24][array_rand([6, 12, 24])] : null,
					'setup_fee'      => in_array($unit, ['service', 'month', 'sprint'], true) ? $this->money4(mt_rand(0, 12000) / 100) : 0.0000,
					'sla_hours'      => in_array($unit, ['month', 'service', 'hour', 'ticket'], true) ? [4, 8, 24][array_rand([4, 8, 24])] : null,
					'tags'           => $faker->randomElements($tagsPool, random_int(1, 3)),
				];
				$attributes = array_filter($attributes, fn($v) => $v !== null);

				$payload = [
					'name'                => $name,
					'code'                => $code,
					'status'              => $status,
					AC::COL_MUNIT         => $unit,
					'quantity'            => $quantity,
					BC::COL_BS_PRC        => $price,
					BC::COL_CUR_ID        => 'BRL',
					'attributes'          => $attributes,
					'categories'          => [['id' => $catId]], // atende à validação do Model
					'description'         => $faker->sentence(random_int(8, 18)),
					'notes'               => $faker->optional(0.35)->sentence(random_int(6, 14)),
					AC::COL_AV_FROM       => $from,   // Carbon (cast datetime)
					AC::COL_AV_UNTIL      => $until,  // Carbon (cast datetime)
					DC::TABLE_CREATOR     => $systemUserId,
				];

				// upsert por "name" (mantém código único por criação)
				$model = ProductServiceUnit::query()->where('name', $name)->first();

				if ($model) {
					$model->fill($payload)->save();
					$updated++;
				} else {
					ProductServiceUnit::create($payload);
					$created++;
				}
			}

			// Itens adicionais gerados pelo Faker para variedade
			$extraCount = 10;
			for ($i = 0; $i < $extraCount; $i++) {
				$unit = $this->pick(['hour', 'session', 'item', 'license', 'GB', 'day', 'month', 'visit', 'ticket', 'seat', 'service', 'sprint', 'point', 'meter']);
				$name = match ($unit) {
					'hour'    => 'Pacote Horas ' . $faker->bs(),
					'session' => 'Sessão de ' . ucfirst($faker->word()),
					'item'    => ucfirst($faker->word()) . ' Pro ' . strtoupper($faker->bothify('??-###')),
					'license' => 'Licença ' . strtoupper($faker->bothify('PRO-??##')),
					'GB'      => 'Pacote de Armazenamento',
					'day'     => 'Diária Técnica ' . strtoupper($faker->bothify('DT-###')),
					'month'   => 'Plano Mensal ' . ucfirst($faker->word()),
					'visit'   => 'Visita Técnica ' . strtoupper($faker->bothify('VT-##')),
					'ticket'  => 'Pacote de Chamados',
					'seat'    => 'Assento Adicional',
					'sprint'  => 'Sprint de Projeto ' . strtoupper($faker->bothify('SP-##')),
					'point'   => 'Ponto de Rede Adicional',
					'meter'   => 'Cabo de Rede (metro)',
					default   => 'Serviço Especializado ' . ucfirst($faker->word()),
				};

				$code  = strtoupper(Str::slug(mb_substr($name, 0, 30), '_')) . '-' . Str::upper(Str::random(5));
				$from  = now($tz)->subDays(random_int(0, 60));
				$until = (clone $from)->addDays(random_int(150, 540));
				$status   = $statusPool[array_rand($statusPool)];
				$quantity = match ($unit) {
					'GB'      => [100, 200, 500, 1000][array_rand([100, 200, 500, 1000])],
					'seat'    => [1, 5, 10, 25, 50][array_rand([1, 5, 10, 25, 50])],
					'ticket'  => [1, 5, 10][array_rand([1, 5, 10])],
					'session' => [1, 2, 4][array_rand([1, 2, 4])],
					default   => 1,
				};

				$price = $this->money4(mt_rand(500, 150000) / 100);

				// Categoria coerente com o tipo de unidade
				$catCode = match (true) {
					in_array($unit, ['item', 'license', 'GB', 'seat', 'meter', 'point'], true) => 'RETAIL',
					$unit === 'ticket' => 'HELPDESK',
					default            => $this->pick(['SWDEV', 'ITINFRA']), // serviços em geral
				};
				$catId = $catIds[$catCode] ?? $catIds['ITINFRA'];

				$attributes = [
					'tax_included' => (bool) random_int(0, 1),
					'setup_fee'    => in_array($unit, ['service', 'month', 'sprint'], true) ? $this->money4(mt_rand(0, 9000) / 100) : 0.0000,
					'tags'         => $faker->randomElements($tagsPool, random_int(1, 3)),
				];

				$payload = [
					'name'                => $name,
					'code'                => $code,
					'status'              => $status,
					AC::COL_MUNIT         => $unit,
					'quantity'            => $quantity,
					BC::COL_BS_PRC        => $price,
					BC::COL_CUR_ID        => 'BRL',
					'attributes'          => $attributes,
					'categories'          => [['id' => $catId]],
					'description'         => $faker->sentence(random_int(8, 18)),
					'notes'               => $faker->optional(0.30)->sentence(random_int(6, 14)),
					AC::COL_AV_FROM       => $from,
					AC::COL_AV_UNTIL      => $until,
					DC::TABLE_CREATOR     => $systemUserId,
				];

				ProductServiceUnit::create($payload);
				$created++;
			}

			Log::info("ProductServiceUnitSeeder: created={$created}, updated={$updated}");
		});
	}

	private function money4(float $v): float
	{
		return (float) number_format($v, 4, '.', '');
	}

	private function pick(array $options): mixed
	{
		return $options[array_rand($options)];
	}

	/**
	 * Garante as categorias base e retorna um mapa code => id.
	 */
	private function ensureCategories(string $creatorId): array
	{
		$base = [
			'SWDEV'   => ['name' => 'Desenvolvimento de Software',        'label' => 'service', 'color' => '#2563eb'],
			'ITINFRA' => ['name' => 'Infraestrutura de TI',               'label' => 'service', 'color' => '#0ea5e9'],
			'HELPDESK' => ['name' => 'Suporte / Help Desk',                'label' => 'service', 'color' => '#10b981'],
			'RETAIL'  => ['name' => 'Revenda de Produtos de Tecnologia',  'label' => 'product', 'color' => '#f59e0b'],
		];

		$out = [];
		foreach ($base as $code => $cfg) {
			$cat = ProductServiceCategory::query()->where('code', $code)->first();

			if (!$cat) {
				$cat = ProductServiceCategory::create([
					'id'                => (string) Str::uuid(),
					'name'              => $cfg['name'],
					'code'              => $code,
					// compatibilidade: 'type' livre (0–9) e label enum (product/service/other)
					'type'              => 0,
					DC::COL_TP_LB       => $cfg['label'],
					'color'             => $cfg['color'],
					'icon'              => null,
					'attributes'        => ['seeded' => true],
					'description'       => 'Categoria base semântica para unidades de produto/serviço.',
					DC::TABLE_CREATOR   => $creatorId,
					DC::TABLE_UPDATER   => $creatorId,
				]);
			}

			$out[$code] = $cat->id;
		}

		return $out;
	}

	/**
	 * Mapeia nome/unidade para a categoria adequada (code).
	 */
	private function guessCategoryCode(string $name, string $unit): string
	{
		$n = mb_strtolower($name);

		// Produtos / revenda
		if (in_array($unit, ['item', 'license', 'GB', 'seat', 'meter', 'point'], true)) {
			return 'RETAIL';
		}
		if (str_contains($n, 'licença') || str_contains($n, 'ap ') || str_contains($n, 'switch') || str_contains($n, 'nobreak') || str_contains($n, 'ssd') || str_contains($n, 'impressora') || str_contains($n, 'firewall utm')) {
			return 'RETAIL';
		}

		// Help desk
		if (str_contains($n, 'help desk') || $unit === 'ticket' || str_contains($n, 'vip help')) {
			return 'HELPDESK';
		}

		// Desenvolvimento de software
		if (str_contains($n, 'desenvolvimento') || str_contains($n, 'next.js') || str_contains($n, 'api') || str_contains($n, 'webhook') || str_contains($n, 'treinamento')) {
			return 'SWDEV';
		}

		// Infraestrutura de TI (default para serviços de infra/segurança)
		return 'ITINFRA';
	}
}
