<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\CompaniesConstants as CPC;

use App\Models\Department as Dpt;
use App\Models\Branch as Br;
use App\Models\User as Usr;

final class DepartmentSeeder extends Seeder
{
	public const MIN_DEPTS_PER_BRANCH = 4;
	public $max_depts_per_branch = 32;
	public const MIN_DSG_PER_DEPT = 2;
	public const MAX_DSG_PER_DEPT = 16;
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$branchIds = Br::query()->pluck('id')->all();
			if (!$branchIds) {
				Log::warning(get_class($this) . ' aborted: no branches found.');
				return;
			}

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
				'Suporte',
				'Pesquisa e Desenvolvimento',
				'Atendimento ao Cliente',
				'Produção',
				'Qualidade',
				'Comunicação Corporativa',
				'Relações Públicas',
				'Desenvolvimento de Negócios',
				'Planejamento Estratégico',
				'Segurança da Informação',
				'Gestão de Projetos',
				'Suporte Técnico',
				'Administração',
				'Contabilidade',
				'Desenvolvimento de Produto',
				'Engenharia',
				'Design',
				'Eventos',
				'Treinamento e Desenvolvimento',
				'Saúde e Segurança Ocupacional',
				'Sustentabilidade',
				'Inovação',
				'Riscos e Conformidade'
			];
			$this->max_depts_per_branch = count($names);
			foreach ($branchIds as $brId) {
				$count = random_int(self::MIN_DEPTS_PER_BRANCH, $this->max_depts_per_branch);
				$pool  = collect($names)->shuffle()->take($count)->values()->all();

				foreach ($pool as $nm) {
					$chanceOfSkipping = random_int(0, 100);
					if ($chanceOfSkipping < 25)
						continue;
					try {
						do $deptId = Str::uuid()->toString();
						while (Dpt::where('id', $deptId)->exists());
						(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Seeding department: {$nm} ({$deptId})");

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
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
