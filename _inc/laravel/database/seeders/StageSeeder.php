<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Config\Constants\ActivitiesConstants as AC;

use App\Models\Stage as Stg;
// crítica: é necessário que existam pipelines; se o model diferir, ajuste o import abaixo.
use App\Models\Pipeline as Pln;

final class StageSeeder extends Seeder
{
	use EnsuresSystemUser;

	// private const MIN_ROWS = 800;
	private const HARD_CAP = 128; /* original: pipelines × 11 names; unbounded — raised from 4 to cover ~12 pipelines */

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();
			$pipelineIds = Pln::query()->pluck('id')->all();
			// crítica: se não houver pipelines, interromper evita violação de FK (COL_PPL_ID é obrigatório).
			if (!$pipelineIds) {
				Log::debug('StageSeeder: no pipelines found; skipping stage seeding.');
				return;
			}

			$names = ['Backlog', 'A Fazer', 'Em Progresso', 'Revisão', 'Concluído', 'Arquivado', 'Cancelado', 'Pausado', 'Em Espera', 'Rejeitado', 'Aprovado'];

			$created = 0;
			foreach ($pipelineIds as $pipelineId) {
				if ($created >= self::HARD_CAP) break; /* HARD_CAP guard */
				$order = 0;

				foreach ($names as $nm) {
					if ($created >= self::HARD_CAP) break; /* HARD_CAP guard */
					try {
						// (new \Symfony\Component\Console\Output\ConsoleOutput
						// )->writeln("Criando Estágio de Projeto: {$nm}");
						do $stageId = Str::uuid()->toString();
						while (Stg::where('id', $stageId)->exists());

						$s = new Stg();
						$s->id = $stageId;
						$s->{PJC::COL_PPL_ID} = $pipelineId;
						$s->{PJC::COL_STG_NM} = $nm;
						$s->{AC::COL_OD}      = $order++;
						$s->{DC::COL_TABLE_CREATOR} = $systemUserId;
						$s->setAttribute(DC::COL_TABLE_UPDATER, null);

						$s->save();
						$created++;
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
