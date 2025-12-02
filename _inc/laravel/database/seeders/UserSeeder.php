<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\PermissionsConstants as PMC;
use App\Enums\UserType;
use App\Models\User;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
	/**
	 * Probabilidade média de preencher campos opcionais.
	 */
	private const OPTIONALITY = 0.65;

	public function run(): void
	{
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
		$target = 64 * $n;
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
				$label = Str::title(str_replace('_', ' ', $type));
				$fixtures[] = [
					UC::COL_NM  => "{$label} User",
					UC::COL_EM  => mb_strtolower($type) . '@example.test',
					UC::COL_PW  => Hash::make('Password123!'),
					UC::COL_TP  => $type,
					UC::COL_SL  => 1024.00,
					UC::COL_LG  => config('app.locale', 'en'),
					UC::COL_MD  => 'light',
					UC::COL_D_ST => 1,
					UC::COL_A_ST => 1,
					UC::COL_DM  => 0,
					UC::COL_IB  => 0,
					UC::COL_MC  => '#2180f3',
					// JSON será normalizado/codificado pelo Model (NormalizesArrays)
					'preferences' => [
						'theme'      => 'light',
						'lang'       => config('app.locale', 'en'),
						'notify'     => ['email' => true, 'sms' => false],
						'timezone'   => config('app.timezone', 'America/Sao_Paulo'),
					],
				];
			}

			foreach ($fixtures as $row) {
				// updateOrCreate por e-mail garante idempotência em dev/testes
				User::query()->updateOrCreate([UC::COL_EM => mb_strtolower($row[UC::COL_EM])], $row);
			}

			$created = count($fixtures);

			// ------------------- Massa aleatória até atingir $target -------------------
			while ($created < $target) {
				$type = Arr::random($allowedTypeValues);

				$name = fake()->name();
				$mode = fake()->randomElement(['light', 'dark']);
				$lang = fake()->randomElement([config('app.locale', 'en'), 'pt-br', 'en', 'es']);

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
				User::query()->create($payload);
				$created++;
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
