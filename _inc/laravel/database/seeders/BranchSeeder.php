<?php

namespace Database\Seeders;

use App\Config\Constants\CompaniesConstants as CPC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\CountryName;
use App\Models\Branch as Br;
use App\Models\User as Usr;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

final class BranchSeeder extends Seeder
{
	use EnsuresSystemUser;
	// public const COL_HARD_CAP = 16000;
	public const COL_HARD_CAP = 4;

	public function run(): void
	{
		DB::transaction(function (): void {
			$clock = microtime(true);
			// private const SECONDS_LIMIT = 300;
			$SECONDS_LIMIT = 32;
			$systemUserId = $this->ensureSystemUser();
			$faker = fake('pt_BR');
			$fallback = fake('en_US');

			$userIds = Usr::query()->pluck('id')->all();
			$pickUser = function () use ($userIds, $systemUserId) {
				return $userIds ? $userIds[array_rand($userIds)] : $systemUserId;
			};
			$companyIds = Usr::query()->where('type', 'company')->pluck('id')->all();
			$pickCompany = function () use ($companyIds) {
				return $companyIds ? $companyIds[array_rand($companyIds)] : null;
			};

			$deptPool = ['financeiro', 'rh', 'ti', 'vendas', 'operacoes'];
			$safeDomains = ['example.com', 'example.net', 'empresa.test'];

			$countries = collect(CountryName::cases())->shuffle()->take(self::COL_HARD_CAP)->all();
			$created = 0;

			foreach ($countries as $countryCase) {
				if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
				if ($created >= self::COL_HARD_CAP) break;
				try {
					$countryName = $countryCase->value;
					$countryIso = CountryName::getIsoCode($countryName) ?? 'BR';

					do $branchId = Str::uuid()->toString();
					while (Br::where('id', $branchId)->exists());

					$branchName = $faker->company() ?: ('Branch ' . Str::upper(Str::random(8)));
					$state = $faker->stateAbbr() ?: $fallback->stateAbbr();
					$city  = $faker->city() ?: $fallback->city();
					$zip   = $faker->postcode() ?: $fallback->postcode();
					$street = $faker->streetAddress() ?: $fallback->streetAddress();
					$branchAddress = "{$street}, {$city}, {$state} {$zip}, {$countryName}";
					$branchPhone = $faker->phoneNumber() ?: $fallback->phoneNumber();
					$companySlug = Str::of($branchName)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
					$domain = $safeDomains[array_rand($safeDomains)];
					$branchEmail = ($companySlug ?: 'branch') . '_' . Str::lower(Str::random(10)) . '@' . $domain;

					$b = new Br();
					$b->id = $branchId;
					$b->company = $pickCompany();
					$b->{CPC::COL_BRC_NM} = $branchName;
					$b->country = $countryName;
					$b->state   = $state;
					$b->city    = $city;
					$b->zip     = $zip;
					$b->address = $branchAddress;
					$b->phone   = $branchPhone;
					$b->email   = $branchEmail;
					$b->{CPC::COL_FND} = $faker->name() ?: ('Founder ' . Str::upper(Str::random(6)));
					$b->{CPC::COL_ADM} = $pickUser();
					$b->{CPC::COL_MNG} = $pickUser();
					$b->description = $faker->boolean(55) ? $faker->sentence(10) : null;
					$n = random_int(2, min(5, count($deptPool)));
					$b->departments = implode(',', collect($deptPool)->shuffle()->take($n)->all());
					$b->budget   = $faker->randomFloat(2, 50_000, 2_000_000);
					$b->expenses = $faker->randomFloat(2, 10_000, 1_500_000);
					$b->profit   = max(0, (float) $b->budget - (float) $b->expenses);
					$b->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$b->setAttribute(DC::COL_TABLE_UPDATER, null);
					$b->save();
					$created++;
				} catch (\Throwable $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
			$elapsed = round(microtime(true) - $clock, 2);
			(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("[BranchSeeder] Done. Created: {$created} in {$elapsed}s");
		}, 3);
	}
}
