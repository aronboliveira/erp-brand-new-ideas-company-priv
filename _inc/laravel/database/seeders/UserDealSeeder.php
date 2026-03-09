<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Models\UserDeal as Ud;
use App\Models\User as Usr;
use App\Models\Deal as Dl;

final class UserDealSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$dealIds = Dl::query()->pluck('id')->all();
			$userIds = Usr::query()->pluck('id')->all();

			if (!$dealIds || !$userIds) {
				Log::notice('No deals or users found. Skipping user_deals seeding.');
				return;
			}

			$HARD_CAP = 24;
			$created = 0;
			foreach ($dealIds as $dealId) {
				if ($created >= $HARD_CAP) break;
				$count = random_int(0, 8);
				if ($count === 0) continue;

				$picked = collect($userIds)->shuffle()->take($count)->all();

				foreach ($picked as $uid) {
					try {
						if (Ud::where('deal_id', $dealId)->where('user_id', $uid)->exists()) continue;
						// (new \Symfony\Component\Console\Output\ConsoleOutput
						// )->writeln("Criando Associação de Usuário para Acordo de Negócios: {$dealId} - Usuário: {$uid}");
						do $pivotId = Str::uuid()->toString();
						while (Ud::where('id', $pivotId)->exists());

						$ud = new Ud();
						$ud->id        = $pivotId;
						$ud->deal_id   = $dealId;
						$ud->user_id   = $uid;
						$ud->{DC::COL_TABLE_CREATOR} = $systemUserId;
						$ud->setAttribute(DC::COL_TABLE_UPDATER, null);
						$ud->save();
						$created++;
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
