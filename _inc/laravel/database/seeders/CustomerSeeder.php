<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\UserType;
use App\Models\Customer;
use App\Models\Tax;
use App\Models\{User, Utility};
use App\Traits\EnsuresSystemUser;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CustomerSeeder extends Seeder
{
	public const MIN_CUSTOMER = 64;
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = Faker::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			$allTaxIds = Tax::query()->pluck('id')->map(fn($v) => (string) $v)->all();
			if (empty($allTaxIds)) {
				Log::warning(self::class . ': nenhum Tax encontrado; campos fiscais serão preenchidos com null.');
			}

			// $count   = max(User::where('type', 'customer')->count(), self::MIN_CUSTOMER * count(UserType::cases())); // ORIGINAL — unbounded
			$count   = min(2, max(User::where('type', 'customer')->count(), self::MIN_CUSTOMER * count(UserType::cases()))); // HARD CAP
			$created = 0;
			$updated = 0;

			for ($i = 0; $i < $count; $i++) {
				try {
					$name   = $faker->name();
					$safeAcc = 0;
					do {
						if ($safeAcc > $count * 1000) throw new \Exception("Too many attempts to generate unique email");
						$email  = $faker->username() . '_' . Str::random(8) . "@" . $faker->freeEmailDomain();
						$safeAcc++;
					} while (Customer::query()->where('email', $email)->exists());
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Cliente: {$name}, Email: {$email}");
					// --- Dados base
					$isVip  = $faker->boolean(20);
					$rating = $isVip ? $faker->randomFloat(2, 4.2, 5.0) : $faker->randomFloat(2, 3.3, 4.9);

					// --- Identidade web / social
					$website      = 'https://' . Str::slug($faker->domainName());
					$handleBase   = Str::lower(Str::slug($name, ''));
					$whatsDigits  = preg_replace('/\D+/', '', $faker->cellphoneNumber());
					$socialArray  = [
						'instagram' => 'https://instagram.com/' . $handleBase,
						'linkedin'  => 'https://www.linkedin.com/company/' . $handleBase,
						'x'         => 'https://x.com/' . $handleBase,
						'facebook'  => $faker->boolean(70) ? 'https://facebook.com/' . $handleBase : null,
						'youtube'   => $faker->boolean(25) ? 'https://youtube.com/@' . $handleBase : null,
						'whatsapp'  => 'https://wa.me/' . $whatsDigits,
						'site'      => $website,
					];
					// remove nulos e serializa para JSON
					$socialMedia = json_encode(
						array_filter($socialArray, fn($v) => !empty($v)),
						JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
					);

					// --- Imposto principal e adicionais
					$taxMain = null;
					$otherTaxIds = [];
					if (!empty($allTaxIds)) {
						$taxMain = $faker->randomElement($allTaxIds);
						$pool = array_values(array_diff($allTaxIds, [$taxMain]));
						if (!empty($pool)) {
							$qty  = min($faker->numberBetween(0, 2), count($pool));
							if ($qty > 0) {
								$keys = (array) array_rand($pool, $qty);
								foreach ($keys as $k) {
									$otherTaxIds[] = $pool[$k];
								}
							}
						}
					}

					// --- Endereço de entrega
					$shipName = $name;
					$shipCtr  = 'BR';
					$shipSt   = $faker->stateAbbr();
					$shipCty  = $faker->city();
					$shipZip  = preg_replace('/\D+/', '', $faker->postcode());
					$shipAdr  = $faker->streetAddress();
					$shipDtl  = $faker->boolean(30) ? ', ' . $faker->secondaryAddress() : '';
					$shipTel  = preg_replace('/\D+/', '', $faker->cellphoneNumber());

					// --- Endereço de cobrança (50% igual ao de entrega)
					$billSame = $faker->boolean(50);
					$billName = $billSame ? $shipName : $name . ' - Financeiro';
					$billCtr  = $billSame ? $shipCtr  : 'BR';
					$billSt   = $billSame ? $shipSt   : $faker->stateAbbr();
					$billCty  = $billSame ? $shipCty  : $faker->city();
					$billZip  = $billSame ? $shipZip  : preg_replace('/\D+/', '', $faker->postcode());
					$billAdr  = $billSame ? $shipAdr  : $faker->streetAddress();
					$billDtl  = $billSame ? $shipDtl  : ($faker->boolean(30) ? ', ' . $faker->secondaryAddress() : '');
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
						'name'                  => $name,
						'email'                 => $email,

						// NOVOS CAMPOS
						'phone'                 => Utility::generateBrazilianPhone() ?? $faker->phoneNumber(),
						'website'               => $website,
						'social_media'          => $socialMedia, // jsonificado aqui

						// Fiscais
						BC::COL_TX_N            => $taxMain,
						BC::COL_OT_TX_ID        => $otherTaxIds,

						'contact'               => $faker->firstName() . ' ' . $faker->lastName(),
						'avatar'                => '',

						BC::COL_OD_C            => $faker->numberBetween(0, 120),
						BC::COL_IS_PRM          => $isVip,
						UC::COL_IA              => $faker->boolean(92),
						UC::COL_AVG_RT          => $rating,
						'preferences'           => $preferences,
						'balance'               => $isVip
							? $faker->randomFloat(2, 0, 5_000)
							: $faker->randomFloat(2, 0, 2_000),

						UC::COL_EM_V_AT         => $faker->boolean(70)
							? now()->subDays($faker->numberBetween(1, 365))->format('Y-m-d H:i:s')
							: null,

						// Shipping
						BC::COL_SHIP_NAME       => $shipName,
						BC::COL_SHIP_CTR        => $shipCtr,
						BC::COL_SHIP_ZIP        => $shipZip,
						BC::COL_SHIP_ADR        => $shipAdr,
						BC::COL_SHIP_ST         => $shipSt,
						BC::COL_SHIP_CTY        => $shipCty,
						BC::COL_SHIP_TEL        => $shipTel,
						BC::COL_SHIP_DTL        => ltrim($shipDtl, ', '),

						// Billing
						BC::COL_BL_NAME         => $billName,
						BC::COL_BL_EMAIL        => $billMail,
						BC::COL_BL_TEL          => $billTel,
						BC::COL_BL_ZIP          => $billZip,
						BC::COL_BL_ADR          => $billAdr,
						BC::COL_BL_ST           => $billSt,
						BC::COL_BL_CTY          => $billCty,
						BC::COL_BL_CTR          => $billCtr,
						BC::COL_BL_DTL          => ltrim($billDtl, ', '),

						'lang'                  => 'pt_BR',

						// Relacionamento com Users
						BC::COL_CST_ID          => $systemUserId,

						// Auditoria
						DC::COL_TABLE_CREATOR   => $systemUserId,
					];

					/** @var Customer|null $model */
					$model = Customer::query()->where('email', $email)->first();

					if ($model) {
						$model->fill($payload)->save();
						$updated++;
					} else {
						Customer::create($payload);
						$created++;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("CustomerSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
