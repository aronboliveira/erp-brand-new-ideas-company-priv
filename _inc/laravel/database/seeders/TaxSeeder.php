<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Tax as Tx;

final class TaxSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$defs = [
				['name' => 'ICMS',    'rate' => $faker->randomElement([7.00, 12.00, 18.00])],
				['name' => 'ISS',     'rate' => $faker->randomFloat(2, 2.00, 5.00)],
				['name' => 'IPI',     'rate' => $faker->randomFloat(2, 0.00, 15.00)],
				['name' => 'PIS',     'rate' => 1.65],
				['name' => 'COFINS',  'rate' => 7.60],
				['name' => 'CSLL',    'rate' => 9.00],
				['name' => 'IRPJ',    'rate' => 15.00],
				['name' => 'IR',      'rate' => 27.50],
				['name' => 'INSS',    'rate' => 20.00],
				['name' => 'FGTS',    'rate' => 8.00],
				['name' => 'IOF',     'rate' => 6.38],
				['name' => 'IPVA',    'rate' => $faker->randomElement([2.00, 3.00, 4.00])],

				// MicroEmpresas — Simples Nacional (Faixa 1)
				['name' => 'Simples Nacional (DAS) - ME - Anexo I - Faixa 1',  'rate' => 4.00],
				['name' => 'Simples Nacional (DAS) - ME - Anexo II - Faixa 1', 'rate' => 4.50],
				['name' => 'Simples Nacional (DAS) - ME - Anexo III - Faixa 1', 'rate' => 6.00],
				['name' => 'Simples Nacional (DAS) - ME - Anexo IV - Faixa 1', 'rate' => 4.50],
				['name' => 'Simples Nacional (DAS) - ME - Anexo V - Faixa 1',  'rate' => 15.50],
			];

			while (log(count($defs), 2) % 1 !== 0 || count($defs) < 32) {
				$defs[] = [
					'name' => 'Imposto ' . fake()->unique()->words(2, true),
					'rate' => $faker->randomFloat(2, 0.50, 25.00),
					'description' => $faker->sentence(),
				];
			}

			foreach ($defs as $def) {
				try {
					if (Tx::where('name', $def['name'])->exists()) {
						do $def['name'] = 'Imposto ' . fake()->unique()->words(2, true);
						while (Tx::where('name', $def['name'])->exists());
					}
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Imposto: {$def['name']}");
					do $taxId = Str::uuid()->toString();
					while (Tx::where('id', $taxId)->exists());

					$t = new Tx();
					$t->id                   = $taxId;
					$t->name                 = $def['name'];
					$t->rate                 = $def['rate'];
					$t->{DC::COL_TABLE_CREATOR}  = $systemUserId;
					$t->setAttribute(DC::COL_TABLE_UPDATER, null);
					$t->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
