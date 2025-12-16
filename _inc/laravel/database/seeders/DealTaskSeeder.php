<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ActivitiesConstants as AC,
	ProjectsConstants as PJC
};

use App\Models\Deal as Dl;
use App\Models\DealTask as Dtk;

final class DealTaskSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$dealIds = Dl::query()->pluck('id')->all();
			if (!$dealIds) {
				Log::notice('No deals found. Skipping deal_tasks seeding.');
				return;
			}

			// * manter coerência com a migration (TINYINT)
			$priorityPool = [1, 2, 3];   // 1: Low, 2: Medium, 3: High
			$statusPool   = [0, 1];      // 0: On Going, 1: Completed

			foreach ($dealIds as $dealId) {
				$count = random_int(4, 64);

				for ($i = 0; $i < $count; $i++) {
					try {
						$nm = $faker->sentence(4);
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Tarefa para Acordo de Negócios: {$nm}");
						do $taskId = Str::uuid()->toString();
						while (Dtk::where('id', $taskId)->exists());

						$dt   = $faker->dateTimeBetween('-30 days', '+30 days');
						$date = $dt->format('Y-m-d');
						$time = $dt->format('H:i:s');

						$t = new Dtk();
						$t->id                    = $taskId;
						$t->{AC::COL_DL}          = $dealId;
						$t->{PJC::COL_NM}         = $nm;
						$t->{AC::COL_TSK_DATE}    = $date;
						$t->{AC::COL_TSK_TIME}    = $time;
						$t->{PJC::COL_PRT}        = $faker->randomElement($priorityPool);
						$t->{AC::COL_TSK_STT}     = $faker->randomElement($statusPool);
						$t->{DC::COL_TABLE_CREATOR}   = $systemUserId;
						$t->setAttribute(DC::COL_TABLE_UPDATER, null);
						$t->save();
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
