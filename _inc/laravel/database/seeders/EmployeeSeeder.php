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
			$systemUserId = $this->ensureSystemUser();

			$branchIds = Br::query()->pluck('id')->all();
			$deptIds   = Dep::query()->pluck('id')->all();
			$dsgIds    = Dsg::query()->pluck('id')->all();
			$taxIds    = Tx::query()->pluck('id')->all();
			$payIds    = Pay::query()->pluck('id')->all();

			if (!$branchIds || !$deptIds || !$dsgIds) {
				Log::notice('Branches/Departments/Designations ausentes. Abortando EmployeeSeeder.');
				return;
			}

			$alreadyAssigned = Emp::query()
				->whereNotNull(UC::COL_USER_ID)
				->pluck(UC::COL_USER_ID)
				->all();

			$availableUserIds = Usr::query()
				->when($alreadyAssigned, fn($q) => $q->whereNotIn('id', $alreadyAssigned))
				->pluck('id')
				->all();

			$genders = array_map(fn($e) => $e->value, Gender::cases());
			$quantity = 30;

			for ($i = 0; $i < $quantity; $i++) {
				do $id = Str::uuid()->toString();
				while (Emp::where('id', $id)->exists());

				do $publicId = Str::uuid()->toString();
				while (Emp::where(UC::COL_EMP_ID, $publicId)->exists());

				$branchId = $faker->randomElement($branchIds);
				$deptId   = $faker->randomElement($deptIds);
				$dsgId    = $faker->randomElement($dsgIds);

				$maybeUserId = null;
				if ($availableUserIds && $faker->boolean(60)) {
					$pick = array_rand($availableUserIds);
					$maybeUserId = $availableUserIds[$pick] ?? null;
					if ($maybeUserId) unset($availableUserIds[$pick]);
				}

				$maybeTaxId = $taxIds ? $faker->optional(0.6)->randomElement($taxIds) : null;
				$maybePayId = $payIds ? $faker->optional(0.7)->randomElement($payIds) : null;

				$name  = $faker->name();
				$email = $faker->boolean(70) ? strtolower($faker->unique()->safeEmail()) : null;

				if ($email !== null) {
					while (Emp::where('email', $email)->exists()) {
						$email = strtolower($faker->unique()->safeEmail());
					}
				}

				$phone = $faker->boolean(70) ? preg_replace('/\D+/', '', $faker->unique()->phoneNumber()) : null;
				if ($phone !== null) {
					while (Emp::where('phone', $phone)->exists()) {
						$phone = preg_replace('/\D+/', '', $faker->unique()->phoneNumber());
					}
				}

				$accNumber = $faker->boolean(50) ? preg_replace('/\s+/u', '', $faker->bothify('BR-####-#####')) : null;
				if ($accNumber !== null) {
					while (Emp::where(UC::COL_ACC_NM, $accNumber)->exists()) {
						$accNumber = preg_replace('/\s+/u', '', $faker->bothify('BR-####-#####'));
					}
				}

				$documents = [
					['tipo' => 'RG', 'numero' => (string) $faker->numerify('#########')],
					['tipo' => 'CPF', 'numero' => (string) $faker->numerify('###########')],
				];

				$emp = new Emp();
				$emp->id                     = $id;
				$emp->{UC::COL_EMP_ID}       = $publicId;
				$emp->{UC::COL_USER_ID}      = $maybeUserId;
				$emp->name                   = $name;
				$emp->email                  = $email;
				$emp->phone                  = $phone;
				$emp->gender                 = $faker->randomElement($genders);
				$emp->notes                  = $faker->optional()->sentence();
				$emp->password               = \Illuminate\Support\Str::password(); // cast hashed
				$emp->address                = $faker->optional()->address();
				$emp->dob                    = $faker->optional()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d');
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
				$emp->{DC::TABLE_CREATOR}    = DC::DEFAULT_UUID;

				$emp->save();
			}
		}, 3);
	}
}
