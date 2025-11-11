<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Models\ClientDeal as Cld;
use App\Models\Deal as Dl;
use App\Models\User as Usr;

final class ClientDealSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$dealIds   = Dl::query()->pluck('id')->all();
			$clientIds = Usr::query()->pluck('id')->all();

			if (!$dealIds || !$clientIds) {
				Log::notice('No deals or users found. Skipping client_deals seeding.');
				return;
			}

			foreach ($dealIds as $dealId) {
				$count = random_int(0, 4);
				if ($count === 0) continue;

				$picked = collect($clientIds)->shuffle()->take($count)->all();

				foreach ($picked as $clientId) {
					if (Cld::where('deal_id', $dealId)->where('client_id', $clientId)->exists()) {
						continue; // * evita violar UNIQUE(deal_id, client_id)
					}

					do $pivotId = Str::uuid()->toString();
					while (Cld::where('id', $pivotId)->exists());

					$cd = new Cld();
					$cd->id         = $pivotId;
					$cd->deal_id    = $dealId;
					$cd->client_id  = $clientId;
					$cd->{DC::TABLE_CREATOR} = $systemUserId;
					$cd->setAttribute(DC::TABLE_UPDATER, null);
					$cd->save();
				}
			}
		}, 3);
	}
}
