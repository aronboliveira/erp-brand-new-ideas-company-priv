<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
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
				'suporte',
				'vendas',
				'administrativo',
				'qualidade',
				'projetos',
				'estrategia',
				'compliance',
				'inovacao',
				'desenvolvimento',
				'planejamento',
				'relacionamento',
				'servico_ao_cliente',
			];
			$makeDepartments = function () use ($deptPool) {
				$n = random_int(2, min(5, count($deptPool)));
				return implode(',', collect($deptPool)->shuffle()->take($n)->all());
			};

			$rows = random_int(2, 8) * Usr::where('type', 'company')->count();
			for ($i = 0; $i < $rows; $i++) {
				try {
					do $branchId = Str::uuid()->toString();
					while (Br::where('id', $branchId)->exists());

					do $branchName = $faker->company();
					while (Br::where(CPC::COL_BRC_NM, $branchName)->exists());

					do $branchAddress = $faker->address();
					while (Br::where('address', $branchAddress)->exists());

					do $branchPhone = $faker->cellphoneNumber();
					while (Br::where('phone', $branchPhone)->exists());

					$admId = $pickUser();
					$mngId = $pickUser();
					(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Seeding branch: {$branchName} ({$branchId})");
					$b = new Br();
					$b->id                  = $branchId;
					$b->{CPC::COL_BRC_NM}   = $branchName;
					$b->address             = $branchAddress;
					$b->phone               = $branchPhone;
					$b->{CPC::COL_FND}      = $faker->name();
					$b->{CPC::COL_ADM}      = $admId;
					$b->{CPC::COL_MNG}      = $mngId;
					$b->description         = $faker->boolean(60) ? $faker->sentence(10) : null;
					$b->departments         = $makeDepartments();
					$b->budget              = $faker->randomFloat(2, 50_000, 2_000_000);
					$b->expenses            = $faker->randomFloat(2, 10_000, 1_500_000);
					$b->profit              = max(0, $b->budget - $b->expenses);
					$b->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$b->setAttribute(DC::COL_TABLE_UPDATER, null);
					$b->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
