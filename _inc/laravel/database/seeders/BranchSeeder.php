<?php

namespace Database\Seeders;

use App\Config\Constants\CompaniesConstants as CPC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\CountryName;
use App\Models\Branch as Br;
use App\Models\User as Usr;
use App\Traits\EnsuresSystemUser;
use App\Traits\UsesCountryRegions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

final class BranchSeeder extends Seeder
{
	use EnsuresSystemUser;
	public const COL_HARD_CAP = 16000;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();
			$output = new \Symfony\Component\Console\Output\ConsoleOutput();
			$userIds = Usr::query()->pluck('id')->all();
			$pickUser = function () use ($userIds, $systemUserId) {
				return $userIds ? $userIds[array_rand($userIds)] : $systemUserId;
			};

			$companyIds = Usr::query()->where('type', 'company')->pluck('id')->all();
			$pickCompany = function () use ($companyIds) {
				return $companyIds ? $companyIds[array_rand($companyIds)] : null;
			};

			$deptPool = [
				'financeiro',
				'rh',
				'ti',
				'vendas',
				'operacoes',
				'marketing',
				'juridico',
				'logistica',
				'compras',
				'suporte',
				'administrativo',
				'qualidade',
				'projetos',
				'estrategia',
				'compliance',
				'inovacao',
				'desenvolvimento',
				'planejamento',
				'relacionamento',
				'servico_ao_cliente',
			];

			$makeDepartments = function () use ($deptPool) {
				$n = random_int(2, min(5, count($deptPool)));
				return implode(',', collect($deptPool)->shuffle()->take($n)->all());
			};

			$localeForIso = static function (?string $iso): string {
				$cc = strtoupper(trim((string) $iso));
				return match ($cc) {
					'BR' => 'pt_BR',
					'US' => 'en_US',
					'CA' => 'en_CA',
					'GB' => 'en_GB',
					'DE' => 'de_DE',
					'FR' => 'fr_FR',
					'ES' => 'es_ES',
					'PT' => 'pt_PT',
					'IT' => 'it_IT',
					'AR' => 'es_AR',
					'CL' => 'es_CL',
					'MX' => 'es_MX',
					'CO' => 'es_CO',
					'PE' => 'es_PE',
					'VE' => 'es_VE',
					'BO' => 'es_BO',
					'EC' => 'es_EC',
					'PY' => 'es_PY',
					'UY' => 'es_UY',
					'GY' => 'en_GY',
					'GF' => 'fr_FR',
					'JP' => 'ja_JP',
					'CN' => 'zh_CN',
					'TW' => 'zh_TW',
					'IN' => 'en_IN',
					'AU' => 'en_AU',
					'ZA' => 'en_ZA',
					'DK' => 'da_DK',
					'NL' => 'nl_NL',
					'PL' => 'pl_PL',
					'SA' => 'ar_SA',
					'TR' => 'tr_TR',
					'IL' => 'he_IL',
					'RU' => 'ru_RU',
					'NO' => 'no_NO',
					'SE' => 'sv_SE',
					'FI' => 'fi_FI',
					'GR' => 'el_GR',
					'CZ' => 'cs_CZ',
					'HU' => 'hu_HU',
					'RO' => 'ro_RO',
					default => 'en_US',
				};
			};

			$countryIsoFor = static function (CountryName $country): ?string {
				return CountryName::getIsoCode($country->value);
			};

			$weightFor = static function (CountryName $country): int {
				return match ($country) {
					CountryName::Brazil => 650,

					CountryName::Argentina,
					CountryName::Chile,
					CountryName::Uruguay,
					CountryName::Paraguay,
					CountryName::Bolivia,
					CountryName::Peru,
					CountryName::Colombia,
					CountryName::Ecuador,
					CountryName::Venezuela,
					CountryName::Guyana,
					CountryName::Suriname,
					CountryName::FrenchGuiana => 120,

					CountryName::UnitedStates,
					CountryName::Canada => 80,

					CountryName::Mexico => 70,

					CountryName::Portugal,
					CountryName::Spain,
					CountryName::France,
					CountryName::Germany,
					CountryName::Italy,
					CountryName::Netherlands,
					CountryName::Belgium,
					CountryName::Switzerland,
					CountryName::Austria,
					CountryName::Denmark,
					CountryName::Poland,
					CountryName::CzechRepublic,
					CountryName::Hungary,
					CountryName::Romania,
					CountryName::Norway,
					CountryName::Sweden,
					CountryName::Finland,
					CountryName::Greece,
					CountryName::UnitedKingdom => 55,

					CountryName::SouthAfrica => 45,

					CountryName::Turkey,
					CountryName::Israel,
					CountryName::SaudiArabia,
					CountryName::Russia => 35,

					CountryName::India => 30,
					CountryName::Japan => 28,
					CountryName::China,
					CountryName::Taiwan => 26,

					CountryName::Australia => 20,
				};
			};

