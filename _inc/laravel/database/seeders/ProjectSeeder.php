<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\ActivitiesConstants as AC;

use App\Models\Project as Prj;
use App\Models\Client as Cli;
use App\Models\ProjectStage as Pst;
use App\Models\User as Usr;
use App\Traits\EnsuresSystemUser;

final class ProjectSeeder extends Seeder
{
	use EnsuresSystemUser;
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = DC::DEFAULT_UUID;
			// HARD_CAP: limit iterations for dev/test speed
			$HARD_CAP = 2;
			if (!Cli::exists()) {
				for ($i = 0; $i < $HARD_CAP; $i++) { // original: 8
					$cName = $faker->boolean(30) ? $faker->company() : $faker->name();
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Cliente: {$cName}");
					do $clientId = Str::uuid()->toString();
					while (Cli::where('id', $clientId)->exists());

					$c = new Cli();
					$c->id = $clientId;
					$c->{UC::COL_NM}      = $cName;
					$c->{UC::COL_EM}      = $faker->unique()->safeEmail();
					$c->{UC::COL_LG}      = 'pt_BR';
					$c->{UC::COL_TEL}     = $faker->phoneNumber();
					$c->{UC::COL_ADR}     = $faker->address();
					$c->{UC::COL_IA}      = 1;
					$c->{UC::COL_IU}      = false;
					$c->{UC::COL_AV}      = 'default.png';
					$c->{UC::COL_MSG_CL}  = '#2180f3';
					$c->{UC::COL_DEL_STT} = 1;
					$c->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$c->save();
				}
			}
			if (!Pst::exists()) {
				$names = ['Planejamento', 'Em andamento', 'Revisão', 'Concluído', 'Aguardando cliente', 'Em espera', 'Cancelado', 'Arquivado', 'Iniciado', 'Em teste', 'Produção', 'Homologação', 'Análise', 'Design', 'Implementação', 'Lançamento', 'Suporte', 'Manutenção', 'Otimização', 'Encerramento'];
				$ord = 0;
				foreach (array_slice($names, 0, $HARD_CAP) as $nm) { // original: foreach ($names as $nm)
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Estágio: {$nm}");
					do $stageId = Str::uuid()->toString();
					while (Pst::where('id', $stageId)->exists());

					$s = new Pst();
					$s->id = $stageId;
					$s->setAttribute(PJC::COL_NM, $nm);
					$s->setAttribute(PJC::COL_CL, $faker->hexColor());
					$s->setAttribute(AC::COL_OD, $ord++);
					$s->setAttribute(DC::COL_TABLE_CREATOR, $systemUserId);
					$s->save();
				}
			}
			$quantity = $HARD_CAP; // original: 255
			$statusKeys = array_keys(Prj::$project_status);
			$clientIds = Cli::query()->pluck('id')->all();
			$stageIds  = Pst::query()->pluck('id')->all();
			for ($i = 0; $i < $quantity; $i++) {
				try {
					$pjNm = $faker->sentence(3);
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Projeto: {$pjNm}");
					do $projectId = Str::uuid()->toString();
					while (Prj::where('id', $projectId)->exists());

					$start = $faker->dateTimeBetween('-90 days', '+10 days');
					$end   = $faker->boolean(70) ? $faker->dateTimeBetween($start, '+120 days') : null;

					$p = new Prj();
					$p->id = $projectId;
					$p->{PJC::COL_NM}          = $pjNm;
					$p->{PJC::COL_S_DT}        = $start->format('Y-m-d');
					$p->{PJC::COL_E_DT}        = $end?->format('Y-m-d');
					$p->{PJC::COL_CLIENT_ID}   = $faker->randomElement($clientIds);
					$p->{PJC::COL_IMG}         = null;
					$p->{PJC::COL_BUDGET}      = $faker->numberBetween(5_000, 150_000);
					$p->{PJC::COL_STAGE_ID}    = $faker->randomElement($stageIds);
					$p->{PJC::COL_DESCRIPTION} = $faker->paragraph();
					$p->{PJC::COL_STATUS}      = $faker->randomElement($statusKeys);
					$p->{PJC::COL_E_HRS}       = (string) $faker->numberBetween(10, 480);
					$p->{PJC::COL_PASSWORD}    = null; // crítico: evitar armazenar texto puro
					$p->{PJC::COL_COPYLINK}    = null;
					$p->{PJC::COL_TAGS}        = $faker->words(3, true);
					$p->{DC::COL_TABLE_CREATOR}    = $systemUserId;
					$p->setAttribute(DC::COL_TABLE_UPDATER, null);

					$p->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
