<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

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
			$creatorId = DC::DEFAULT_UUID;

			$deptIds = Dpt::query()->pluck('id')->all();
			if (!$deptIds) {
				Log::notice('No departments found. Skipping designation seeding.');
				return;
			}

			$count = 30;

			for ($i = 0; $i < $count; $i++) {
				do $designationId = Str::uuid()->toString();
				while (Dsg::where('id', $designationId)->exists());

				$deptId = $faker->randomElement($deptIds);

				$name = $faker->jobTitle(); // * se houver índice único futuro, considerar compor com o departamento

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
				$d->{DC::TABLE_CREATOR}     = $creatorId;
				$d->setAttribute(DC::TABLE_UPDATER, null);
				$d->save();
			}
		}, 3);
	}
}