			$pickWeightedCountry = static function (array $countries, array $weights): CountryName {
				$total = 0;
				foreach ($countries as $c) $total += max(1, (int) ($weights[$c->value] ?? 1));
				$r = random_int(1, max(1, $total));
				$acc = 0;
				foreach ($countries as $c) {
					$acc += max(1, (int) ($weights[$c->value] ?? 1));
					if ($r <= $acc) return $c;
				}
				return $countries[array_key_last($countries)];
			};

			$safeFaker = static function (string $locale) {
				try {
					return fake($locale);
				} catch (\Throwable) {
					try {
						return fake('en_US');
					} catch (\Throwable) {
						return fake('pt_BR');
					}
				}
			};

			$safeFakerCall = static function (object $faker, string $method, array $args = []) {
				try {
					return $faker->{$method}(...$args);
				} catch (\Throwable) {
					return null;
				}
			};

			$val = static function (array $candidates) {
				foreach ($candidates as $v) {
					if ($v === null) continue;
					$s = is_string($v) ? trim($v) : $v;
					if ($s !== '' && $s !== []) return $s;
				}
				return null;
			};

			$randomTwoWords = static function (object $faker, object $fallback) use ($val, $safeFakerCall): string {
				$w = $val([
					$safeFakerCall($faker, 'words', [2, true]),
					$safeFakerCall($fallback, 'words', [2, true]),
				]);
				$w = is_string($w) ? trim(preg_replace('/\s+/', ' ', $w)) : '';
				return $w !== '' ? $w : ('Region ' . Str::upper(Str::random(6)));
			};

			$enumClassForCountryIso = static function (?string $iso): ?string {
				$cc = strtoupper(trim((string) $iso));
				$map = defined(UsesCountryRegions::class . '::STATE_ENUMS') ? UsesCountryRegions::STATE_ENUMS : [];
				$cls = is_array($map) ? ($map[$cc] ?? null) : null;
				return is_string($cls) && $cls !== '' && enum_exists($cls) && method_exists($cls, 'cases') ? $cls : null;
			};

			$randomStateFromEnum = static function (string $enumCls): ?string {
				try {
					$cases = $enumCls::cases();
					if (!$cases) return null;
					$e = $cases[array_rand($cases)];
					return $e instanceof \BackedEnum ? (string) $e->value : null;
				} catch (\Throwable) {
					return null;
				}
			};

			$randomStateFromFaker = static function (object $faker, object $fallback) use ($val, $safeFakerCall): ?string {
				$methods = [
					'stateAbbr',
					'state',
					'stateCode',
					'province',
					'region',
					'county',
				];
				foreach ($methods as $m) {
					$v = $val([$safeFakerCall($faker, $m), $safeFakerCall($fallback, $m)]);
					if (is_string($v) && trim($v) !== '') return trim($v);
				}
				return null;
			};

			$maybeCityFromBrBucket = static function (?string $countryIso, ?string $stateValue): ?string {
				$cc = strtoupper(trim((string) $countryIso));
				if ($cc !== 'BR') return null;

				$st = strtoupper(Str::ascii(trim((string) $stateValue)));
				$st = match ($st) {
					'MG', 'MINASGERAIS', 'MINAS GERAIS' => 'MG',
					'SP', 'SAOPAULO', 'SÃO PAULO', 'SAO PAULO' => 'SP',
					'RJ', 'RIODEJANEIRO', 'RIO DE JANEIRO' => 'RJ',
					default => null,
				};
				if ($st === null) return null;

				$data = defined(UsesCountryRegions::class . '::CITIES_BY_STATE') ? UsesCountryRegions::CITIES_BY_STATE : null;
				$list = is_array($data) ? (($data['BR'][$st]['normalized'] ?? null)) : null;
				if (!is_array($list) || !$list) return null;

				$pick = $list[array_rand($list)];
				$pick = is_scalar($pick) ? trim((string) $pick) : '';
				return $pick !== '' ? $pick : null;
			};

			$safeDomains = [
				'example.com',
				'example.net',
				'example.org',
				'empresa.test',
				'corp.test',
				'mail.test',
			];

			$countries = CountryName::cases();
			$countryCount = count($countries);

			$companyCount = max(1, count($companyIds));
			$rows = max($countryCount, random_int(2, 8) * $companyCount);

			$weights = [];
			foreach ($countries as $c) $weights[$c->value] = $weightFor($c);

			$sequence = collect($countries)->shuffle()->values()->all();
			while (count($sequence) < $rows) $sequence[] = $pickWeightedCountry($countries, $weights);

