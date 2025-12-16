<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User as Usr;
use App\Config\Constants\DatabaseConstants as DC;

final class PasswordResetsSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$emails = Usr::query()
				->inRandomOrder()
				->limit(25)
				->pluck('email')
				->filter()
				->unique()
				->all();

			foreach ($emails as $email) {
				try {
					$plain = Str::random(64);
					$hash  = Hash::make($plain);

					// * evita colisão na PK 'token'
					while (DB::table('password_resets')->where('token', $hash)->exists()) {
						$plain = Str::random(64);
						$hash  = Hash::make($plain);
					}

					DB::table('password_resets')->insert([
						'token'        => $hash,
						'email'        => $email,
						'created_at'   => now(),
						DC::COL_TABLE_CREATOR => $systemUserId,
					]);
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
