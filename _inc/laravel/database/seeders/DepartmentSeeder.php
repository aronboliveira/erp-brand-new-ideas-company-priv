<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\CompaniesConstants as CPC;

use App\Models\Department as Dpt;
use App\Models\Branch as Br;
use App\Models\User as Usr;

final class DepartmentSeeder extends Seeder
{
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$branchIds = Br::query()->pluck('id')->all();
			if (!$branchIds) return; // ! Não há filiais; nada a fazer

			$userIds = Usr::query()->pluck('id')->all();
			$pickUser = function () use ($userIds) {
				return $userIds ? $userIds[array_rand($userIds)] : null; // manager é nullable
			};

			$names = [
				'Financeiro',
				'Recursos Humanos',
				'Tecnologia da Informação',
				'Vendas',
				'Operações',
				'Marketing',
				'Jurídico',
				'Logística',
				'Compras',
				'Suporte'
			];

			foreach ($branchIds as $brId) {
				$count = random_int(2, 6);
				$pool  = collect($names)->shuffle()->take($count)->values()->all();

				foreach ($pool as $nm) {
					do $deptId = Str::uuid()->toString();
					while (Dpt::where('id', $deptId)->exists());

					$d = new Dpt();
					$d->id                   = $deptId;
					$d->{CPC::COL_BRC_ID}    = $brId;
					$d->{CPC::COL_DEP_NM}    = $nm;
					$d->description          = $faker->boolean(50) ? $faker->sentence(10) : null;
					$d->phone                = $faker->boolean(60) ? $faker->cellphoneNumber() : null;
					$d->email                = $faker->boolean(60) ? $faker->unique()->safeEmail() : null;
					$d->{CPC::COL_MNG}       = $pickUser();
					$d->budget               = $faker->randomFloat(2, 10_000, 800_000);
					$d->expenses             = $faker->randomFloat(2, 2_000, 600_000);
					$d->profit               = max(0, $d->budget - $d->expenses);
					$d->{DC::COL_TABLE_CREATOR}  = DC::DEFAULT_UUID;
					$d->setAttribute(DC::COL_TABLE_UPDATER, null);
					$d->save();
				}
			}
		}, 3);
	}
}
