<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Source as Src;

final class SourceSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();
			$names = [
				'Portal Institucional',
				'CRM Corporativo',
				'Integração ERP',
				'Formulário Web',
				'Importação CSV',
				'API Externa',
				'Help Desk',
				'Campanha Marketing',
			];

			foreach ($names as $nm) {
				do $sourceId = Str::uuid()->toString();
				while (Src::where('id', $sourceId)->exists());

				$s = new Src();
				$s->id = $sourceId;
				$s->name = $nm;
				$s->{DC::TABLE_CREATOR} = $systemUserId;
				$s->{DC::TABLE_UPDATER} = null;
				$s->save();
			}
		}, 3);
	}
}
