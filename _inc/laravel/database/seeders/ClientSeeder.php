<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str as Str;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Models\Client as Cli;
use App\Traits\EnsuresSystemUser;

class ClientSeeder extends Seeder
{
	use EnsuresSystemUser;
	public function run(): void
	{
		$this->ensureSystemUser();
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$creatorId = DC::DEFAULT_UUID;

			$quantity = 20;

			for ($i = 0; $i < $quantity; $i++) {
				do $clientId = Str::uuid()->toString();
				while (Cli::where('id', $clientId)->exists());

				$c = new Cli();
				$c->id = $clientId;
				$c->{UC::COL_NM}      = $faker->name();
				$c->{UC::COL_EM}      = $faker->unique()->safeEmail();
				$c->{UC::COL_EM_V_AT} = null;
				$c->{UC::COL_PW}      = Hash::make(Str::password());
				$c->{UC::COL_LG}      = 'pt_BR';
				$c->{UC::COL_IA}      = 1;
				$c->{UC::COL_USER_ID} = $faker->boolean(35) ? $creatorId : null;
				$c->{UC::COL_TEL}     = $faker->phoneNumber();
				$c->{UC::COL_ADR}     = $faker->address();
				$c->{UC::COL_IU}      = false;
				$c->{UC::COL_AV}      = 'default.png';
				$c->{UC::COL_MSG_CL}  = '#2180f3';
				$c->{UC::COL_DEL_STT} = 1;
				$c->{DC::TABLE_CREATOR} = $creatorId;

				$c->save();
			}
		}, 3);
	}
}
