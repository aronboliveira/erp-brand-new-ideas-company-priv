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
		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		// Sanidade da tabela
		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('Tabela ' . DC::TABLE_USERS . ' ausente. Seeder abortado.');
			return;
		}

		// * ensuring all existing users have cpfs and cnpjs

		$usersPool = DB::table(DC::TABLE_USERS);
		$unidentifiedUsers = $usersPool->whereNull(UC::COL_ENT_CD)->orWhere(UC::COL_ENT_CD, '')->get();
		foreach ($unidentifiedUsers as $user) {
			$codeIdentifier = null;
			$isCnpjCandidate = in_array($user->type, [UserType::Vendor->value, UserType::Company->value], true)
				? true : (in_array($user->type, [UserType::Client->value], true) ? fake()->boolean(50) : false);
			do $codeIdentifier = $isCnpjCandidate ? Utility::generateRandomCnpj() : Utility::generateRandomCpf();
			while (User::query()->where(UC::COL_ENT_CD, $codeIdentifier)->exists());
			DB::table(DC::TABLE_USERS)->where('id', $user->id)->update([
				UC::COL_ENT_CD => $codeIdentifier,
				UC::COL_ENT_TP => $isCnpjCandidate ? 'cnpj' : 'cpf',
			]);
			$output->writeln("Updated user {$user->id} with " . ($isCnpjCandidate ? 'CNPJ' : 'CPF') . " {$codeIdentifier}");
		}

		$languageMappings = [
			'pt-br' => 'Português brasileiro',
			'pt' => 'Português',
			'es' => 'Espanhol',
			'fr' => 'Francês',
			'de' => 'Alemão',
			'it' => 'Italiano',
			'zh' => 'Chinês',
			'ja' => 'Japonês',
			'ru' => 'Russo',
			'pl' => 'Polonês',
			'da' => 'Dinamarquês',
			'nl' => 'Holandês',
			'ar' => 'Árabe',
			'tr' => 'Turco',
			'he' => 'Hebraico',
		];
		// Tipos permitidos (exclui SuperAdmin para mock)
		$allowedCases       = array_values(array_filter(UserType::cases(), fn($c) => $c !== UserType::SuperAdmin));
		$allowedTypeValues  = array_filter(array_map(fn(UserType $c) => $c->value, $allowedCases), fn($v) => $v !== PMC::SA);
		$n                  = max(1, count($allowedTypeValues));

		// Regra global do projeto p/ total: --count OU (64 * n)
		$branchIds = Br::query()->pluck('id')->all();
		$deptIds   = Dep::query()->pluck('id')->all();
		$dsgIds    = Dsg::query()->pluck('id')->all();
		$minLate = min(1, count($deptIds) * 2, count($branchIds) * DepartmentSeeder::MIN_DEPTS_PER_BRANCH) * DepartmentSeeder::MIN_DSG_PER_DEPT;
		$maxLate = max(2, count($deptIds) * count($branchIds), count($branchIds) * DepartmentSeeder::MAX_DEPTS_PER_BRANCH) * DepartmentSeeder::MAX_DSG_PER_DEPT;
		$target = max((min(160 * (floor(log10(count($deptIds) ?: 1)) + 1), random_int($minLate, $maxLate)) + VendorSeeder::MIN_VENDORS + CustomerSeeder::MIN_CUSTOMER + ClientSeeder::MIN_CLIENTS) * (count(array_filter(UserType::cases(), fn($c) => $c !== UserType::SuperAdmin && $c !== UserType::Vendor && $c !== UserType::Customer && $c !== UserType::Client)) ?: 1), count($deptIds) * max(3, count(array_filter(UserType::cases(), fn($c) => $c !== UserType::SuperAdmin && $c !== UserType::Vendor && $c !== UserType::Customer && $c !== UserType::Client)) ?: 3));
		$phase === 'initial' ? Log::warning($target . ' Usuários iniciais criados') : Log::warning(
			'UserSeeder tardio definido para ' . $target . ' usuários.',
			['branch_count' => count($branchIds), 'dept_count' => count($deptIds), 'dsg_count' => count($dsgIds), 'calculated_n' => $n]
		);
		$target = min(6400, $target); // hard cap
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$target = $opt;
			}
		}

		$maybe = fn(callable $fn) => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? $fn() : null;

		DB::beginTransaction();
		$faker = fake(locale: config('app.locale', 'pt_BR'));
		try {
			// ------------------- Fixtures determinísticas (1 por tipo) -------------------
			$fixtures = [];
			$lang = $faker->boolean(80) ? config('app.locale', DC::DEFAULT_LANG) : array_rand(array_unique(['pt-br', 'pt', 'es', 'fr', 'de', 'it', 'zh', 'ja', 'ru', 'pl', 'da', 'nl', 'ar', 'tr', 'he']), 1);
			foreach ($allowedTypeValues as $type) {
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
					'address' => $faker->streetAddress() . ', ' .
						$faker->city() . ', ' .
						$faker->stateAbbr() . ' ' .
						$faker->postcode() . ', ' .
						$faker->country(),
					UC::COL_EM  => $faker->unique()->safeEmail(),
					UC::COL_PW  => Hash::make('Password123!'),
					UC::COL_TP  => $type,
					UC::COL_SL  => Arr::random([1024.00, 2048.00, 5120.00, 10240.00]),
					UC::COL_LG  => $lang,
					UC::COL_MD  => Arr::random(['light', 'dark']),
					UC::COL_D_ST => fake()->boolean(20) ? 0 : 1,
					UC::COL_A_ST => fake()->boolean(30) ? 1 : 0,
					UC::COL_DM  => Arr::random([0, 1]),
					UC::COL_IB  => fake()->boolean(10) ? 1 : 0,
					UC::COL_MC  => Arr::random(['#2180f3', '#f3214d', '#21f38c', '#f3e021', '#8c21f3', '#f38c21', '#21d4f3', '#ffffff', '#000000', '#777777']),
					// JSON será normalizado/codificado pelo Model (NormalizesArrays)
					'preferences' => [
						'theme'      => Arr::random(['light', 'dark']),
						'lang'       => $lang,
						'notify'     => ['email' => true, 'sms' => false],
						'timezone'   => config('app.timezone', 'America/Sao_Paulo'),
						'language'	 => $faker->boolean(80) ? DC::DEFAULT_LANG_LONG : ($languageMappings[$lang] ?? DC::DEFAULT_LANG_LONG),
					],
				];
			}
			$fixtAcc = 0;
			foreach ($fixtures as $row) {
				if ($fixtAcc === 0 || $fixtAcc % 10 === 0)
					$output->writeln('Running...' . $row[UC::COL_NM] . ', a ' . $row[UC::COL_TP]);
				// updateOrCreate por e-mail garante idempotência em dev/testes
				User::query()->updateOrCreate([UC::COL_EM => mb_strtolower($row[UC::COL_EM])], $row);
				$fixtAcc++;
			}

			$created = count($fixtures);

			// ------------------- Massa aleatória até atingir $target -------------------
			$targAcc = 0;
			$faker = fake(locale: config('app.locale', 'pt_BR'));
			while ($created < $target) {
				try {
					$type = Arr::random($allowedTypeValues);

					$name = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
						? $faker->company()
						: (in_array($type, [UserType::Client->value], true)
							? ($faker->boolean(50) ? $faker->company() : $faker->name())
							: $faker->name());
					$mode = $faker->randomElement(['light', 'dark']);
					$lang = $faker->boolean(80) ? config('app.locale', DC::DEFAULT_LANG) : array_rand(array_unique(['pt-br', 'pt', 'es', 'fr', 'de', 'it', 'zh', 'ja', 'ru', 'pl', 'da', 'nl', 'ar', 'tr', 'he']), 1);

					// E-mail único e estável para evitar colisão com UNIQUE
					$emailLocal = Str::slug($name, '.') . '.' . Str::lower(Str::random(6));
					$email      = $emailLocal . '@example.test';
					$isCnpjCandidate = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
						? true : (in_array($type, [UserType::Client->value], true) ? $faker->boolean(50) : false);
					do $codeIdentifier = $isCnpjCandidate ? Utility::generateRandomCnpj() : Utility::generateRandomCpf();
					while (User::query()->where(UC::COL_ENT_CD, $codeIdentifier)->exists());
					$payload = [
						UC::COL_NM   => $name,
						UC::COL_ENT_CD => $codeIdentifier,
						UC::COL_ENT_TP => $isCnpjCandidate ? 'cnpj' : 'cpf',
						'phone' 	=> fake()->boolean(75) ? Utility::generateBrazilianPhone() : $faker->unique()->phoneNumber(),
						'address' 	=> $faker->streetAddress() . ', ' .
							$faker->city() . ', ' .
							$faker->stateAbbr() . ' ' .
							$faker->postcode() . ', ' .
							$faker->country(),
						UC::COL_EM   => $email, // normalizado em saving()
						UC::COL_PW   => Hash::make('Password123!'),
						UC::COL_TP   => $type,
						UC::COL_SL   => fake()->randomFloat(2, 800.00, 12000.00),
						UC::COL_LG   => $lang,
						UC::COL_MD   => $mode,
						UC::COL_D_ST => fake()->numberBetween(0, 2),
						UC::COL_A_ST => $maybe(fn() => 1) ?? 1,
						UC::COL_DM   => $mode === 'dark' ? 1 : 0,
						UC::COL_IB   => fake()->numberBetween(0, 25),
						UC::COL_LLA  => $maybe(fn() => Carbon::now()->subDays(fake()->numberBetween(0, 180))->toDateTimeString()),
						UC::COL_PED  => $maybe(fn() => Carbon::now()->addDays(fake()->numberBetween(10, 730))->toDateString()),
						UC::COL_MC   => $maybe(fn() => fake()->hexColor()) ?? '#2180f3',
						UC::COL_EM_V_AT => $maybe(fn() => Carbon::now()->subDays(fake()->numberBetween(0, 365))->toDateTimeString()),
						// Preferências (salvas como JSON por hook do Model)
						'preferences' => [
							'theme'      => $mode,
							'lang'       => $lang,
							'timezone'   => config('app.timezone', 'America/Sao_Paulo'),
							'notify'     => ['email' => fake()->boolean(80), 'sms' => fake()->boolean(15)],
							'shortcuts'  => ['open_cmd' => 'Ctrl+K', 'toggle_theme' => 'Ctrl+D'],
							'language'	 => $faker->boolean(80) ? DC::DEFAULT_LANG_LONG : ($languageMappings[$lang] ?? DC::DEFAULT_LANG_LONG),
						],
					];

					// Usa Eloquent para disparar boot/saving (normalização de e-mail e JSON)
					if ($targAcc === 0 || $targAcc % 50 === 0)
						$output->writeln('Running... ' . $payload[UC::COL_NM] . ', a ' . $payload[UC::COL_TP]);
					User::query()->create($payload);
					$targAcc++;
					$created++;
				} catch (\Exception $e) {
					$created++;
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			DB::commit();
			$this->command?->info("UserSeeder: {$created} usuários inseridos/atualizados em " . DC::TABLE_USERS . ".");
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('UserSeeder falhou: ' . $e->getMessage());
			throw $e;
		}
	}
}
