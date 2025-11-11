<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Models\Deal as Dl;
use App\Models\User as Usr;
use App\Models\Pipeline as Pln;
use App\Models\Stage as Stg;
use App\Models\Label as Lbl;
use App\Models\Source as Src;
use App\Models\ProductService as Psv;

final class DealSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$pipelineIds = Pln::query()->pluck('id')->all();
			if (!$pipelineIds) {
				Log::notice('No pipelines found. Skipping deal seeding.');
				return;
			}

			$labels   = Lbl::query()->pluck('id')->all();
			$sources  = Src::query()->pluck('id')->all();
			$products = Psv::query()->pluck('id')->all();

			$quantity = 40;
			$statusKeys = array_keys(Dl::$statuses);

			$hierarchy = ['client', 'user', 'accountant', 'admin', 'superAdmin'];
			$resources = ['tasks', 'files', 'sources', 'products', 'contacts', 'invoices', 'members', 'custom-fields'];
			$actions   = ['view', 'create', 'update', 'delete'];

			$abilities = [];
			foreach ($resources as $res) {
				foreach ($actions as $act) {
					$abilities[] = "{$act}:{$res}";
				}
			}

			$rolePermissions = [];
			foreach ($hierarchy as $i => $role) {
				$perms = [];
				for ($j = 0; $j <= $i; $j++) {
					foreach ($abilities as $ab) {
						$perms[] = "{$hierarchy[$j]}:{$ab}";
					}
				}
				$rolePermissions[$role] = $perms;
			}

			for ($i = 0; $i < $quantity; $i++) {
				$pipelineId = $faker->randomElement($pipelineIds);
				$stageIdsForPipeline = Stg::query()->where('pipeline_id', $pipelineId)->pluck('id')->all();
				if (!$stageIdsForPipeline) {
					Log::notice("Pipeline {$pipelineId} has no stages. Skipping deal creation for this pipeline.");
					continue;
				}

				do $dealId = Str::uuid()->toString();
				while (Dl::where('id', $dealId)->exists());

				$name  = $faker->sentence(3);
				$phone = $faker->boolean(60) ? $faker->phoneNumber() : null;
				$price = $faker->randomFloat(2, 300, 25000);

				$pick = function (array $pool, int $min, int $max): ?string {
					if (!$pool) return null;
					$n = random_int($min, $max);
					$slice = collect($pool)->shuffle()->take($n)->all();
					return $slice ? implode(',', $slice) : null;
				};

				$role = $faker->randomElement($hierarchy); // armazenado em 'permissions'

				$d = new Dl();
				$d->id          = $dealId;
				$d->name        = $name;
				$d->phone       = $phone;
				$d->price       = $price;
				$d->pipeline_id = $pipelineId;
				$d->stage_id    = $faker->randomElement($stageIdsForPipeline);
				$d->group_id    = random_int(1, 5); // ? recheck typing if needed
				$d->sources     = $pick($sources, 0, 3);
				$d->products    = $pick($products, 0, 3);
				$d->notes       = $faker->boolean(40) ? $faker->paragraph() : null;
				$d->labels      = $pick($labels, 0, 3);
				$d->permissions = $role; // string curta; herança resolvida em runtime via $rolePermissions
				$d->status      = $faker->randomElement($statusKeys);
				$d->order       = $i;
				$d->is_active   = 1;
				$d->{DC::TABLE_CREATOR} = $systemUserId;
				$d->setAttribute(DC::TABLE_UPDATER, null);
				$d->save();

				$userIds = Usr::query()->inRandomOrder()->limit(random_int(0, 3))->pluck('id')->all();
				foreach ($userIds as $uid) {
					$exists = DB::table('user_deals')
						->where('deal_id', $d->id)
						->where('user_id', $uid)
						->exists();

					if ($exists) {
						continue;
					}

					do $pivotId = Str::uuid()->toString();
					while (DB::table('user_deals')->where('id', $pivotId)->exists());

					DB::table('user_deals')->insert([
						'id'       => $pivotId,
						'deal_id'  => $d->id,
						'user_id'  => $uid,
					]);
				}
			}
		}, 3);
	}
}
