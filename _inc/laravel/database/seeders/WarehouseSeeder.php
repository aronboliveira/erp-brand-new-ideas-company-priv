<?php

namespace Database\Seeders;

use App\Config\Constants\{CompaniesConstants as CC, UsersConstants as UC};
use App\Enums\Weekday;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
	public function run(): void
	{
		// Evita cair em produção por engano
		if (app()->isProduction()) {
			Log::warning(self::class . ' skipped in production.');
			return;
		}

		// Reprodutibilidade
		fake()->seed(20251127);

		DB::beginTransaction();
		try {
			// Pré-carrega valores existentes para evitar colisões em execuções repetidas
			$usedCodes = Warehouse::query()->pluck('code')->filter()->map(fn($v) => (string)$v)->all();
			$usedNames = Warehouse::query()->pluck('name')->filter()->map(fn($v) => (string)$v)->all();
			$usedEmails = Warehouse::query()->pluck('email')->filter()->map(fn($v) => strtolower((string)$v))->all();

			$usedCodeSet  = array_fill_keys($usedCodes, true);
			$usedNameSet  = array_fill_keys($usedNames, true);
			$usedEmailSet = array_fill_keys($usedEmails, true);

			$mondayToFriday = array_map(fn($e) => $e->value, array_slice(Weekday::ordered(true), 0, 5));

			// -------- FIXTURES ESTÁVEIS (idempotentes) --------
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
				Warehouse::query()->updateOrCreate(['code' => $data['code']], $data);
				$usedCodeSet[$data['code']] = true;
				$usedNameSet[$data['name']] = true;
				if (!empty($data['email'])) {
					$usedEmailSet[strtolower($data['email'])] = true;
				}
			}

			// -------- MASSA ALEATÓRIA (UNICIDADE APENAS AQUI) --------
			$count = (int) (env('WAREHOUSE_FAKE_COUNT', 15));

			for ($i = 0; $i < $count; $i++) {
				// código único
				$code = null;
				do {
					$candidate = 'WRH-' . Str::upper(fake()->bothify('??-###'));
				} while (isset($usedCodeSet[$candidate]) || Warehouse::where('code', $candidate)->exists());
				$usedCodeSet[$code = $candidate] = true;

				// nome único
				$name = null;
				do {
					$candidate = 'Armazém ' . fake()->city() . ' ' . fake()->numberBetween(1, 99);
				} while (isset($usedNameSet[$candidate]) || Warehouse::where('name', $candidate)->exists());
				$usedNameSet[$name = $candidate] = true;

				// email único (quando gerado)
				$email = null;
				if (fake()->boolean(70)) {
					// formato determinístico com o code para minimizar colisão
					$candidate = Str::of($code)->lower()->replace(['wrh-', '-'], '')->toString();
					$emailCandidate = "wh-{$candidate}@" . fake()->freeEmailDomain();

					// se ainda assim existir, agrega sufixo numérico
					$suffix = 1;
					$emailUnique = $emailCandidate;
					while (isset($usedEmailSet[strtolower($emailUnique)]) || Warehouse::where('email', $emailUnique)->exists()) {
						$emailUnique = "wh-{$candidate}-{$suffix}@" . fake()->freeEmailDomain();
						$suffix++;
					}
					$email = $emailUnique;
					$usedEmailSet[strtolower($email)] = true;
				}

				// demais campos
				$state = fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS', 'SC', 'BA', 'PE']);
				$open  = fake()->randomElement(['07:00:00', '08:00:00', '09:00:00']);
				$close = fake()->randomElement(['16:00:00', '18:00:00', '20:00:00']);

				$width  = fake()->numberBetween(15, 80);
				$length = fake()->numberBetween(30, 150);
				$height = fake()->numberBetween(6, 12);

				Warehouse::query()->create([
					'code'                  => $code,
					'name'                  => $name,
					CC::COL_CP_ID          => null,
					'zip'                   => fake()->postcode(),
					'country'               => 'BR',
					'state'                 => $state,
					'city'                  => fake()->city(),
					'address'               => fake()->streetAddress(),
					CC::COL_ADR_DTL         => fake()->optional()->sentence(3),
					'notes'                 => fake()->optional(0.4)->sentence(8),
					'phone'                 => fake()->optional()->e164PhoneNumber(),
					'email'                 => $email,
					CC::COL_OWN_ID          => null,
					CC::COL_OWN_NM          => fake()->company(),
					CC::COL_IA              => fake()->boolean(90),
					CC::COL_IS_SHP          => fake()->boolean(80),
					CC::COL_FD_DT           => fake()->optional()->date(),
					'dimensions'            => ['width' => $width, 'length' => $length, 'height' => $height, 'unit' => 'm'],
					'capacity'              => ['pallets' => fake()->numberBetween(150, 1500), 'kg' => fake()->numberBetween(20000, 120000)],
					'employees'             => [],
					'supervisors'           => [],
					'managers'              => [],
					'partners'              => [],
					'sections'              => fake()->randomElements(['recebimento', 'expedição', 'estoque', 'inventário', 'cross-dock'], fake()->numberBetween(2, 4)),
					CC::COL_REACH           => fake()->randomElements(['SP', 'RJ', 'MG', 'ES', 'PR', 'SC', 'RS', 'GO'], fake()->numberBetween(1, 4)),
					CC::COL_OP_TM           => $open,
					CC::COL_CL_TM           => $close,
					CC::COL_WK_DYS          => $mondayToFriday, // já normalizado
					UC::COL_AVG_RT          => fake()->randomFloat(2, 3.5, 5.0), // o Model aplica clamp se preciso
				]);
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			Log::error(self::class . ' failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			throw $e;
		}
	}
}
