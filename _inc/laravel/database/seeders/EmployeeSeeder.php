<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Traits\EnsuresSystemUser;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\CompaniesConstants as CPC;

use App\Enums\Gender;

use App\Models\Employee as Emp;
use App\Models\User as Usr;
use App\Models\Branch as Br;
use App\Models\Department as Dep;
use App\Models\Designation as Dsg;
use App\Models\Tax as Tx;
use App\Models\PayslipType as Pay;

final class EmployeeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$clock = microtime(true);
			// private const HARD_CAP = 160;
			$HARD_CAP = 4;
			// private const SECONDS_LIMIT = 300;
			$SECONDS_LIMIT = 32;

			$this->ensureSystemUser();

			$branchIds = Br::query()->pluck('id')->all();
			$deptIds   = Dep::query()->pluck('id')->all();
			$dsgIds    = Dsg::query()->pluck('id')->all();
			$taxIds    = Tx::query()->pluck('id')->all();
			$payIds    = Pay::query()->pluck('id')->all();

			if (!$branchIds || !$deptIds || !$dsgIds) {
				Log::notice('Branches/Departments/Designations ausentes. Abortando EmployeeSeeder.');
				return;
			}

			$availableUserIds = Usr::query()
				->whereNotIn('type', ['company', 'client', 'vendor'])
				->pluck('id')
				->values()
				->all();

			$genders = array_map(fn($e) => $e->value, Gender::cases());
			$created = 0;

			for ($i = 0; $i < $HARD_CAP; $i++) {
				if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
				try {
					do $id = Str::uuid()->toString();
					while (Emp::where('id', $id)->exists());

					do $publicId = Str::uuid()->toString();
					while (Emp::where(UC::COL_EMP_ID, $publicId)->exists());

					$branchId = $faker->randomElement($branchIds);
					$deptId   = $faker->randomElement($deptIds);
					$dsgId    = $faker->randomElement($dsgIds);

					$maybeUserId = null;
					if ($availableUserIds) {
						$key = array_rand($availableUserIds);
						$candidateId = $availableUserIds[$key];
						if (!Emp::where(UC::COL_USER_ID, $candidateId)->exists()) {
							$maybeUserId = $candidateId;
							unset($availableUserIds[$key]);
						}
					}

					$maybeTaxId = $taxIds ? $faker->optional(0.6)->randomElement($taxIds) : null;
					$maybePayId = $payIds ? $faker->optional(0.7)->randomElement($payIds) : null;

					$name  = $faker->name();
					$email = $faker->boolean(70) ? strtolower($faker->unique()->safeEmail()) : null;
					$phone = $faker->boolean(70) ? preg_replace('/\D+/', '', $faker->unique()->phoneNumber()) : null;
					$accNumber = $faker->boolean(50) ? preg_replace('/\s+/u', '', $faker->bothify('BR-####-#####')) : null;

					$documents = [
						['tipo' => 'RG', 'numero' => (string) $faker->numerify('#########')],
						['tipo' => 'CPF', 'numero' => (string) $faker->numerify('###########')],
					];

					$employeeAsUser = $maybeUserId && DB::table(DC::TABLE_USERS)->where('id', $maybeUserId)->exists();
					$isManager = $faker->boolean(10);
					if ($employeeAsUser) {
						DB::table(DC::TABLE_USERS)->where('id', $maybeUserId)->update([UC::COL_EMP_ID => $publicId]);
					}

					$emp = new Emp();
					$emp->id                     = $id;
					$emp->{UC::COL_EMP_ID}       = $publicId;
					$emp->{UC::COL_USER_ID}      = $maybeUserId;
					$emp->name                   = $name;
					$emp->manager                = $isManager;
					$emp->email                  = $email;
					$emp->phone                  = $phone;
					$emp->gender                 = $faker->randomElement($genders);
					$emp->notes                  = $faker->optional()->sentence();
					$emp->password               = Str::password();
					$emp->address                = $faker->optional()->address();
					$dob = $faker->dateTimeBetween('-60 years', '-18 years');
					$emp->dob = $faker->boolean(80) ? $dob->format('Y-m-d') : null;
					$emp->{CPC::COL_BRC_ID}      = $branchId;
					$emp->{CPC::COL_DEP_ID}      = $deptId;
					$emp->{UC::COL_DSG_ID}       = $dsgId;
					$emp->{CPC::COL_DOJ}         = $faker->optional()->date('Y-m-d');
					$emp->documents              = $documents;
					$emp->{UC::COL_ACC_HD}       = $faker->optional()->name();
					$emp->{UC::COL_ACC_NM}       = $accNumber;
					$emp->{UC::COL_BANK_NM}      = $faker->optional()->company();
					$emp->{UC::COL_BANK_IC}      = $faker->optional()->swiftBicNumber();
					$emp->{UC::COL_TAX_ID}       = $maybeTaxId;
					$emp->salary                 = $faker->randomFloat(2, 1500, 14000);
					$emp->{UC::COL_SLR_TP}       = $maybePayId;
					$emp->{UC::COL_IA}           = 1;
					$emp->{DC::COL_TABLE_CREATOR}    = DC::DEFAULT_UUID;
					$emp->save();
					$created++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
			$elapsed = round(microtime(true) - $clock, 2);
			(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("[EmployeeSeeder] Done. Created: {$created} in {$elapsed}s");
		}, 3);
	}
}
