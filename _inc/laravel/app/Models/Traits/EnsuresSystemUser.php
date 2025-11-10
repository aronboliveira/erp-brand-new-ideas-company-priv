<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\User as Usr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str as Str;

trait EnsuresSystemUser
{
	protected function ensureSystemUser(): string
	{
		$id = DC::DEFAULT_UUID;

		if (!Usr::where('id', $id)->exists()) {
			$faker = fake('pt_BR');

			$u = new Usr();
			$u->id = $id;
			$u->name = $faker->name();
			$u->email = $faker->unique()->safeEmail();
			$u->password = Hash::make(Str::password());
			$u->save();
		}

		return $id;
	}
}
