<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use App\Config\Constants\DatabaseConstants as DC;

final class MessageSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();
			if (count($userIds) < 1) {
				Log::notice('No users found. Skipping message seeding.');
				return;
			}

			$quantity = 128;
			$types = ['text', 'file', 'system'];

			for ($i = 0; $i < $quantity; $i++) {
				try {
					do $messageId = Str::uuid()->toString();
					while (DB::table('messages')->where('id', $messageId)->exists());

					$type   = $faker->randomElement($types);
					$toId   = $faker->randomElement($userIds);
					$fromId = $type === 'system' ? null : $faker->randomElement($userIds);
					if ($fromId && $fromId === $toId) {
						$alts = array_values(array_diff($userIds, [$toId]));
						$fromId = $alts ? $faker->randomElement($alts) : $fromId;
					}

					$body = $type === 'file' && $faker->boolean(60)
						? null
						: $faker->realText($faker->numberBetween(60, 1200));

					$attachment = $type === 'file'
						? 'uploads/' . Str::uuid()->toString() . '.' . $faker->randomElement(['pdf', 'png', 'jpg', 'docx'])
						: null;

					$createdAt = $faker->dateTimeBetween('-30 days', 'now');

					DB::table('messages')->insert([
						'id'              => $messageId,
						'type'            => $type,
						'from_id'         => $fromId,
						'to_id'           => $toId,
						'body'            => $body,
						'attachment'      => $attachment,
						'seen'            => $faker->boolean(55),
						'created_at'      => $createdAt,
						'updated_at'      => $createdAt,
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => null,
					]);
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
