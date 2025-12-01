<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str as Str;
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
			];

			$order = 0;
			foreach ($names as $nm) {
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
			}
		}, 3);
	}
}
