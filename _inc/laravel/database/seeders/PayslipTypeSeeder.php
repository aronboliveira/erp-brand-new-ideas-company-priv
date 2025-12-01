<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\UserType;
use App\Models\PayslipType as Pst;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
			$rla = BC::COL_RL_APL;

			$base = [
				['name' => 'Mensalista',      'desc' => $faker->sentence(),           $min => null,   $max => null,   $rla => null],
				['name' => 'Horista',         'desc' => $faker->sentence(),           $min => null,   $max => null,   $rla => null],
				['name' => 'Comissão',        'desc' => $faker->sentence(),           $min => '0',    $max => '20000', $rla => [UserType::Vendor->value, UserType::Admin->value]],
				['name' => 'Bônus',           'desc' => $faker->sentence(),           $min => '0',    $max => '10000', $rla => null],
				['name' => 'PLR',             'desc' => $faker->sentence(),           $min => '0',    $max => '50000', $rla => [UserType::Company->value, UserType::Accountant->value, UserType::Admin->value]],
				['name' => 'Adiantamento',    'desc' => $faker->sentence(),           $min => '0',    $max => '5000', $rla => [UserType::Company->value, UserType::Admin->value]],
				['name' => 'Periculosidade',  'desc' => 'Adicional por risco.',      $min => null,   $max => null,   $rla => null],
				['name' => 'Insalubridade',   'desc' => 'Adicional por insalubr.',   $min => null,   $max => null,   $rla => null],
				['name' => 'Gratificação',    'desc' => $faker->sentence(),           $min => '0',    $max => '8000', $rla => null],
				['name' => 'Auxílio',         'desc' => 'Auxílios (VT, VR, VA etc.)', $min => '0',    $max => '3000', $rla => null],
			];

			foreach ($base as $row) {
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
				$m->{$rla}      = $row[$rla]; // ? mutator normaliza array|string|null
				$m->{DC::COL_TABLE_CREATOR} = $systemUserId;
				$m->setAttribute(DC::COL_TABLE_UPDATER, null);
				$m->save();
			}

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
				$m->{$rla}      = $rolesUpToHere;
				$m->{DC::COL_TABLE_CREATOR} = $systemUserId;
				$m->setAttribute(DC::COL_TABLE_UPDATER, null);
				$m->save();
			}
		}, 3);
	}
}
