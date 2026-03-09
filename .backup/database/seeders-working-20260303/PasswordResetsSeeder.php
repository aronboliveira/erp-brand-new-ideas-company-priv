<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User as Usr;

final class PasswordResetsSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		if (!defined(DC::class . '::TABLE_PW_RST')) return;

		$table = DC::TABLE_PW_RST;

		if (!Schema::hasTable($table)) return;
		foreach (['token', 'email', PJC::COL_SBM_AT, DC::COL_EXP_DT, 'attempts'] as $col) {
			if (!Schema::hasColumn($table, $col)) return;
		}

		DB::transaction(function () use ($table) {
			$systemUserId = $this->ensureSystemUser();

			$emails = Usr::query()
				->inRandomOrder()
				->limit(256)
				->pluck('email')
				->filter(fn($v) => is_scalar($v) && trim((string) $v) !== '')
				->map(fn($v) => mb_strtolower(trim((string) $v)))
				->unique()
				->values()
				->all();

			$faker = fake('pt_BR');

			$sources = ['web', 'mobile', 'automatic', 'api', 'admin'];

			// Per-email second offsets to avoid same-second collisions on unique composites.
			$secOffsetByEmail = [];

			$rows = [];

			foreach ($emails as $email) {
				$tries = random_int(1, 3);

				// Base time: random in the last 60 days, aligned to seconds.
				$base = now()
					->subDays(random_int(0, 60))
					->subHours(random_int(0, 23))
					->subMinutes(random_int(0, 59))
					->setSecond(random_int(0, 59));

				$secOffsetByEmail[$email] = $secOffsetByEmail[$email] ?? random_int(0, 20);

				foreach (range(1, $tries) as $i) {
					$plain = Str::random(64);
					$token = Hash::make($plain);

					// submitted_at spaced by >= 2 hours each try (never violates 2/hour)
					$sbmAt = (clone $base)
						->addHours(($i - 1) * 2)
						->addSeconds($secOffsetByEmail[$email]++);

					$expAt = (clone $sbmAt)->addMinutes(15);

					$rows[] = [
						'token' => $token,
						'email' => $email,
						PJC::COL_SBM_AT => $sbmAt,
						DC::COL_EXP_DT  => $expAt,
						'source'   => $sources[random_int(0, count($sources) - 1)],
						'attempts' => random_int(0, 3),
						'ip'       => random_int(0, 99) < 12 ? null : $faker->ipv4(),
						'created_at' => $sbmAt,
						'updated_at' => $sbmAt,
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => $systemUserId,
					];
				}
			}

			// Insert in chunks to keep memory predictable.
			foreach (array_chunk($rows, 500) as $chunk) {
				DB::table($table)->insert($chunk);
			}
		}, 3);
	}
}
