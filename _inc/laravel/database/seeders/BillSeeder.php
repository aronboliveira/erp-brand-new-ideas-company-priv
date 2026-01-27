<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{
	BillStatus,
	BrazilState,
	ChinaState,
	CountryName,
	PortugalState,
	ConsumableType,
	PaymentStatus,
	TransactionType,
	UnitedStatesState,
	UserType
};
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class BillSeeder extends Seeder
{
	private const SECONDS_LIMIT = 6 * 10 ** 2; // 5 minutos
	public function run(): void
	{
		// Dependências mínimas para respeitar FKs
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$vendorIds = DB::table(DC::TABLE_VENDORS)->pluck('id');
		$catIds    = DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id');
		$orderIds  = DB::table(DC::TABLE_ORDERS)->pluck('id');

		if ($vendorIds->isEmpty() || $catIds->isEmpty() || $orderIds->isEmpty()) {
			$this->command?->warn(
				'[BillSeeder] Dependências ausentes: ' .
					($vendorIds->isEmpty() ? 'vendors ' : '') .
					($catIds->isEmpty()    ? 'categorias ' : '') .
					($orderIds->isEmpty()  ? 'orders ' : '') .
					'— rode os seeders correspondentes antes do BillSeeder.'
			);
			return;
		}

		$count = 512;
		$clock = microtime(true);
		$today = Carbon::today();
		$customerPool = DB::table(DC::TABLE_CUSTOMERS)->inRandomOrder()->get()->toArray();
		if (empty($customerPool)) {
			$this->command?->warn(
				'[BillSeeder] Nenhum cliente encontrado para associar às contas; por favor, execute o CustomerSeeder primeiro.'
			);
			return;
		}
		for ($i = 0; $i < $count; $i++) {
			try {
				if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
					$out->writeln('[BillSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
					$this->command?->warn('[BillSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
					return;
				}
				$vendorId = $vendorIds->random();
				$catId    = $catIds->random();
				$orderId  = $orderIds->random();

				// Datas coerentes com as regras do modelo (booted/saving fará os clamps)
				$sendDate = $today->copy()->addDays(random_int(0, 1));
				$billDate = $today->copy()->addDays(random_int(0, 2));
				$dueDate  = $today->copy()->addDays(random_int(3, 15));

				// Valores monetários
				$amount          = round(random_int(5_000, 200_000) / 100, 2); // 50.00 ~ 2000.00
				$discountApply   = (int) random_int(0, 1);
				$discountMax     = $discountApply ? (int) floor($amount * 0.2 * 100) : 0; // até 20%
				$discount        = round(($discountMax > 0 ? random_int(0, $discountMax) : 0) / 100, 2);

				// Status (deixa o modelo normalizar se algo vier inválido)
				$billStatusCases = BillStatus::cases();
				$payStatusCases  = PaymentStatus::cases();

				$billStatus = $billStatusCases[array_rand($billStatusCases)];
				$payStatus  = $payStatusCases[array_rand($payStatusCases)];

				// Itens simples (sem refs de tax para evitar FKs inexistentes)
				$items = [
					[
						'name'     => 'Serviço ' . Str::upper(Str::random(4)),
						'price'    => round(random_int(1_000, 10_000) / 100, 2),
						'quantity' => random_int(1, 5),
						'discount' => round(random_int(0, 500) / 100, 2),
						'tax'      => [], // o modelo filtrará impostos inválidos
					],
				];
				(new \Symfony\Component\Console\Output\ConsoleOutput
				)->writeln("Criando Conta do fornecedor {$vendorId} associada ao pedido {$orderId}");
				$customer = $customerPool[array_rand($customerPool)];
				// Use CountryName enum to get a real country
				$countryEnum = fake()->randomElement([
					CountryName::Brazil,
					CountryName::UnitedStates,
					CountryName::Portugal,
					CountryName::Spain,
					CountryName::Argentina,
					CountryName::Chile,
					CountryName::Mexico,
				]);
				$country = $countryEnum->value;

				// Get state based on country
				$state = match ($countryEnum) {
					CountryName::Brazil => fake()->randomElement(BrazilState::cases())->value,
					CountryName::UnitedStates => fake()->randomElement(UnitedStatesState::cases())->value,
					CountryName::Portugal => fake()->randomElement(PortugalState::cases())->value,
					CountryName::China => fake()->randomElement(ChinaState::cases())->value,
					default => strtoupper(fake()->stateAbbr()),
				};

				// Generate ZIP code based on country
				$zip = match ($countryEnum) {
					CountryName::Brazil => sprintf('%05d', random_int(10000, 99999)) . '-' . sprintf('%03d', random_int(0, 999)),
					CountryName::UnitedStates => sprintf('%05d', random_int(1000, 99999)) . '-' . sprintf('%04d', random_int(0, 9999)),
					CountryName::Portugal => sprintf('%04d', random_int(100, 9999)) . '-' . sprintf('%03d', random_int(0, 999)),
					default => fake()->postcode(),
				};

				// Generate city based on country/state
				$city = match ($countryEnum) {
					CountryName::Brazil => match ($state) {
						'SP' => fake()->randomElement(['São Paulo', 'Campinas', 'Santos', 'Ribeirão Preto']),
						'RJ' => fake()->randomElement(['Rio de Janeiro', 'Niterói', 'Petrópolis']),
						'MG' => fake()->randomElement(['Belo Horizonte', 'Uberlândia', 'Juiz de Fora']),
						'RS' => fake()->randomElement(['Porto Alegre', 'Caxias do Sul', 'Pelotas']),
						'PR' => fake()->randomElement(['Curitiba', 'Londrina', 'Maringá']),
						default => fake()->city(),
					},
					CountryName::UnitedStates => match ($state) {
						'CA' => fake()->randomElement(['Los Angeles', 'San Francisco', 'San Diego']),
						'NY' => fake()->randomElement(['New York', 'Buffalo', 'Rochester']),
						'TX' => fake()->randomElement(['Houston', 'Dallas', 'Austin']),
						'FL' => fake()->randomElement(['Miami', 'Orlando', 'Tampa']),
						'IL' => fake()->randomElement(['Chicago', 'Springfield', 'Aurora']),
						default => fake()->city(),
					},
					default => fake()->city(),
				};

				// Generate address
				$address = match ($countryEnum) {
					CountryName::Brazil => fake()->randomElement([
						fake()->streetAddress(),
						fake()->streetName() . ', ' . random_int(10, 5000),
						'Rua ' . fake()->lastName() . ', ' . random_int(10, 500),
						'Avenida ' . fake()->firstName() . ', ' . random_int(100, 2000),
					]),
					CountryName::UnitedStates => fake()->streetAddress(),
					default => fake()->address(),
				};
				// Criação via Eloquent para acionar normalizações do modelo
				Bill::create([
					// Identificadores e relacionamentos
					BC::COL_BL_ID   => Str::uuid()->toString(),
					UC::COL_VD_ID   => $vendorId,
					BC::COL_CAT_ID  => $catId,
					BC::COL_OD_ID   => $orderId,

					// Datas
					BC::COL_SD_DT   => $sendDate->toDateString(),
					BC::COL_BL_DT   => $billDate->toDateString(),
					PJC::COL_D_DATE => $dueDate->toDateString(),

					// Status numérico legado (0..3, por exemplo)
					PJC::COL_STATUS => random_int(0, 3),

					// Enums
					BC::COL_STT_LB  => $billStatus,
					BC::COL_PAY_STT => $payStatus,
					'type'          => TransactionType::Bill->value,
					UC::COL_U_TP    => UserType::Customer,

					// Preços e flags
					BC::COL_PRC_CUR => strtoupper(SC::DEF_SITE_CURRENCY_ID),
					BC::COL_SHIP_DSP => random_int(0, 1),
					'amount'        => $amount,
					BC::COL_DSC_APL => $discountApply,
					'discount'      => $discount,

					// Payloads variados
					'taxes'       => [],        // deixe vazio; o modelo valida
					'items'       => $items,
					'attachments' => [],
					'notes'       => 'Conta gerada por seeder para testes.',

					// Billing (opcional)
					BC::COL_BL_NAME => $customer->name ?? 'Cliente ' . ($i + 1),
					BC::COL_BL_EMAIL => $customer->email ?? 'cliente' . ($i + 1) . '@example.com',
					BC::COL_BL_TEL => $customer->phone ?? match ($countryEnum) {
						CountryName::Brazil => '+55 ' . fake()->randomElement(BrazilState::DDD) . ' 9' .
							random_int(9000, 9999) . '-' . random_int(0000, 9999),
						CountryName::UnitedStates => '+1 ' . random_int(200, 999) . ' ' .
							random_int(100, 999) . '-' . random_int(1000, 9999),
						default => fake()->phoneNumber(),
					},
					BC::COL_BL_ZIP => $zip,
					BC::COL_BL_ADR => $address,
					BC::COL_BL_CTY => $city,
					BC::COL_BL_ST => $state,
					BC::COL_BL_CTR => $country,

					// Shipping (optional) - 50% chance to be same as billing, 50% different
					BC::COL_SHIP_NAME => fake()->boolean(50) ? null : 'Logística ' . ($i + 1),
					BC::COL_SHIP_TEL => fake()->boolean(50) ? null : match ($countryEnum) {
						CountryName::Brazil => '+55 ' . fake()->randomElement(BrazilState::DDD) . ' 3' .
							random_int(1000, 9999) . '-' . random_int(1000, 9999),
						CountryName::UnitedStates => '+1 ' . random_int(200, 999) . ' ' .
							random_int(100, 999) . '-' . random_int(1000, 9999),
						default => fake()->phoneNumber(),
					},
					BC::COL_SHIP_ZIP => fake()->boolean(50) ? null : match ($countryEnum) {
						CountryName::Brazil => sprintf('%05d', random_int(1000, 99999)) . '-' . sprintf('%03d', random_int(0, 999)),
						CountryName::UnitedStates => sprintf('%05d', random_int(1000, 99999)) . '-' . sprintf('%04d', random_int(0, 9999)),
						default => fake()->postcode(),
					},
					BC::COL_SHIP_ADR => fake()->boolean(50) ? null : fake()->streetAddress(),
					BC::COL_SHIP_CTY => fake()->boolean(50) ? null : $city,
					BC::COL_SHIP_ST => fake()->boolean(50) ? null : $state,
					BC::COL_SHIP_CTR => fake()->boolean(50) ? null : $country,
					BC::COL_SHIP_EMAIL => fake()->boolean(50) ? null : 'logistica' . ($i + 1) . '@example.com',
					BC::COL_SHIP_DTL => fake()->boolean(30) ? null : fake()->randomElement([
						'Apto ' . random_int(1, 200),
						'Bloco ' . chr(random_int(65, 70)), // A-F
						'Sala ' . random_int(1, 50),
						'Andar ' . random_int(1, 30),
					]),
					BC::COL_BL_DTL => fake()->boolean(30) ? null : fake()->randomElement([
						'Apto ' . random_int(1, 200),
						'Complemento ' . random_int(1, 10),
						'Referência: ' . fake()->sentence(3),
					]),
				]);
				$out->writeln("Creating bill {$i} / {$count} succeeded, vendor {$vendorId}, order {$orderId}, amount {$amount}");
			} catch (\Exception $e) {
				Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
				continue;
			}
		}

		$this->command?->info('[BillSeeder] ' . $count . ' contas lançadas com sucesso.');
	}
}
