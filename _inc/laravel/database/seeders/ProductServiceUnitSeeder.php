<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BillsConstants as BC,
	DatabaseConstants as DC
};
use App\Models\ProductServiceUnit;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

			// Catálogo base (mistura produtos/serviços)
			$catalog = [
				// Serviços
				['Consultoria de TI',            'hour',     180.00],
				['Suporte Help Desk',            'ticket',    55.00],
				['Visita Técnica On-site',       'visit',    250.00],
				['Administração de Servidores',  'month',   2200.00],
				['Gestão de Backups',            'month',    690.00],
				['Monitoramento 24x7',           'month',   1290.00],
				['Implantação de Firewall',      'service', 3500.00],
				['Auditoria de Segurança',       'service', 4800.00],
				['Treinamento Corporativo',      'session',  650.00],
				['Desenvolvimento sob Demanda',  'hour',     220.00],
				// Produtos/Unidades
				['Licença Software Pro',         'license',  990.00],
				['Licença Software Std',         'license',  590.00],
				['Switch Gerenciável 24p',       'item',    1790.00],
				['AP Wi-Fi Corporativo',         'item',     890.00],
				['Nobreak 1500VA',               'item',    1390.00],
				['SSD NVMe 1TB',                 'item',     520.00],
				['Ampliação de Storage (100 GB)', 'GB',         2.90],
				['Tráfego Adicional (100 GB)',   'GB',         1.70],
				['Licença por Assento',          'seat',      49.90],
				['Plano Antivírus Endpoint',     'month',     14.90],
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

				// Atributos extras
				$attributes = [
					'tax_included' => (bool) random_int(0, 1),
					'warranty_months' => in_array($unit, ['item']) ? [6, 12, 24][array_rand([6, 12, 24])] : null,
					'setup_fee'    => in_array($unit, ['service', 'month']) ? $this->money4(mt_rand(0, 12000) / 100) : 0.0000,
					'sla_hours'    => in_array($unit, ['month', 'service', 'hour']) ? [4, 8, 24][array_rand([4, 8, 24])] : null,
					'tags'         => $faker->randomElements($tagsPool, random_int(1, 3)),
				];
				// Remove nulls residuais de atributos
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
					'description'         => $faker->sentence(random_int(8, 18)),
					'notes'               => $faker->optional(0.35)->sentence(random_int(6, 14)),
					AC::COL_AV_FROM       => $from,   // Carbon instance (cast datetime)
					AC::COL_AV_UNTIL      => $until,  // Carbon instance (cast datetime)
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
				$unit = $this->pick(['hour', 'session', 'item', 'license', 'GB', 'day', 'month', 'visit', 'ticket', 'seat', 'service']);
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

				$attributes = [
					'tax_included' => (bool) random_int(0, 1),
					'setup_fee'    => in_array($unit, ['service', 'month']) ? $this->money4(mt_rand(0, 9000) / 100) : 0.0000,
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
}
