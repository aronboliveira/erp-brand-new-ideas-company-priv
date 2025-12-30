<?php

namespace Database\Seeders;

use App\Config\Constants\{CompaniesConstants as CC, UsersConstants as UC, DatabaseConstants as DC};
use App\Enums\Weekday;
use App\Enums\CountryName;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
	private const FAKE_COUNT = 32;

	public function run(): void
	{
		if (app()->isProduction()) {
			Log::warning(self::class . ' skipped in production.');
			return;
		}

		fake()->seed(20251127);

		DB::beginTransaction();
		try {
			$usedCodes  = Warehouse::query()->pluck('code')->filter()->map(fn($v) => (string)$v)->all();
			$usedNames  = Warehouse::query()->pluck('name')->filter()->map(fn($v) => (string)$v)->all();
			$usedEmails = Warehouse::query()->pluck('email')->filter()->map(fn($v) => strtolower((string)$v))->all();

			$usedCodeSet  = array_fill_keys($usedCodes, true);
			$usedNameSet  = array_fill_keys($usedNames, true);
			$usedEmailSet = array_fill_keys($usedEmails, true);

			$userPool = DB::table(DC::TABLE_USERS)
				->select(['id', 'name', 'type'])
				->get();

			$companyPool = $userPool->filter(fn($u) => $u->type === 'company')
				->values()
				->all();

			$adminUsers = $userPool->filter(fn($u) => in_array($u->type, ['admin', 'super admin', 'company', 'vendor'], true))
				->values()
				->all();

			$employeePool = DB::table(DC::TABLE_EMPLOYEES)
				->select(['id', 'name', 'user_id'])
				->get()
				->all();

			$vendorPool = DB::table(DC::TABLE_VENDORS)
				->select(['id', 'name'])
				->get()
				->all();

			$userToEmployeeMap = [];
			foreach ($employeePool as $employee) {
				if ($employee->user_id) {
					$userToEmployeeMap[$employee->user_id] = $employee->id;
				}
			}

			$eligibleAdminEmployees = [];
			foreach ($adminUsers as $adminUser) {
				if (isset($userToEmployeeMap[$adminUser->id])) {
					$eligibleAdminEmployees[] = (object)[
						'user_id'     => $adminUser->id,
						'user_type'   => $adminUser->type,
						'employee_id' => $userToEmployeeMap[$adminUser->id],
						'id'          => $adminUser->id,
						'name'        => $adminUser->name,
					];
				}
			}

			$adminAdmins = array_values(array_filter($eligibleAdminEmployees, fn($a) => $a->user_type === 'admin'));
			$superAdmins = array_values(array_filter($eligibleAdminEmployees, fn($a) => $a->user_type === 'super admin'));

			$managerIds = [];
			$managerEmployeeIds = [];

			if (!empty($eligibleAdminEmployees)) {
				$selectedManager = !empty($superAdmins)
					? fake()->randomElement($superAdmins)
					: fake()->randomElement($eligibleAdminEmployees);

				$managerIds = [$selectedManager->user_id];
				$managerEmployeeIds = [$selectedManager->employee_id];

				$eligibleForSupervisors = array_values(array_filter(
					$eligibleAdminEmployees,
					fn($a) => $a->user_id !== $selectedManager->user_id
				));
			} else {
				$eligibleForSupervisors = $eligibleAdminEmployees;
			}

			$supervisorIds = [];
			$supervisorEmployeeIds = [];

			if (!empty($eligibleForSupervisors)) {
				$supervisorCount = fake()->numberBetween(1, min(3, count($eligibleForSupervisors)));
				$selectedSupervisors = fake()->randomElements($eligibleForSupervisors, $supervisorCount);

				$supervisorIds = array_values(array_map(fn($s) => $s->user_id, $selectedSupervisors));
				$supervisorEmployeeIds = array_values(array_map(fn($s) => $s->employee_id, $selectedSupervisors));
			}

			$regularEmployeeIds = array_map(fn($e) => $e->id, $employeePool);
			$regularEmployeeIds = array_values(array_diff(
				$regularEmployeeIds,
				array_merge($managerEmployeeIds, $supervisorEmployeeIds)
			));

			$allEmployeeIds = array_values(array_merge(
				$regularEmployeeIds,
				$managerEmployeeIds,
				$supervisorEmployeeIds
			));

			$employeeCount = !empty($allEmployeeIds)
				? fake()->numberBetween(1, min(5, count($allEmployeeIds)))
				: 0;
			$selectedEmployeeIds = $employeeCount > 0
				? fake()->randomElements($allEmployeeIds, $employeeCount)
				: [];

			$vendorIds = array_map(fn($v) => $v->id, $vendorPool);
			$partnerCount = !empty($vendorIds) ? fake()->numberBetween(1, min(3, count($vendorIds))) : 0;
			$partnerIds = $partnerCount > 0 ? fake()->randomElements($vendorIds, $partnerCount) : [];

			$ownerCandidates = !empty($adminAdmins)
				? $adminAdmins
				: $eligibleAdminEmployees;

			$mondayToFriday = array_map(fn($e) => $e->value, array_slice(Weekday::ordered(true), 0, 5));

			$pickLocaleForCountry = function (CountryName $country): string {
				return match ($country) {
					CountryName::Brazil        => 'pt_BR',
					CountryName::UnitedStates  => 'en_US',
					CountryName::Canada        => 'en_CA',
					CountryName::UnitedKingdom => 'en_GB',
					CountryName::Germany       => 'de_DE',
					CountryName::France        => 'fr_FR',
					CountryName::Spain         => 'es_ES',
					CountryName::Portugal      => 'pt_PT',
					CountryName::Italy         => 'it_IT',
					CountryName::Argentina     => 'es_AR',
					CountryName::Chile         => 'es_CL',
					CountryName::Mexico        => 'es_MX',
					CountryName::Japan         => 'ja_JP',
					CountryName::China         => 'zh_CN',
					CountryName::India         => 'en_IN',
					CountryName::Australia     => 'en_AU',
					CountryName::SouthAfrica   => 'en_ZA',
					CountryName::Denmark       => 'da_DK',
					CountryName::Netherlands   => 'nl_NL',
					CountryName::Poland        => 'pl_PL',
					CountryName::SaudiArabia   => 'ar_SA',
					CountryName::Turkey        => 'tr_TR',
					CountryName::Israel        => 'he_IL',
					CountryName::Russia        => 'ru_RU',
					CountryName::Switzerland   => 'de_CH',
					CountryName::Belgium       => 'fr_BE',
					CountryName::Austria       => 'de_AT',
					CountryName::Taiwan        => 'zh_TW',
					CountryName::Colombia      => 'es_CO',
					CountryName::Peru          => 'es_PE',
					CountryName::Venezuela     => 'es_VE',
					CountryName::Norway        => 'nb_NO',
					CountryName::Sweden        => 'sv_SE',
					CountryName::Finland       => 'fi_FI',
					CountryName::Greece        => 'el_GR',
					CountryName::CzechRepublic => 'cs_CZ',
					CountryName::Hungary       => 'hu_HU',
					CountryName::Romania       => 'ro_RO',
					CountryName::Bolivia       => 'es_BO',
					CountryName::Ecuador       => 'es_EC',
					CountryName::Paraguay      => 'es_PY',
					CountryName::Uruguay       => 'es_UY',
					CountryName::FrenchGuiana  => 'fr_GF',
					CountryName::Guyana,
					CountryName::Suriname => 'en_US',
				};
			};

			$pickWeightedCountry = function () {
				$weighted = [
					[CountryName::Brazil, 70],
					[CountryName::UnitedStates, 8],
					[CountryName::Portugal, 4],
					[CountryName::Argentina, 3],
					[CountryName::Mexico, 2],
					[CountryName::Chile, 2],
					[CountryName::Canada, 2],
					[CountryName::UnitedKingdom, 2],
					[CountryName::Germany, 2],
					[CountryName::France, 2],
					[CountryName::Spain, 2],
					[CountryName::Italy, 2],
					[CountryName::Australia, 2],
					[CountryName::Japan, 1],
					[CountryName::China, 1],
					[CountryName::India, 1],
					[CountryName::SouthAfrica, 1],
					[CountryName::Denmark, 1],
					[CountryName::Netherlands, 1],
					[CountryName::Poland, 1],
					[CountryName::SaudiArabia, 1],
					[CountryName::Turkey, 1],
					[CountryName::Israel, 1],
					[CountryName::Russia, 1],
					[CountryName::Switzerland, 1],
					[CountryName::Belgium, 1],
					[CountryName::Austria, 1],
					[CountryName::Taiwan, 1],
					[CountryName::Colombia, 1],
					[CountryName::Peru, 1],
					[CountryName::Venezuela, 1],
					[CountryName::Norway, 1],
					[CountryName::Sweden, 1],
					[CountryName::Finland, 1],
					[CountryName::Greece, 1],
					[CountryName::CzechRepublic, 1],
					[CountryName::Hungary, 1],
					[CountryName::Romania, 1],
					[CountryName::Bolivia, 1],
					[CountryName::Ecuador, 1],
					[CountryName::Guyana, 1],
					[CountryName::Paraguay, 1],
					[CountryName::Suriname, 1],
					[CountryName::Uruguay, 1],
					[CountryName::FrenchGuiana, 1],
				];

				$total = 0;
				foreach ($weighted as $w) $total += $w[1];

				$r = random_int(1, max(1, $total));
				$acc = 0;
				foreach ($weighted as [$country, $weight]) {
					$acc += $weight;
					if ($r <= $acc) return $country;
				}
				return CountryName::Brazil;
			};

			$makeFaker = function (string $locale) {
				try {
					return \Faker\Factory::create($locale);
				} catch (\Throwable) {
					return \Faker\Factory::create('en_US');
				}
			};

			$brStates = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
			$usStates = ['AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'];

			(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando Armazém WRH-MAIN como fixture");

			$fixtures = [
				[
					'code'             => 'WRH-MAIN',
					'name'             => 'Armazém Central',
					'zip'              => '01001-000',
					'country'          => 'BR',
					'state'            => 'SP',
					'city'             => 'São Paulo',
					'address'          => 'Rua Exemplo, 100',
					CC::COL_ADR_DTL    => 'Galpão A',
					'notes'            => 'Unidade principal — recebimento/expedição.',
					'phone'            => '+55 11 1234-5678',
					'email'            => 'contato@empresa.test',
					CC::COL_OWN_ID     => null,
					CC::COL_OWN_NM     => 'Empresa LTDA',
					CC::COL_IA         => true,
					CC::COL_IS_SHP     => true,
					CC::COL_FD_DT      => now()->subYears(5)->toDateString(),
					'dimensions'       => ['width' => 50, 'length' => 120, 'height' => 8, 'unit' => 'm'],
					'capacity'         => ['pallets' => 1200, 'kg' => 100000],
					'employees'        => [],
					'supervisors'      => [],
					'managers'         => [],
					'partners'         => [],
					'sections'         => ['recebimento', 'expedição', 'estoque'],
					CC::COL_REACH      => ['SP', 'RJ', 'MG'],
					CC::COL_OP_TM      => '08:00:00',
					CC::COL_CL_TM      => '18:00:00',
					CC::COL_WK_DYS     => $mondayToFriday,
					UC::COL_AVG_RT     => 4.75,
				],
				[
					'code'             => 'WRH-RJ-01',
					'name'             => 'Depósito Rio 01',
					'zip'              => '20040-020',
					'country'          => 'BR',
					'state'            => 'RJ',
					'city'             => 'Rio de Janeiro',
					'address'          => 'Av. das Américas, 500',
					CC::COL_ADR_DTL    => 'Bloco 2, Módulo 5',
					'notes'            => null,
					'phone'            => '+55 21 3333-2222',
					'email'            => 'rj01@empresa.test',
					CC::COL_OWN_ID     => null,
					CC::COL_OWN_NM     => 'Empresa LTDA',
					CC::COL_IA         => true,
					CC::COL_IS_SHP     => true,
					CC::COL_FD_DT      => now()->subYears(2)->toDateString(),
					'dimensions'       => ['width' => 30, 'length' => 80, 'height' => 7, 'unit' => 'm'],
					'capacity'         => ['pallets' => 600, 'kg' => 60000],
					'employees'        => [],
					'supervisors'      => [],
					'managers'         => [],
					'partners'         => [],
					'sections'         => ['expedição', 'estoque'],
					CC::COL_REACH      => ['RJ', 'ES'],
					CC::COL_OP_TM      => '09:00:00',
					CC::COL_CL_TM      => '18:00:00',
					CC::COL_WK_DYS     => $mondayToFriday,
					UC::COL_AVG_RT     => 4.30,
				],
			];

			foreach ($fixtures as $data) {
				try {
					Warehouse::query()->updateOrCreate(['code' => $data['code']], $data);
					$usedCodeSet[$data['code']] = true;
					$usedNameSet[$data['name']] = true;
					if (!empty($data['email'])) $usedEmailSet[strtolower($data['email'])] = true;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			$count = self::FAKE_COUNT;

			for ($i = 0; $i < $count; $i++) {
				try {
					do {
						$candidate = 'WRH-' . Str::upper(fake()->bothify('??-###'));
					} while (isset($usedCodeSet[$candidate]) || Warehouse::where('code', $candidate)->exists());
					$usedCodeSet[$code = $candidate] = true;

					$countryEnum = $pickWeightedCountry();
					$countryIso  = CountryName::getIsoCode($countryEnum->value) ?? 'BR';
					$locale      = $pickLocaleForCountry($countryEnum);
					$f           = $makeFaker($locale);

					$cityForName = method_exists($f, 'city') ? $f->city() : fake()->city();
					do {
						$candidate = 'Armazém ' . $cityForName . ' ' . $f->numberBetween(1, 99);
					} while (isset($usedNameSet[$candidate]) || Warehouse::where('name', $candidate)->exists());
					$usedNameSet[$name = $candidate] = true;

					$email = null;
					if ($f->boolean(70)) {
						$slug   = Str::of($code)->lower()->replace(['wrh-', '-'], '')->toString();
						$domain = method_exists($f, 'freeEmailDomain') ? $f->freeEmailDomain() : fake()->freeEmailDomain();
						$emailCandidate = "wh-{$slug}@{$domain}";

						$suffix = 1;
						$emailUnique = $emailCandidate;
						while (isset($usedEmailSet[strtolower($emailUnique)]) || Warehouse::where('email', $emailUnique)->exists()) {
							$domain2 = method_exists($f, 'freeEmailDomain') ? $f->freeEmailDomain() : fake()->freeEmailDomain();
							$emailUnique = "wh-{$slug}-{$suffix}@{$domain2}";
							$suffix++;
						}
						$email = $emailUnique;
						$usedEmailSet[strtolower($email)] = true;
					}

					$owner = !empty($ownerCandidates)
						? fake()->randomElement($ownerCandidates)
						: null;

					$state = null;
					if ($countryIso === 'BR') {
						$state = $f->randomElement($brStates);
					} elseif ($countryIso === 'US') {
						$state = $f->randomElement($usStates);
					} else {
						try {
							$state = method_exists($f, 'stateAbbr') ? $f->stateAbbr() : (method_exists($f, 'state') ? $f->state() : null);
						} catch (\Throwable) {
							$state = null;
						}
					}

					$open  = $f->randomElement(['07:00:00', '08:00:00', '09:00:00']);
					$close = $f->randomElement(['16:00:00', '18:00:00', '20:00:00']);

					$width  = $f->numberBetween(15, 80);
					$length = $f->numberBetween(30, 150);
					$height = $f->numberBetween(6, 12);

					$zip = null;
					try {
						$zip = $f->postcode();
					} catch (\Throwable) {
						$zip = fake()->postcode();
					}

					$city = null;
					try {
						$city = $f->city();
					} catch (\Throwable) {
						$city = fake()->city();
					}

					$address = null;
					try {
						$address = $f->streetAddress();
					} catch (\Throwable) {
						$address = fake()->streetAddress();
					}

					$phone = null;
					try {
						$phone = method_exists($f, 'e164PhoneNumber') ? $f->e164PhoneNumber() : fake()->e164PhoneNumber();
					} catch (\Throwable) {
						$phone = fake()->e164PhoneNumber();
					}

					$reachPool = $countryIso === 'BR'
						? $brStates
						: ($countryIso === 'US' ? $usStates : array_values(array_filter([$state, 'REG-01', 'REG-02', 'REG-03'])));

					$reach = !empty($reachPool)
						? $f->randomElements($reachPool, $f->numberBetween(1, min(4, count($reachPool))))
						: [];

					$company = !empty($companyPool)
						? fake()->randomElement($companyPool)
						: null;

					(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando Armazém {$code} com nome {$name} ({$countryIso}/{$locale})");

					Warehouse::query()->create([
						'code'                  => $code,
						'name'                  => $name,
						CC::COL_CP_ID           => $company?->id,
						'zip'                   => $zip,
						'country'               => $countryIso,
						'state'                 => $state,
						'city'                  => $city,
						'address'               => $address,
						CC::COL_ADR_DTL         => $f->optional()->sentence(3),
						'notes'                 => $f->optional(0.4)->sentence(8),
						'phone'                 => $f->optional()->passthrough($phone),
						'email'                 => $email,
						CC::COL_OWN_ID          => $owner?->id,
						CC::COL_OWN_NM          => $owner?->name,
						CC::COL_IA              => $f->boolean(90),
						CC::COL_IS_SHP          => $f->boolean(80),
						CC::COL_FD_DT           => $f->optional()->date(),
						'dimensions'            => ['width' => $width, 'length' => $length, 'height' => $height, 'unit' => 'm'],
						'capacity'              => ['pallets' => $f->numberBetween(150, 1500), 'kg' => $f->numberBetween(20000, 120000)],
						'employees'             => $selectedEmployeeIds,
						'supervisors'           => $supervisorIds,
						'managers'              => $managerIds,
						'partners'              => $partnerIds,
						'sections'              => $f->randomElements(['recebimento', 'expedição', 'estoque', 'inventário', 'cross-dock'], $f->numberBetween(2, 4)),
						CC::COL_REACH           => $reach,
						CC::COL_OP_TM           => $open,
						CC::COL_CL_TM           => $close,
						CC::COL_WK_DYS          => $mondayToFriday,
						UC::COL_AVG_RT          => $f->randomFloat(2, 3.5, 5.0),
					]);
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			Log::error(self::class . ' failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			throw $e;
		}
	}
}
