<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\PermissionsConstants as PMC;
use App\Config\Constants\SettingsConstants as SC;
use App\Enums\UserType;
use App\Models\{Branch as Br, Department as Dep, Designation as Dsg, User, Utility};
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
	/**
	 * Probabilidade média de preencher campos opcionais.
	 */
	private const OPTIONALITY = 0.65;

	public function run(?string $phase = 'initial'): void
	{
		$clock = microtime(true);
		// private const SECONDS_LIMIT = 300;
		$SECONDS_LIMIT = 32;
		// Sanidade da tabela
		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('Tabela ' . DC::TABLE_USERS . ' ausente. Seeder abortado.');
			return;
		}

		// * ensuring all existing users have cpfs and cnpjs
		$usersPool = DB::table(DC::TABLE_USERS);
		$unidentifiedUsers = $usersPool->whereNull(UC::COL_ENT_CD)->orWhere(UC::COL_ENT_CD, '')->get();
		foreach ($unidentifiedUsers as $user) {
			if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
			$isCnpjCandidate = in_array($user->type, [UserType::Vendor->value, UserType::Company->value], true)
				? true : (in_array($user->type, [UserType::Client->value], true) ? fake()->boolean(50) : false);
			do $codeIdentifier = $isCnpjCandidate ? Utility::generateRandomCnpj() : Utility::generateRandomCpf();
			while (User::query()->where(UC::COL_ENT_CD, $codeIdentifier)->exists());
			DB::table(DC::TABLE_USERS)->where('id', $user->id)->update([
				UC::COL_ENT_CD => $codeIdentifier,
				UC::COL_ENT_TP => $isCnpjCandidate ? 'cnpj' : 'cpf',
			]);
		}

		$languageMappings = [
			'pt-br' => 'Português brasileiro',
			'pt' => 'Português',
			'es' => 'Espanhol',
			'fr' => 'Francês',
			'de' => 'Alemão',
		];
		$allowedCases       = array_values(array_filter(UserType::cases(), fn($c) => $c !== UserType::SuperAdmin));
		$allowedTypeValues  = array_filter(array_map(fn(UserType $c) => $c->value, $allowedCases), fn($v) => $v !== PMC::SA);

		$branchIds = Br::query()->pluck('id')->all();
		$deptIds   = Dep::query()->pluck('id')->all();
		$dsgIds    = Dsg::query()->pluck('id')->all();
		$minLate = min(1, count($deptIds) * 2, count($branchIds) * DepartmentSeeder::MIN_DEPTS_PER_BRANCH) * DepartmentSeeder::MIN_DSG_PER_DEPT;
		$maxLate = max(2, count($deptIds) * count($branchIds), count($branchIds) * DepartmentSeeder::MAX_DEPTS_PER_BRANCH) * DepartmentSeeder::MAX_DSG_PER_DEPT;
		// $target = min(6400, $target);
		$target = 4;

		$maybe = fn(callable $fn) => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? $fn() : null;

		DB::beginTransaction();
		$faker = fake(locale: config('app.locale', 'pt_BR'));
		try {
			// ------------------- Fixtures determinísticas (1 por tipo, até target) -------------------
			$fixtures = [];
			$lang = $faker->boolean(80) ? config('app.locale', DC::DEFAULT_LANG) : array_rand(array_unique(['pt-br', 'pt', 'es']), 1);
			$fixtureTypes = array_slice(array_values($allowedTypeValues), 0, $target);
			foreach ($fixtureTypes as $type) {
				if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
				$name = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
					? $faker->company()
					: (in_array($type, [UserType::Client->value], true)
						? ($faker->boolean(50) ? $faker->company() : $faker->name())
						: $faker->name());
				$isCnpjCandidate = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
					? true : (in_array($type, [UserType::Client->value], true) ? $faker->boolean(50) : false);
				do $codeIdentifier = $isCnpjCandidate ? Utility::generateRandomCnpj() : Utility::generateRandomCpf();
				while (User::query()->where(UC::COL_ENT_CD, $codeIdentifier)->exists());
				$fixtures[] = [
					UC::COL_NM  => "{$name} — System Fixture for {$type}",
					UC::COL_ENT_CD => $codeIdentifier,
					UC::COL_ENT_TP => $isCnpjCandidate ? 'cnpj' : 'cpf',
					'phone' => $faker->boolean(50) ? Utility::generateBrazilianPhone() : $faker->unique()->phoneNumber(),
					'address' => $faker->address(),
					UC::COL_EM  => $faker->unique()->safeEmail(),
					UC::COL_PW  => Hash::make('Password123!'),
					UC::COL_TP  => $type,
					UC::COL_SL  => Arr::random([1024.00, 2048.00, 5120.00]),
					UC::COL_LG  => $lang,
					UC::COL_MD  => Arr::random(['light', 'dark']),
					UC::COL_D_ST => fake()->boolean(20) ? 0 : 1,
					UC::COL_A_ST => fake()->boolean(30) ? 1 : 0,
					UC::COL_DM  => Arr::random([0, 1]),
					UC::COL_IB  => fake()->boolean(10) ? 1 : 0,
					UC::COL_MC  => Arr::random(['#2180f3', '#f3214d', '#21f38c']),
					'preferences' => [
						'theme'      => Arr::random(['light', 'dark']),
						'lang'       => $lang,
						'notify'     => ['email' => true, 'sms' => false],
						'timezone'   => config('app.timezone', 'America/Sao_Paulo'),
						'language'	 => $faker->boolean(80) ? DC::DEFAULT_LANG_LONG : ($languageMappings[$lang] ?? DC::DEFAULT_LANG_LONG),
					],
				];
			}
			foreach ($fixtures as $row) {
				User::query()->updateOrCreate([UC::COL_EM => mb_strtolower($row[UC::COL_EM])], $row);
			}

			$created = count($fixtures);
			DB::commit();
			$elapsed = round(microtime(true) - $clock, 2);
			(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("[UserSeeder] Done. Created: {$created} in {$elapsed}s");
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('UserSeeder falhou: ' . $e->getMessage());
			throw $e;
		}
	}
}
