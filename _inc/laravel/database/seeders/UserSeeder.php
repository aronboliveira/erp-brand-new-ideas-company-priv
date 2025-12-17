<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\PermissionsConstants as PMC;
use App\Config\Constants\SettingsConstants as SC;
use App\Enums\UserType;
use App\Models\{Branch as Br, Department as Dep, Designation as Dsg, User};
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
		try {
			// ------------------- Fixtures determinísticas (1 por tipo) -------------------
			$fixtures = [];
			foreach ($allowedTypeValues as $type) {
				$name = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
					? fake()->company()
					: (in_array($type, [UserType::Client->value], true)
						? (fake()->boolean(50) ? fake()->company() : fake()->name())
						: fake()->name());
				$fixtures[] = [
					UC::COL_NM  => "{$name} — System Fixture for {$type}",
					'phone' => fake()->unique()->phoneNumber(),
					UC::COL_EM  => fake()->unique()->safeEmail(),
					UC::COL_PW  => Hash::make('Password123!'),
					UC::COL_TP  => $type,
					UC::COL_SL  => Arr::random([1024.00, 2048.00, 5120.00, 10240.00]),
					UC::COL_LG  => config('app.locale', DC::DEFAULT_LANG),
					UC::COL_MD  => Arr::random(['light', 'dark']),
					UC::COL_D_ST => fake()->boolean(20) ? 0 : 1,
					UC::COL_A_ST => fake()->boolean(30) ? 1 : 0,
					UC::COL_DM  => Arr::random([0, 1]),
					UC::COL_IB  => fake()->boolean(10) ? 1 : 0,
					UC::COL_MC  => '#2180f3',
					// JSON será normalizado/codificado pelo Model (NormalizesArrays)
					'preferences' => [
						'theme'      => 'light',
						'lang'       => config('app.locale', DC::DEFAULT_LANG),
						'notify'     => ['email' => true, 'sms' => false],
						'timezone'   => config('app.timezone', 'America/Sao_Paulo'),
						'language'	 => DC::DEFAULT_LANG_LONG,
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
			while ($created < $target) {
				try {
					$type = Arr::random($allowedTypeValues);

					$name = in_array($type, [UserType::Vendor->value, UserType::Company->value], true)
						? fake()->company()
						: (in_array($type, [UserType::Client->value], true)
							? (fake()->boolean(50) ? fake()->company() : fake()->name())
							: fake()->name());
					$mode = fake()->randomElement(['light', 'dark']);
					$lang = fake()->randomElement([config('app.locale', DC::DEFAULT_LANG), 'pt-br', DC::DEFAULT_LANG, 'es']);

					// E-mail único e estável para evitar colisão com UNIQUE
					$emailLocal = Str::slug($name, '.') . '.' . Str::lower(Str::random(6));
					$email      = $emailLocal . '@example.test';

					$payload = [
						UC::COL_NM   => $name,
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
