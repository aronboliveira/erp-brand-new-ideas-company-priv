<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BLC,
	ChartsConstants as CHTC,
	DatabaseConstants as DC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Models\{BankAccount, ChartOfAccount, User};
use App\Traits\EnsuresSystemUser;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class BankAccountSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = Faker::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			// Catálogo de bancos brasileiros (mock realista)
			$banks = [
				['nome' => 'Banco do Brasil', 'cnpj' => self::digits(14)],
				['nome' => 'Caixa Econômica Federal', 'cnpj' => self::digits(14)],
				['nome' => 'Itaú Unibanco', 'cnpj' => self::digits(14)],
				['nome' => 'Bradesco', 'cnpj' => self::digits(14)],
				['nome' => 'Santander', 'cnpj' => self::digits(14)],
				['nome' => 'Sicoob', 'cnpj' => self::digits(14)],
				['nome' => 'Sicredi', 'cnpj' => self::digits(14)],
				['nome' => 'Nubank', 'cnpj' => self::digits(14)],
			];

			// Providers de integração suportados
			$providers = ['ofx', 'cnab', 'open_banking', 'manual'];

			$total    = 16;   // quantidade de contas a semear
			$created  = 0;
			$updated  = 0;

			for ($i = 0; $i < $total; $i++) {
				$bankMeta = $banks[$i % count($banks)];

				// Titular e responsável (pode usar o próprio system user em ambientes de teste)
				$holderId       = $faker->boolean(70) ? $systemUserId : (User::inRandomOrder()->value('id') ?: $systemUserId);
				$responsibleId  = $faker->boolean(60) ? $holderId : $systemUserId;

				// Conta contábil (opcional, se existir)
				$coaId = ChartOfAccount::inRandomOrder()->value('id');

				// Números e valores
				$accountNumber = self::digits($faker->numberBetween(6, 10)) . '-' . self::digits(1);
				$agencyNumber  = self::digits($faker->numberBetween(3, 5));
				$agencyDigit   = (string) $faker->randomDigit();

				$openingBalance = $faker->randomFloat(2, 0, 50_000);
				$currentBalance = $faker->boolean(80)
					? $openingBalance + $faker->randomFloat(2, -5_000, 20_000)
					: $openingBalance;

				if ($currentBalance < 0) $currentBalance = 0.00;

				$stored = max(
					$currentBalance,
					$faker->randomFloat(2, $currentBalance, $currentBalance + 10_000)
				);

				$locked = $faker->boolean(40) ? $faker->randomFloat(2, 0, $stored * 0.4) : 0.00;

				// PIX
				$acceptsPix = $faker->boolean(70);
				$pixKeys    = $acceptsPix ? self::fakePixKeys($faker) : [];

				// Cartões
				$hasCredit     = $faker->boolean(55);
				$creditCards   = $hasCredit ? [self::fakeCreditCard($faker)] : [];
				$acceptsCredit = $hasCredit && $faker->boolean(80);

				$hasDebit      = $faker->boolean(65);
				$debitCards    = $hasDebit ? [self::fakeDebitCard($faker)] : [];
				$acceptsDebit  = $hasDebit && $faker->boolean(85);

				// Vaults
				$vaults = [
					'main_vault' => [
						'name'                => 'Main Vault',
						'code'                => (string) Str::uuid(),
						'stored'              => $stored,
						'can_be_retrieved_in' => now()->addDays($faker->numberBetween(7, 45))->format('Y-m-d'),
					],
				];
				if ($faker->boolean(30)) {
					$vaults['term_vault'] = [
						'name'                => 'Term Vault',
						'code'                => (string) Str::uuid(),
						'stored'              => $faker->randomFloat(2, 0, 5_000),
						'can_be_retrieved_in' => now()->addDays($faker->numberBetween(30, 120))->format('Y-m-d'),
					];
				}

				// Regras para conciliação automática
				$autorcc = $faker->boolean(40);
				$rccRules = $autorcc ? [
					'match_min'         => 0.90,
					'match_window_days' => 5,
					'allow_split'       => $faker->boolean(),
					'vendors_whitelist' => $faker->randomElements(
						['Pag*', 'MercadoPago', 'Stone', 'Cielo', 'PicPay', 'IFood', '99Pay', 'Uber'],
						$faker->numberBetween(0, 4)
					),
				] : [];

				$payload = [
					// Identificação da conta
					BKC::COL_ACC_N   => $accountNumber,
					BKC::COL_IS_VRT  => true,

					// Conciliação
					BLC::COL_AUTORCC => $autorcc,
					BLC::COL_RCC_RL  => $rccRules,

					// Titular
					BKC::COL_HD_ID   => $holderId,
					BKC::COL_HNM     => $faker->name(),
					BKC::COL_CT      => preg_replace('/\D+/', '', $faker->cellphoneNumber()),
					BKC::COL_HD_ADDR => $faker->address(),

					// Responsável
					UC::COL_RSP_ID   => $responsibleId,
					UC::COL_RSP_NM   => $faker->name(),
					UC::COL_RSP_TEL  => preg_replace('/\D+/', '', $faker->cellphoneNumber()),
					UC::COL_RSP_EM   => $faker->safeEmail(),
					UC::COL_RSP_ADDR => $faker->address(),

					// Banco
					BKC::COL_NM         => $bankMeta['nome'],
					BKC::COL_ADR        => $faker->streetAddress() . ', ' . $faker->city(),
					BKC::COL_BANK_IDF   => $bankMeta['cnpj'],
					BKC::COL_AG_N       => $agencyNumber,
					BKC::COL_AG_DG      => $agencyDigit,

					// COA (opcional)
					BKC::COL_COA        => $coaId,

					// Saldos
					BKC::COL_OB         => round($openingBalance, 2),
					CHTC::CUR_BL        => round($currentBalance, 2),
					BKC::COL_AMT_STR    => round($stored, 2),
					BKC::COL_AM_LK      => round($locked, 2),

					// PIX
					BKC::COL_PIX_KEYS   => $pixKeys,
					BKC::COL_ACPT_PIX   => $acceptsPix,

					// Moeda
					BLC::COL_CUR_ID     => 'BRL',

					// Variados
					'vaults'            => $vaults,
					'restrictions'      => [
						'allow_negative_balances' => false,
						'transaction_limits'      => [
							'daily'   => 50_000.00,
							'monthly' => 500_000.00,
						],
					],
					'profile'           => [
						'business_segment' => $faker->randomElement(['services', 'retail', 'industry', 'general']),
						'risk_tier'        => $faker->randomElement(['low', 'medium', 'high']),
					],

					// Cartões
					BKC::COL_HAS_CRD       => $hasCredit,
					BKC::COL_CRD_CD        => $creditCards,
					BKC::COL_ACPTS_CRD_CD  => $acceptsCredit,

					BKC::COL_HAS_DBT       => $hasDebit,
					BKC::COL_DBT_CD        => $debitCards,
					BKC::COL_ACPTS_DBT_CD  => $acceptsDebit,

					BKC::COL_HAS_PND_STT   => $faker->boolean(25),

					// Flags
					BLC::COL_IS_PRM        => $i === 0, // marca a primeira como principal
					BKC::COL_RSK           => $faker->randomFloat(2, 0, 100),
					UC::COL_IA             => $faker->boolean(95),
					BKC::COL_INT_PRV       => $providers[$i % count($providers)],
					BLC::COL_SYNC_ER       => [],

					// Auditoria
					DC::TABLE_CREATOR      => $systemUserId,
				];

				// Critério de upsert: (Banco + Agência + Número da Conta)
				$unique = [
					BKC::COL_NM   => $payload[BKC::COL_NM],
					BKC::COL_AG_N => $payload[BKC::COL_AG_N],
					BKC::COL_ACC_N => $payload[BKC::COL_ACC_N],
				];

				$model = BankAccount::updateOrCreate($unique, $payload);
				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("BankAccountSeeder: created={$created}, updated={$updated}");
		}, 3);
	}

	private static function digits(int $len): string
	{
		$len = max(1, min(32, $len));
		$s   = '';
		for ($i = 0; $i < $len; $i++) $s .= random_int(0, 9);
		return $s;
	}

	private static function fakePixKeys($faker): array
	{
		$keys = [];
		$candidates = [
			['alias' => 'Email', 'type' => 'email', 'key' => $faker->safeEmail()],
			['alias' => 'CPF',   'type' => 'cpf',   'key' => self::digits(11)],
			['alias' => 'Cel',   'type' => 'phone', 'key' => preg_replace('/\D+/', '', $faker->cellphoneNumber())],
			['alias' => 'EVP',   'type' => 'evp',   'key' => (string) Str::uuid()],
		];
		foreach ($faker->randomElements($candidates, $faker->numberBetween(1, 3)) as $idx => $k) {
			$keys['key_' . ($idx + 1)] = [
				'alias'        => $k['alias'],
				'key'          => $k['key'],
				'type'         => $k['type'],
				'use_count'    => 0,
				'last_used_at' => null,
			];
		}
		return $keys;
	}

	private static function fakeCreditCard($faker): array
	{
		$brands = ['VISA', 'MASTER CARD', 'ELO', 'HIPERCARD', 'AMEX'];
		return [
			'alias'       => 'Main credit card',
			'masked_pan'  => self::digits(4) . ' **** **** ' . self::digits(4),
			'brand'       => $faker->randomElement($brands),
			'limit'       => $faker->randomFloat(2, 1_000, 30_000),
			'closing_day' => $faker->numberBetween(1, 28),
			'due_day'     => $faker->numberBetween(1, 28),
			'currency_id' => SC::DEF_SITE_CURRENCY_ID,
			'is_active'   => $faker->boolean(85),
		];
	}

	private static function fakeDebitCard($faker): array
	{
		$brands = ['VISA', 'MASTER CARD', 'ELO', 'MAESTRO'];
		return [
			'alias'       => 'Main debit card',
			'masked_pan'  => self::digits(4) . ' **** **** ' . self::digits(4),
			'brand'       => $faker->randomElement($brands),
			'daily_limit' => $faker->randomFloat(2, 500, 5_000),
			'currency_id' => SC::DEF_SITE_CURRENCY_ID,
			'is_active'   => $faker->boolean(90),
		];
	}
}
