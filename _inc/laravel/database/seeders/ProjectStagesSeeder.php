<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Config\Constants\ActivitiesConstants as AC;
use App\Models\ProjectStages as Pst;
use App\Traits\EnsuresSystemUser;

class ProjectStagesSeeder extends Seeder
{
	use EnsuresSystemUser;
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$creatorId = DC::DEFAULT_UUID;
			$names = [
				'Planejamento',
				'Em andamento',
				'Revisão',
				'Concluído',
				'Arquivado',
				'Cancelado',
				'Pausado',
				'Em espera',
				'Rejeitado',
				'Aprovado',
				'Em revisão',
				'Em teste',
				'Implementação',
				'Desenvolvimento',
				'Design',
				'Pesquisa',
				'Análise',
				'Documentação',
				'Entrega',
				'Suporte',
				'Manutenção',
			];

			$order = 0;
			foreach ($names as $nm) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Estágio de Projeto: {$nm}");
					do $projStageId = Str::uuid()->toString();
					while (Pst::where('id', $projStageId)->exists());

					$s = new Pst();
					$s->id = $projStageId;
					$s->{PJC::COL_NM} = $nm;
					$s->{PJC::COL_CL} = $faker->hexColor();
					$s->{AC::COL_OD}  = $order++;
					$s->{DC::COL_TABLE_CREATOR} = $creatorId;
					$s->{DC::COL_TABLE_UPDATER} = null;

					$s->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
