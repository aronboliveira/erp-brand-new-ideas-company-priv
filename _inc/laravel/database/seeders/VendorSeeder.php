<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{
	ProductService,
	ProductServiceUnit,
	Vendor
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class VendorSeeder extends Seeder
{
	public function run(): void
	{
		DB::transaction(function (): void {
			$faker = Faker::create('pt_BR');

			// Pequeno catálogo base para tornar a seed reexecutável/idempotente
			$seed = [
				['Fornecedor Alpha',  'alpha@vendors.example',  'BR', 'RJ'],
				['Fornecedor Beta',   'beta@vendors.example',   'BR', 'SP'],
				['Fornecedor Gama',   'gama@vendors.example',   'BR', 'MG'],
				['Fornecedor Delta',  'delta@vendors.example',  'BR', 'PR'],
				['Fornecedor Épsilon', 'epsilon@vendors.example', 'BR', 'RS'],
				['Fornecedor Zeta',   'zeta@vendors.example',   'BR', 'SC'],
			];

			// Tabela de ProductServices para linkar ofertas (se não houver, apenas ofertas "unregistered")
			$productIds = ProductService::query()
				->inRandomOrder()
				->limit(30)
				->pluck('id')
				->all();

			$created = 0;
			$updated = 0;

			foreach ($seed as [$name, $email, $ctr, $uf]) {
				$offers = $this->buildOffers($productIds);
				$mainTaxId = $this->requireAnyId(DC::TABLE_TAXES, 'tributário');
				$payload = [
					UC::COL_NM          => $name,
					UC::COL_EM          => $email,
					UC::COL_PW          => Hash::make('secret123!'),
					'contact'           => $faker->cellphoneNumber(),
					UC::COL_AV          => 'chatify.user_avatar.default',
					UC::COL_IA          => true,
					UC::COL_LG          => 'pt-br',
					'preferences'       => [
						'newsletter' => (bool) random_int(0, 1),
						'channels'   => ['email', 'phone'],
					],
					UC::COL_EM_V_AT     => $faker->optional(0.7)->dateTimeBetween('-180 days', 'now'),

					// Billing
					BC::COL_BL_NAME     => $name . ' - Financeiro',
					BC::COL_BL_EMAIL    => $email,
					BC::COL_BL_CTR      => $ctr,
					BC::COL_BL_ST       => $uf,
					BC::COL_BL_CTY      => $faker->city(),
					BC::COL_BL_TEL      => $faker->cellphoneNumber(),
					BC::COL_BL_ZIP      => $faker->postcode(),
					BC::COL_BL_ADR      => $faker->streetAddress(),
					BC::COL_BL_DTL      => $faker->secondaryAddress(),

					// Shipping
					BC::COL_SHIP_NAME   => $name . ' - Logística',
					BC::COL_SHIP_CTR    => $ctr,
					BC::COL_SHIP_ST     => $uf,
					BC::COL_SHIP_CTY    => $faker->city(),
					BC::COL_SHIP_TEL    => $faker->cellphoneNumber(),
					BC::COL_SHIP_ZIP    => $faker->postcode(),
					BC::COL_SHIP_ADR    => $faker->streetAddress(),
					BC::COL_SHIP_DTL    => $faker->secondaryAddress(),

					// Tributário e flags
					BC::COL_TX_N        => $mainTaxId, // CPF/CNPJ limpo; normalizador decidirá
					BC::COL_OT_TX_ID    => [...array_filter($this->idPool(DC::TABLE_TAXES), fn($t) => $t !== $mainTaxId)],
					BC::COL_IS_PRM      => (bool) random_int(0, 1),

					'balance'           => 0.00,
					'offers'            => $offers,

					// Auditoria
					DC::TABLE_CREATOR   => null,  // se quiser atrelar a um "system user", injete aqui o ID
				];

				$found = Vendor::query()->where(UC::COL_EM, $email)->first();

				if ($found) {
					$found->fill($payload)->save();
					$updated++;
				} else {
					Vendor::create($payload);
					$created++;
				}
			}

			Log::info("VendorSeeder: created={$created}, updated={$updated}");
		});
	}

	/**
	 * Monta uma lista de ofertas mesclando registradas e não registradas.
	 * A função respeita o formato esperado por Vendor::sanitizeOffers().
	 */
	private function buildOffers(array $productIds): array
	{
		$offers = [];

		// 2–4 ofertas válidas (registradas)
		$count = $productIds ? random_int(2, 4) : 0;
		shuffle($productIds);

		for ($i = 0; $i < $count; $i++) {
			$pid = $productIds[$i] ?? null;
			if (!$pid) break;

			// Se existir, tenta pegar uma unidade ligada ao ProductService
			$unitId = ProductServiceUnit::query()
				->where(BC::COL_PRD_SV_ID, $pid)
				->inRandomOrder()
				->value('id');

			$offers[] = $unitId
				? ['id' => $pid, 'unit_id' => $unitId]
				: ['id' => $pid];
		}

		// 1–2 ofertas não registradas (servem para exercitar os métodos de classificação)
		$extra = random_int(1, 2);
		for ($j = 0; $j < $extra; $j++) {
			$offers[] = ['key' => 'OFF-' . Str::upper(Str::random(8))];
		}

		return $offers;
	}

	private function idPool(string $table): array
	{
		return array_values(array_map('strval', DB::table($table)->pluck('id')->all()));
	}

	private function requireAnyId(string $table, string $humanName): string
	{
		$id = (string) (DB::table($table)->value('id') ?? '');
		if ($id !== '') return $id;

		throw new \RuntimeException("VendorSeeder: nenhuma linha encontrada em '{$table}' para '{$humanName}'. Crie ao menos 1 registro antes de semear Vendors.");
	}
}
