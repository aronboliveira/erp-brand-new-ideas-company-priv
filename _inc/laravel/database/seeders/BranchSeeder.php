<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\CompaniesConstants as CPC;

use App\Models\Branch as Br;
use App\Models\User as Usr;

final class BranchSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$userIds = Usr::query()->pluck('id')->all();
			$pickUser = function () use ($userIds, $systemUserId) {
				return $userIds ? $userIds[array_rand($userIds)] : $systemUserId;
			};

			$deptPool = [
				'financeiro',
				'rh',
				'ti',
				'vendas',
				'operacoes',
				'marketing',
				'juridico',
				'logistica',
				'compras',
				'suporte'
			];
			$makeDepartments = function () use ($deptPool) {
				$n = random_int(2, min(5, count($deptPool)));
				return implode(',', collect($deptPool)->shuffle()->take($n)->all());
			};

			$rows = 8;
			for ($i = 0; $i < $rows; $i++) {
				do $branchId = Str::uuid()->toString();
				while (Br::where('id', $branchId)->exists());

				$admId = $pickUser();
				$mngId = $pickUser();

				$b = new Br();
				$b->id                  = $branchId;
				$b->{CPC::COL_BRC_NM}   = $faker->company();
				$b->address             = $faker->address();
				$b->phone               = $faker->cellphoneNumber();
				$b->{CPC::COL_FND}      = $faker->name();
				$b->{CPC::COL_ADM}      = $admId;
				$b->{CPC::COL_MNG}      = $mngId;
				$b->description         = $faker->boolean(60) ? $faker->sentence(10) : null;
				$b->departments         = $makeDepartments();
				$b->budget              = $faker->randomFloat(2, 50_000, 2_000_000);
				$b->expenses            = $faker->randomFloat(2, 10_000, 1_500_000);
				$b->profit              = max(0, $b->budget - $b->expenses);
				$b->{DC::TABLE_CREATOR} = $systemUserId;
				$b->setAttribute(DC::TABLE_UPDATER, null);
				$b->save();
			}
		}, 3);
	}
}
