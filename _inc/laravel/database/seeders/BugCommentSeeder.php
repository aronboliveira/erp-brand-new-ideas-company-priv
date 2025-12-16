<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Models\Bug as Bg;
use App\Models\BugComment as Bc;
use App\Models\User as Usr;
use App\Enums\UserType as UT;

final class BugCommentSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$bugIds  = Bg::query()->pluck('id')->all();
			$userIds = Usr::query()->pluck('id')->all();

			if (!$bugIds) {
				Log::notice('No bugs found. Skipping bug_comments seeding.');
				return;
			}

			$typePool = [
				UT::SuperAdmin,
				UT::Admin,
				UT::Company,
				UT::Client,
				UT::Customer,
				UT::Vendor,
				UT::Accountant,
			];

			foreach ($bugIds as $bugId) {
				try {
					$count = random_int(1, 6);

					for ($i = 0; $i < $count; $i++) {
						try {
							do $commentId = Str::uuid()->toString();
							while (Bc::where('id', $commentId)->exists());
							(new \Symfony\Component\Console\Output\ConsoleOutput
							)->writeln("Criando Comentário para Bug: {$commentId}");
							$authorId = ($userIds && random_int(1, 100) <= 70)
								? $userIds[array_rand($userIds)]
								: $systemUserId;

							$ts = $faker->dateTimeBetween('-30 days', 'now');

							$bc = new Bc();
							$bc->id                  = $commentId;
							$bc->bug_id              = $bugId;
							$bc->comment             = $faker->realText($faker->numberBetween(120, 600));
							$bc->user_type           = $typePool[array_rand($typePool)]; // crítico: enum garante valor canônico
							$bc->{DC::COL_TABLE_CREATOR} = $authorId;
							$bc->setAttribute(DC::COL_TABLE_UPDATER, null);
							$bc->setAttribute('created_at', $ts);
							$bc->setAttribute('updated_at', $ts);
							$bc->save();
						} catch (\Exception $e) {
							Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
							continue;
						}
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
