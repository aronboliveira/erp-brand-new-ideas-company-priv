<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\ProjectsConstants as PJC;

use App\Models\Bug as Bg;
use App\Models\Project as Prj;
use App\Models\User as Usr;

final class BugSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$projectIds = Prj::query()->pluck('id')->all();
			if (!$projectIds) {
				Log::notice('No projects found. Skipping bug seeding.');
				return;
			}

			$userIds = Usr::query()->pluck('id')->all();

			$quantity = 35;
			$priorities = array_keys(\App\Models\Bug::$priority);
			$statuses = ['new', 'open', 'in_progress', 'resolved', 'closed'];

			for ($i = 0; $i < $quantity; $i++) {
				do $bugId = Str::uuid()->toString();
				while (Bg::where('id', $bugId)->exists());

				$startAt = $faker->dateTimeBetween('-40 days', '+10 days');
				$dueAt   = $faker->boolean(70) ? $faker->dateTimeBetween($startAt, '+40 days') : null;

				$assigned = $faker->boolean(60)
					? ($userIds ? $faker->randomElement($userIds) : null)
					: null;

				$b = new Bg();
				$b->id = $bugId;
				$b->{PJC::COL_PJ_ID} = $faker->randomElement($projectIds);
				$b->{AC::COL_TT}     = $faker->sentence(6);
				$b->{PJC::COL_PRT}   = $faker->randomElement($priorities);
				$b->{PJC::COL_S_DT}  = $startAt->format('Y-m-d');
				$b->{PJC::COL_D_DATE} = $dueAt?->format('Y-m-d');
				$b->{AC::COL_DESC}   = $faker->paragraph();
				$b->{AC::COL_TSK_STT} = $faker->randomElement($statuses); // alinhar com sua estratégia (FK vs string)
				$b->{PJC::COL_ASGN}  = $assigned;
				$b->setAttribute('order', $i);
				$b->{DC::TABLE_CREATOR} = $systemUserId;
				$b->setAttribute(DC::TABLE_UPDATER, null);

				$b->save();
			}
		}, 3);
	}
}