			$acc = 0;
			$retryAcc = 0;
			foreach ($sequence as $countryCase) {
				$acc++;
				$retryAcc++;
				if ($acc > self::COL_HARD_CAP) break;
				if ($retryAcc > 256) continue;
				try {
					$countryCase = $countryCase instanceof CountryName ? $countryCase : (CountryName::normalize((string) $countryCase) ?? CountryName::Brazil);
					$countryName = $countryCase->value;
					$countryIso = $countryIsoFor($countryCase) ?? 'BR';

					$locale = $localeForIso($countryIso);
					$faker = $safeFaker($locale);
					$fallback = $safeFaker('en_US');

					do $branchId = Str::uuid()->toString();
					while (Br::where('id', $branchId)->exists());

					do $branchName = (string) $val([
						$safeFakerCall($faker, 'company'),
						$safeFakerCall($fallback, 'company'),
						'Branch ' . Str::upper(Str::random(8)),
					]);
					while (Br::where(CPC::COL_BRC_NM, $branchName)->exists());

					$enumCls = $enumClassForCountryIso($countryIso);
					$state = $enumCls ? $randomStateFromEnum($enumCls) : null;
					$state ??= $randomStateFromFaker($faker, $fallback);
					$state ??= $randomTwoWords($faker, $fallback);

					$city = $maybeCityFromBrBucket($countryIso, $state);
					$city ??= (string) $val([
						$safeFakerCall($faker, 'city'),
						$safeFakerCall($faker, 'cityName'),
						$safeFakerCall($fallback, 'city'),
						$safeFakerCall($fallback, 'cityName'),
						$randomTwoWords($faker, $fallback),
					]);

					$zip = (string) $val([
						$safeFakerCall($faker, 'postcode'),
						$safeFakerCall($faker, 'postalCode'),
						$safeFakerCall($fallback, 'postcode'),
						$safeFakerCall($fallback, 'postalCode'),
						Str::upper(Str::random(2)) . '-' . random_int(10000, 99999),
					]);

					$street = (string) $val([
						$safeFakerCall($faker, 'streetAddress'),
						$safeFakerCall($faker, 'address'),
						$safeFakerCall($fallback, 'streetAddress'),
						$safeFakerCall($fallback, 'address'),
						'Street ' . random_int(1, 999),
					]);

					do $branchAddress = $street . ', ' . $city . ', ' . $state . ' ' . $zip . ', ' . $countryName;
					while (Br::where('address', $branchAddress)->exists());

					$branchPhone = (string) $val([
						$safeFakerCall($faker, 'cellphoneNumber'),
						$safeFakerCall($faker, 'phoneNumber'),
						$safeFakerCall($faker, 'e164PhoneNumber'),
						$safeFakerCall($fallback, 'phoneNumber'),
						$safeFakerCall($fallback, 'e164PhoneNumber'),
						'+55 11 ' . random_int(2000, 9999) . '-' . random_int(1000, 9999),
					]);

					do $branchPhone = (string) $branchPhone;
					while (Br::where('phone', $branchPhone)->exists());

					$companySlug = Str::of((string) $branchName)
						->lower()
						->ascii()
						->replaceMatches('/[^a-z0-9]+/', '_')
						->trim('_')
						->toString();

					$domain = $safeDomains[array_rand($safeDomains)];
					do $branchEmail = ($companySlug ?: 'branch') . '_' . Str::lower(Str::random(10)) . '@' . $domain;
					while (Br::where('email', $branchEmail)->exists());

					$admId = $pickUser();
					$mngId = $pickUser();
					$companyId = $pickCompany();

					$b = new Br();
					$b->id = $branchId;
					$b->company = $companyId;
					$b->{CPC::COL_BRC_NM} = $branchName;

					$b->country = $countryName;
					$b->state = $state;
					$b->city = $city;
					$b->zip = $zip;
					$b->address = $branchAddress;

					$b->phone = $branchPhone;
					$b->email = $branchEmail;

					$b->{CPC::COL_FND} = (string) $val([
						$safeFakerCall($faker, 'name'),
						$safeFakerCall($fallback, 'name'),
						'Founder ' . Str::upper(Str::random(6)),
					]);

					$b->{CPC::COL_ADM} = $admId;
					$b->{CPC::COL_MNG} = $mngId;

					$b->description = (bool) $val([$safeFakerCall($faker, 'boolean', [60]), $safeFakerCall($fallback, 'boolean', [60]), true])
						? (string) $val([$safeFakerCall($faker, 'sentence', [10]), $safeFakerCall($fallback, 'sentence', [10]), null])
						: null;

					$b->departments = $makeDepartments();
					$b->budget = (float) $val([$safeFakerCall($faker, 'randomFloat', [2, 50_000, 2_000_000]), $safeFakerCall($fallback, 'randomFloat', [2, 50_000, 2_000_000]), 0.0]);
					$b->expenses = (float) $val([$safeFakerCall($faker, 'randomFloat', [2, 10_000, 1_500_000]), $safeFakerCall($fallback, 'randomFloat', [2, 10_000, 1_500_000]), 0.0]);
					$b->profit = max(0, (float) $b->budget - (float) $b->expenses);

					$b->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$b->setAttribute(DC::COL_TABLE_UPDATER, null);
					$output->writeln('Creating branch [' . $branchId . '] ' . $branchName . ' (' . $countryName . ', ' . $state . ', ' . $city . ') from company . ' . (DB::table(DC::TABLE_USERS)->where('id', $companyId)->value('name') ?? 'Unnamed') . '[' . ($companyId ?? 'Unidentified') . ']');
					$b->save();
				} catch (\Throwable $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
