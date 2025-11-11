<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\ProjectsConstants as PJC;

use App\Models\Pipeline as Pln;

final class PipelineSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$names = [
				'Vendas',
				'Onboarding',
				'Projetos',
				'Suporte',
				'Renovação',
			];

			$order = 0;

			foreach ($names as $nm) {
				do $pipelineId = Str::uuid()->toString();
				while (Pln::where('id', $pipelineId)->exists());

				$p = new Pln();
				$p->id = $pipelineId;
				$p->{PJC::COL_PPL_NM} = $nm;
				$p->{AC::COL_OD}      = $order++;
				$p->{DC::TABLE_CREATOR} = $systemUserId;
				$p->setAttribute(DC::TABLE_UPDATER, null);

				$p->save();
			}
		}, 3);
	}
}
