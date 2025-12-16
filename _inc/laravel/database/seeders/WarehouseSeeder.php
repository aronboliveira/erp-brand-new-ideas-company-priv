<?php

namespace Database\Seeders;

use App\Config\Constants\{CompaniesConstants as CC, UsersConstants as UC, DatabaseConstants as DC};
use App\Enums\Weekday;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
	// Parâmetro fixo: sem env/pseudo-env
	private const FAKE_COUNT = 32;

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

			// Get all admin/super admin users (these can be managers/supervisors)
			$adminUsers = $userPool->filter(fn($u) => in_array($u->type, ['admin', 'super admin', 'company', 'vendor']))
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

			// Create a map of user_id to employee_id for users who are also employees
			$userToEmployeeMap = [];
			foreach ($employeePool as $employee) {
				if ($employee->user_id) {
					$userToEmployeeMap[$employee->user_id] = $employee->id;
				}
			}

			// Get all admin users who are also employees (eligible for manager/supervisor roles)
			$eligibleAdminEmployees = [];
			foreach ($adminUsers as $adminUser) {
				if (isset($userToEmployeeMap[$adminUser->id])) {
					$eligibleAdminEmployees[] = (object)[
						'user_id' => $adminUser->id,
						'user_type' => $adminUser->type,
						'employee_id' => $userToEmployeeMap[$adminUser->id],
						'id'          => $adminUser->id,
						'name'        => $adminUser->name,
					];
				}
			}

			// Separate admin employees into admins and super admins for different roles
			$adminAdmins = array_filter($eligibleAdminEmployees, fn($a) => $a->user_type === 'admin');
			$superAdmins = array_filter($eligibleAdminEmployees, fn($a) => $a->user_type === 'super admin');

			// Select managers from admin users who are also employees
			$managerIds = [];
			$managerEmployeeIds = [];

			if (!empty($eligibleAdminEmployees)) {
				// Choose a manager (prefer super admins, fall back to admins)
				if (!empty($superAdmins)) {
					$selectedManager = fake()->randomElement($superAdmins);
				} else {
					$selectedManager = fake()->randomElement($eligibleAdminEmployees);
				}

				$managerIds = [$selectedManager->user_id];
				$managerEmployeeIds = [$selectedManager->employee_id];

				// Remove the selected manager from the pool for supervisors
				$eligibleForSupervisors = array_filter(
					$eligibleAdminEmployees,
					fn($a) => $a->user_id !== $selectedManager->user_id
				);
			} else {
				$eligibleForSupervisors = $eligibleAdminEmployees;
			}

			// Select supervisors from remaining admin users who are also employees
			$supervisorIds = [];
			$supervisorEmployeeIds = [];

			if (!empty($eligibleForSupervisors)) {
				$supervisorCount = fake()->numberBetween(1, min(3, count($eligibleForSupervisors)));

				// Get distinct supervisor user IDs
				$selectedSupervisors = fake()->randomElements($eligibleForSupervisors, $supervisorCount);

				$supervisorIds = array_map(fn($s) => $s->user_id, $selectedSupervisors);
				$supervisorEmployeeIds = array_map(fn($s) => $s->employee_id, $selectedSupervisors);
			}

			// Select regular employees (not managers or supervisors)
			$regularEmployeeIds = array_map(fn($e) => $e->id, $employeePool);
			$regularEmployeeIds = array_diff(
				$regularEmployeeIds,
				array_merge($managerEmployeeIds, $supervisorEmployeeIds)
			);

			// Now select the total employee list including all roles
			$allEmployeeIds = array_merge(
				$regularEmployeeIds,
				$managerEmployeeIds,
				$supervisorEmployeeIds
			);

			// Shuffle and select a subset of employees
			$employeeCount = fake()->numberBetween(1, min(5, count($allEmployeeIds)));
			$selectedEmployeeIds = fake()->randomElements($allEmployeeIds, $employeeCount);

			// Select partners
			$vendorIds = array_map(fn($v) => $v->id, $vendorPool);
			$partnerCount = fake()->numberBetween(1, min(3, count($vendorIds)));
			$partnerIds = $partnerCount > 0 ? fake()->randomElements($vendorIds, $partnerCount) : [];
			$ownerCandidates = !empty($adminAdmins)
				? $adminAdmins
				: $eligibleAdminEmployees;
			$mondayToFriday = array_map(fn($e) => $e->value, array_slice(Weekday::ordered(true), 0, 5));
			(new \Symfony\Component\Console\Output\ConsoleOutput
			)->writeln("Criando Armazém WRH-MAIN como fixture");
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
				try {
					Warehouse::query()->updateOrCreate(['code' => $data['code']], $data);
					$usedCodeSet[$data['code']] = true;
					$usedNameSet[$data['name']] = true;
					if (!empty($data['email'])) {
						$usedEmailSet[strtolower($data['email'])] = true;
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			// -------- MASSA ALEATÓRIA --------
			$count = self::FAKE_COUNT;

			for ($i = 0; $i < $count; $i++) {
				try {
					// código único
					do {
						$candidate = 'WRH-' . Str::upper(fake()->bothify('??-###'));
					} while (isset($usedCodeSet[$candidate]) || Warehouse::where('code', $candidate)->exists());
					$usedCodeSet[$code = $candidate] = true;

					// nome único
					do {
						$candidate = 'Armazém ' . fake()->city() . ' ' . fake()->numberBetween(1, 99);
					} while (isset($usedNameSet[$candidate]) || Warehouse::where('name', $candidate)->exists());
					$usedNameSet[$name = $candidate] = true;

					// email único (quando gerado)
					$email = null;
					if (fake()->boolean(70)) {
						$slug   = Str::of($code)->lower()->replace(['wrh-', '-'], '')->toString();
						$domain = fake()->freeEmailDomain();
						$emailCandidate = "wh-{$slug}@{$domain}";

						$suffix = 1;
						$emailUnique = $emailCandidate;
						while (isset($usedEmailSet[strtolower($emailUnique)]) || Warehouse::where('email', $emailUnique)->exists()) {
							$emailUnique = "wh-{$slug}-{$suffix}@" . fake()->freeEmailDomain();
							$suffix++;
						}
						$email = $emailUnique;
						$usedEmailSet[strtolower($email)] = true;
					}

					$owner = !empty($ownerCandidates)
						? fake()->randomElement($ownerCandidates)
						: null;
					// demais campos
					$state = fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS', 'SC', 'BA', 'PE']);
					$open  = fake()->randomElement(['07:00:00', '08:00:00', '09:00:00']);
					$close = fake()->randomElement(['16:00:00', '18:00:00', '20:00:00']);

					$width  = fake()->numberBetween(15, 80);
					$length = fake()->numberBetween(30, 150);
					$height = fake()->numberBetween(6, 12);
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Armazém {$code} com nome {$name}");
					$company = fake()->randomElement($companyPool);
					Warehouse::query()->create([
						'code'                  => $code,
						'name'                  => $name,
						CC::COL_CP_ID           => $company->id,
						'zip'                   => fake()->postcode(),
						'country'               => 'BR',
						'state'                 => $state,
						'city'                  => fake()->city(),
						'address'               => fake()->streetAddress(),
						CC::COL_ADR_DTL         => fake()->optional()->sentence(3),
						'notes'                 => fake()->optional(0.4)->sentence(8),
						'phone'                 => fake()->optional()->e164PhoneNumber(),
						'email'                 => $email,
						CC::COL_OWN_ID          => $owner->id,
						CC::COL_OWN_NM          => $owner->name,
						CC::COL_IA              => fake()->boolean(90),
						CC::COL_IS_SHP          => fake()->boolean(80),
						CC::COL_FD_DT           => fake()->optional()->date(),
						'dimensions'            => ['width' => $width, 'length' => $length, 'height' => $height, 'unit' => 'm'],
						'capacity'              => ['pallets' => fake()->numberBetween(150, 1500), 'kg' => fake()->numberBetween(20000, 120000)],
						'employees'             => $selectedEmployeeIds,
						'supervisors'           => $supervisorIds,
						'managers'              => $managerIds,
						'partners'              => $partnerIds,
						'sections'              => fake()->randomElements(['recebimento', 'expedição', 'estoque', 'inventário', 'cross-dock'], fake()->numberBetween(2, 4)),
						CC::COL_REACH           => fake()->randomElements(['SP', 'RJ', 'MG', 'ES', 'PR', 'SC', 'RS', 'GO'], fake()->numberBetween(1, 4)),
						CC::COL_OP_TM           => $open,
						CC::COL_CL_TM           => $close,
						CC::COL_WK_DYS          => $mondayToFriday,
						UC::COL_AVG_RT          => fake()->randomFloat(2, 3.5, 5.0),
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
