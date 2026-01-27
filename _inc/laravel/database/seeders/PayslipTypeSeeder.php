<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{Frequency, JobLevel, UserType};
use App\Models\PayslipType as Pst;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class PayslipTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$min = BC::COL_MIN_AMT;
			$max = BC::COL_MAX_AMT;

			$base = [
				['name' => 'Mensalista',      'desc' => $faker->sentence(),           $min => null,   $max => null,   'roles' => null],
				['name' => 'Horista',         'desc' => $faker->sentence(),           $min => null,   $max => null,   'roles' => null],
				['name' => 'Comissão',        'desc' => $faker->sentence(),           $min => '0',    $max => '20000', 'roles' => [UserType::Vendor->value, UserType::Admin->value]],
				['name' => 'Bônus',           'desc' => $faker->sentence(),           $min => '0',    $max => '10000', 'roles' => null],
				['name' => 'PLR',             'desc' => $faker->sentence(),           $min => '0',    $max => '50000', 'roles' => [UserType::Company->value, UserType::Accountant->value, UserType::Admin->value]],
				['name' => 'Adiantamento',    'desc' => $faker->sentence(),           $min => '0',    $max => '5000', 'roles' => [UserType::Company->value, UserType::Admin->value]],
				['name' => 'Periculosidade',  'desc' => 'Adicional por risco.',      $min => null,   $max => null,   'roles' => null],
				['name' => 'Insalubridade',   'desc' => 'Adicional por insalubr.',   $min => null,   $max => null,   'roles' => null],
				['name' => 'Gratificação',    'desc' => $faker->sentence(),           $min => '0',    $max => '8000', 'roles' => null],
				['name' => 'Auxílio',         'desc' => 'Auxílios (VT, VR, VA etc.)', $min => '0',    $max => '3000', 'roles' => null],
			];

			foreach (Frequency::cases() as $frequency) {
				$roles = fake()->boolean(25)
					? null
					: array_slice(
						array_map(fn($case) => $case->value, JobLevel::cases()),
						0,
						$faker->numberBetween(1, count(JobLevel::cases()))
					);

				$base[] = [
					'name' => $frequency->value,
					'desc' => $faker->sentence(),
					$min   => $faker->randomNumber(3, true),
					$max   => $faker->randomNumber(5, true),
					'roles' => $roles
				];
			}

			// Pad to power of 2 (minimum 21)
			while (log(count($base), 2) % 1 !== 0 || count($base) < 21) {
				$candidateName = fake()->unique()->words(2, true);
				if (in_array($candidateName, array_column($base, 'name'))) continue;

				$roles = fake()->boolean(25)
					? null
					: array_slice(
						array_map(fn($case) => $case->value, JobLevel::cases()),
						0,
						$faker->numberBetween(1, count(JobLevel::cases()))
					);

				$base[] = [
					'name' => $candidateName,
					'desc' => $faker->sentence(),
					$min   => $faker->randomNumber(2, true),
					$max   => $faker->randomNumber(5, true),
					'roles' => $roles
				];
			}

			// Insert base types
			foreach ($base as $row) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput())
						->writeln("Criando Tipo de Folha de Pagamento: {$row['name']}");

					if (Pst::where('name', $row['name'])->exists()) {
						continue;
					}

					do $typeId = Str::uuid()->toString();
					while (Pst::where('id', $typeId)->exists());

					$m = new Pst();
					$m->id          = $typeId;
					$m->name        = $row['name'];
					$m->description = $row['desc'];
					$m->{$min}      = $row[$min];
					$m->{$max}      = $row[$max];

					// Use the attribute name that triggers the mutator
					$m->roles_applicable = $row['roles'];

					$m->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$m->setAttribute(DC::COL_TABLE_UPDATER, null);
					$m->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			// Hierarchy-based types
			$hierarchy = [
				UserType::Client->value,
				UserType::Customer->value,
				UserType::Vendor->value,
				UserType::Accountant->value,
				UserType::Company->value,
				UserType::Admin->value,
				UserType::SuperAdmin->value,
			];

			foreach ($hierarchy as $i => $role) {
				try {
					$rolesUpToHere = array_slice($hierarchy, 0, $i + 1);
					$name = "Tipo Remuneratório — " . ucfirst($role);

					if (Pst::where('name', $name)->exists()) {
						continue;
					}

					do $typeId = Str::uuid()->toString();
					while (Pst::where('id', $typeId)->exists());

					$m = new Pst();
					$m->id          = $typeId;
					$m->name        = $name;
					$m->description = $faker->sentence();
					$m->{$min}      = '0';
					$m->{$max}      = '9999999.99';

					// Use the attribute name that triggers the mutator
					$m->roles_applicable = $rolesUpToHere;

					$m->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$m->setAttribute(DC::COL_TABLE_UPDATER, null);
					$m->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
