<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;

use App\Models\Label as Lbl;
use App\Models\Pipeline as Pln;

final class LabelSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$pipelineIds = Pln::query()->pluck('id')->all();
			if (!$pipelineIds) {
				Log::notice('No pipelines found. Skipping label seeding.');
				return;
			}

			$names  = ['Bug', 'Melhoria', 'Pesquisa', 'Urgente', 'Bloqueado', 'Frontend', 'Backend'];
			$colors = \App\Models\Label::$colors;

			foreach ($pipelineIds as $pipelineId) {
				foreach ($names as $nm) {
					try {
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Rótulo: {$nm}");
						do $labelId = Str::uuid()->toString();
						while (Lbl::where('id', $labelId)->exists());

						$l = new Lbl();
						$l->id = $labelId;
						$l->{PJC::COL_LB_NM}  = $nm;
						$l->{PJC::COL_CL}     = $faker->randomElement($colors);
						$l->{PJC::COL_PPL_ID} = $pipelineId;
						$l->{DC::COL_TABLE_CREATOR} = $systemUserId;
						$l->setAttribute(DC::COL_TABLE_UPDATER, null);

						$l->save();
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
