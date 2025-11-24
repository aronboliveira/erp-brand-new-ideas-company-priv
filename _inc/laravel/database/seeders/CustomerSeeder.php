<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\Customer;
use App\Traits\EnsuresSystemUser;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CustomerSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = Faker::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			$count   = 30; // total de registros mock
			$created = 0;
			$updated = 0;

			for ($i = 0; $i < $count; $i++) {
				// --- Dados base
				$name   = $faker->unique()->name();
				$email  = Str::lower(Str::slug($name, '.')) . '@' . $faker->freeEmailDomain();
				$isVip  = $faker->boolean(20);
				$rating = $isVip ? $faker->randomFloat(2, 4.2, 5.0) : $faker->randomFloat(2, 3.3, 4.9);

				// --- Documento fiscal principal (11 = CPF, 14 = CNPJ, apenas com número de dígitos válidos)
				$taxLen  = $faker->randomElement([11, 14]);
				$taxMain = self::digits($taxLen);

				// --- Demais documentos (0–2)
				$otherTaxIds = [];
				foreach (range(1, $faker->numberBetween(0, 2)) as $_) {
					$otherTaxIds[] = self::digits($faker->randomElement([11, 14]));
				}

				// --- Endereço de entrega
				$shipName = $name;
				$shipCtr  = 'BR';
				$shipSt   = $faker->stateAbbr();
				$shipCty  = $faker->city();
				$shipZip  = preg_replace('/\D+/', '', $faker->postcode()); // CEP somente dígitos
				$shipAdr  = $faker->streetAddress();
				$shipTel  = preg_replace('/\D+/', '', $faker->cellphoneNumber());

				// --- Endereço de cobrança (50% igual ao de entrega)
				$billSame = $faker->boolean(50);
				$billName = $billSame ? $shipName : $name . ' - Financeiro';
				$billCtr  = $billSame ? $shipCtr  : 'BR';
				$billSt   = $billSame ? $shipSt   : $faker->stateAbbr();
				$billCty  = $billSame ? $shipCty  : $faker->city();
				$billZip  = $billSame ? $shipZip  : preg_replace('/\D+/', '', $faker->postcode());
				$billAdr  = $billSame ? $shipAdr  : $faker->streetAddress();
				$billTel  = $billSame ? $shipTel  : preg_replace('/\D+/', '', $faker->cellphoneNumber());
				$billMail = $billSame ? $email    : 'financeiro+' . Str::random(6) . '@' . $faker->freeEmailDomain();

				// --- Preferências e flags
				$preferences = [
					'comm_channels' => $faker->randomElements(['email', 'sms', 'whatsapp', 'phone'], $faker->numberBetween(1, 3)),
					'newsletter'    => $faker->boolean(35),
					'locale'        => 'pt_BR',
					'currency'      => 'BRL',
				];

				$payload = [
					'name'                => $name,
					'email'               => $email,
					BC::COL_TX_N          => $taxMain,
					BC::COL_OT_TX_ID      => $otherTaxIds,
					'contact'             => $faker->firstName() . ' ' . $faker->lastName(),
					'avatar'              => '',

					BC::COL_OD_C          => $faker->numberBetween(0, 120),
					BC::COL_IS_PRM        => $isVip,
					UC::COL_IA            => $faker->boolean(92),
					UC::COL_AVG_RT        => $rating,
					'preferences'         => $preferences,
					'balance'             => $isVip
						? $faker->randomFloat(2, 0, 5_000)
						: $faker->randomFloat(2, 0, 2_000),

					// Verificação de e-mail (70% dos casos)
					UC::COL_EM_V_AT       => $faker->boolean(70)
						? now()->subDays($faker->numberBetween(1, 365))->format('Y-m-d H:i:s')
						: null,

					// Shipping
					BC::COL_SHIP_NAME     => $shipName,
					BC::COL_SHIP_CTR      => $shipCtr,
					BC::COL_SHIP_ZIP      => $shipZip,
					BC::COL_SHIP_ADR      => $shipAdr,
					BC::COL_SHIP_ST       => $shipSt,
					BC::COL_SHIP_CTY      => $shipCty,
					BC::COL_SHIP_TEL      => $shipTel,

					// Billing
					BC::COL_BL_NAME       => $billName,
					BC::COL_BL_EMAIL      => $billMail,
					BC::COL_BL_TEL        => $billTel,
					BC::COL_BL_ZIP        => $billZip,
					BC::COL_BL_ADR        => $billAdr,
					BC::COL_BL_ST         => $billSt,
					BC::COL_BL_CTY        => $billCty,
					BC::COL_BL_CTR        => $billCtr,

					// Idioma
					'lang'                => 'pt_BR',

					// Relacionamento com Users (foreign BC::COL_CST_ID)
					BC::COL_CST_ID        => $systemUserId,

					// Auditoria
					DC::TABLE_CREATOR     => $systemUserId,
				];

				/** @var Customer $model */
				$model = Customer::query()->where('email', $email)->first();

				if ($model) {
					$model->fill($payload)->save();
					$updated++;
				} else {
					Customer::create($payload);
					$created++;
				}
			}

			Log::info("CustomerSeeder: created={$created}, updated={$updated}");
		}, 3);
	}

	/**
	 * Gera uma string numérica com $len dígitos.
	 */
	private static function digits(int $len): string
	{
		$len = max(1, min(32, $len));
		$s   = '';
		for ($i = 0; $i < $len; $i++) {
			$s .= random_int(0, 9);
		}
		return $s;
	}
}
