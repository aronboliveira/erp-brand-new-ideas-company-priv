<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ActivitiesConstants as AC
};

use App\Models\Deal as Dl;
use App\Models\DealDiscussion as Dds;

final class DealDiscussionSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$dealIds = Dl::query()->pluck('id')->all();
			if (!$dealIds) {
				Log::notice('No deals found. Skipping deal_discussions seeding.');
				return;
			}

			foreach ($dealIds as $dealId) {
				$count = random_int(1, 6);

				for ($i = 0; $i < $count; $i++) {
					do $discussionId = Str::uuid()->toString();
					while (Dds::where('id', $discussionId)->exists());

					$body = $faker->realText($faker->numberBetween(120, 600));
					$ts   = $faker->dateTimeBetween('-30 days', 'now');

					$d = new Dds();
					$d->id                     = $discussionId;
					$d->{AC::COL_DL}           = $dealId;
					$d->comment                = $body;
					$d->{DC::TABLE_CREATOR}    = $systemUserId;
					$d->setAttribute(DC::TABLE_UPDATER, null);
					$d->setAttribute('created_at', $ts);
					$d->setAttribute('updated_at', $ts);
					$d->save();
				}
			}
		}, 3);
	}
}
