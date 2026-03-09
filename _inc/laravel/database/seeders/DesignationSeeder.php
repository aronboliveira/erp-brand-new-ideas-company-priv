<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\{Arr, Str};
use App\Config\Constants\{
	DatabaseConstants as DC,
	CompaniesConstants as CPC,
	UsersConstants as UC
};
use App\Models\Designation as Dsg;
use App\Models\Department as Dpt;

final class DesignationSeeder extends Seeder
{
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$clock = microtime(true);
			$creatorId = DC::DEFAULT_UUID;
			$deptIds = Dpt::query()->pluck('id')->all();
			if (!$deptIds) {
				Log::notice('No departments found. Skipping designation seeding.');
				return;
			}
			// private const HARD_CAP = 128;
			$HARD_CAP = 4;
			// private const SECONDS_LIMIT = 300;
			$SECONDS_LIMIT = 32;
			$designationsPool = [
				"Analista de Sistemas",
				"Engenheiro de Software",
				"Gerente de Projetos",
				"Desenvolvedor Full Stack",
			];
			$pool = array_values(array_unique($designationsPool));
			for ($i = 0; $i < min($HARD_CAP, count($pool)); $i++) {
				if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
				try {
					do $designationId = Str::uuid()->toString();
					while (Dsg::where('id', $designationId)->exists());
					$deptId = $faker->randomElement($deptIds);
					$name = $pool[$i];
					$budget = $faker->boolean(60)
						? $faker->randomFloat(2, 1_000, 250_000)
						: 0.00;
					$validFrom = $faker->dateTimeBetween('-2 years', 'now');
					$validTo   = $faker->dateTimeBetween('+1 years', '+9 years');
					$d = new Dsg();
					$d->id                      = $designationId;
					$d->{UC::COL_DSG_NM}        = $name;
					$d->{CPC::COL_DEP_ID}       = $deptId;
					$d->{CPC::COL_EBDG}         = $budget;
					$d->{CPC::COL_VFROM}        = $validFrom;
					$d->{CPC::COL_VTO}          = $validTo;
					$d->description             = $faker->boolean(55) ? $faker->sentence(12) : null;
					$d->notes                   = $faker->boolean(35) ? $faker->sentence(10) : null;
					$d->{DC::COL_TABLE_CREATOR}     = $creatorId;
					$d->setAttribute(DC::COL_TABLE_UPDATER, null);
					$d->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
			$elapsed = round(microtime(true) - $clock, 2);
			(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("[DesignationSeeder] Done. Created {$i} in {$elapsed}s");
		}, 3);
	}
}
