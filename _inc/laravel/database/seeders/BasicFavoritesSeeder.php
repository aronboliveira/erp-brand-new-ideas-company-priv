<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;
use App\Config\Constants\DatabaseConstants as DC;

use App\Models\User as Usr;
use App\Models\Deal as Dl;
use App\Models\Project as Prj;
use App\Models\Pipeline as Pln;
use App\Models\Stage as Stg;
use App\Models\Label as Lbl;
use App\Models\Source as Src;
use App\Models\ProductService as Psv;
use App\Models\DealTask as Dtk;
use App\Models\DealFile as Df;

final class BasicFavoritesSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$userIds = Usr::query()->pluck('id')->all();
			if (!$userIds) {
				Log::notice('No users found. Skipping basic_favorites seeding.');
				return;
			}

			$pools = [
				Dl::query()->pluck('id')->all(),
				Prj::query()->pluck('id')->all(),
				Pln::query()->pluck('id')->all(),
				Stg::query()->pluck('id')->all(),
				Lbl::query()->pluck('id')->all(),
				Src::query()->pluck('id')->all(),
				Psv::query()->pluck('id')->all(),
				Dtk::query()->pluck('id')->all(),
				Df::query()->pluck('id')->all(),
			];

			$candidates = array_values(array_unique(array_merge(...array_map(
				fn($a) => array_filter($a),
				$pools
			))));

			if (!$candidates) {
				Log::notice('No favoritable IDs found. Skipping basic_favorites seeding.');
				return;
			}

			foreach ($userIds as $uid) {
				$count = random_int(0, 7);
				if ($count === 0) continue;

				$picked = collect($candidates)->shuffle()->take($count)->all();

				foreach ($picked as $favId) {
					$exists = DB::table('basic_favorites')
						->where('user_id', $uid)
						->where('favorite_id', $favId)
						->exists();
					if ($exists) continue;

					do $favRowId = Str::uuid()->toString();
					while (DB::table('basic_favorites')->where('id', $favRowId)->exists());

					$ts = $faker->dateTimeBetween('-60 days', 'now');

					DB::table('basic_favorites')->insert([
						'id'              => $favRowId,
						'user_id'         => $uid,
						'favorite_id'     => $favId,
						'created_at'      => $ts,
						'updated_at'      => $ts,
						DC::TABLE_CREATOR => $systemUserId,
						DC::TABLE_UPDATER => null,
					]);
				}
			}
		}, 3);
	}
}
