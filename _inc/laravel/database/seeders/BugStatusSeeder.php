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
use App\Models\BugStatus as Bst;

final class BugStatusSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$rows = [
				['ord' => 0, 'title' => 'Aberto'],
				['ord' => 1, 'title' => 'Em andamento'],
				['ord' => 2, 'title' => 'Em revisão'],
				['ord' => 3, 'title' => 'Bloqueado'],
				['ord' => 4, 'title' => 'Resolvido'],
				['ord' => 5, 'title' => 'Fechado'],
				['ord' => 6, 'title' => 'Não será corrigido'],
				['ord' => 7, 'title' => 'Duplicado'],
			];

			foreach ($rows as $r) {
				if (Bst::where(AC::COL_TT, $r['title'])->exists()) {
					continue;
				}

				do $statusId = Str::uuid()->toString();
				while (Bst::where('id', $statusId)->exists());

				$bs = new Bst();
				$bs->id                   = $statusId;
				$bs->{AC::COL_OD}         = $r['ord'];
				$bs->{AC::COL_TT}         = $r['title'];
				$bs->{DC::COL_TABLE_CREATOR}  = $systemUserId;
				$bs->setAttribute(DC::COL_TABLE_UPDATER, null);
				$bs->save();
			}
		}, 3);
	}
}
