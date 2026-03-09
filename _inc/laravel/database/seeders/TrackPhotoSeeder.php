<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\Visibility;
use App\Models\TrackPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class TrackPhotoSeeder extends Seeder
{
	private ConsoleOutput $out;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		try {
			$faker = fake('en_US');

			$userIds = $this->fetchIdsRaw(DC::TABLE_USERS);
			if (!$userIds) {
				$this->out->writeln('TrackPhotoSeeder: no users found; skipping');
				return;
			}

			// Ensure 0.05 of users have >= 1 TrackPhoto, each user gets 1..32.
			$targetUsers = (int) ceil(count($userIds) * 0.05);
			if ($targetUsers < 1)
				$targetUsers = 1;

			// Choose the first N for determinism; can be randomized by shuffling.
			shuffle($userIds);
			$selectedUsers = array_values(array_slice($userIds, 0, min($targetUsers, count($userIds))));

			$rawTotal = 0;
			$perUser = [];
			foreach ($selectedUsers as $uid) {
				$n = random_int(1, 32);
				$perUser[$uid] = $n;
				$rawTotal += $n;
			}

			// Cap at 8000 total rows
			// $cap = 8000;
			$cap = 2;
			if ($rawTotal > $cap) {
				$scale = $cap / $rawTotal;
				$newTotal = 0;

				foreach ($perUser as $uid => $n) {
					$nn = (int) floor($n * $scale);
					if ($nn < 1) $nn = 1;
					if ($nn > 32) $nn = 32;
					$perUser[$uid] = $nn;
					$newTotal += $nn;
				}

				// Hard trim if still above cap (rare but possible due to min=1)
				if ($newTotal > $cap) {
					$uids = array_keys($perUser);
					$idx = 0;
					$attempts = 0;

					while ($newTotal > $cap && $attempts < 20000) {
						$attempts++;
						$uid = $uids[$idx % count($uids)];
						$idx++;
						$cur = (int) ($perUser[$uid] ?? 1);
						if ($cur <= 1) continue;
						$perUser[$uid] = $cur - 1;
						$newTotal--;
					}

					if ($newTotal > $cap)
						$this->out->writeln("TrackPhotoSeeder: cap trim attempt limit hit; total={$newTotal} cap={$cap}");
				}

				$rawTotal = array_sum($perUser);
			}

			$this->out->writeln('TrackPhotoSeeder: users=' . count($userIds) . ' selected=' . count($selectedUsers) . ' target_users=' . $targetUsers . ' total=' . $rawTotal . ' cap=8000');

			$visibilityPool = [
				Visibility::Private,
				Visibility::Internal,
				Visibility::Unlisted,
				Visibility::Restricted,
				Visibility::Protected,
				Visibility::Public,
			];

			$created = 0;
			$attemptsGlobal = 0;

			foreach ($perUser as $userId => $n) {
				// Ensure min iterations per selected user: at least 1 already guaranteed
				for ($i = 0; $i < $n; $i++) {
					$attemptsGlobal++;
					if ($attemptsGlobal > 200000) {
						$this->out->writeln('TrackPhotoSeeder: global attempt limit hit; breaking early');
						break 2;
					}

					$trackId = $this->uniqueTrackIdForUser($userId);
					$imgPath = $faker->boolean(60) ? $this->fakeImagePath($faker, $trackId) : null;
					$url = $imgPath === null && $faker->boolean(60) ? $this->fakeSafeUrl($faker, $trackId) : null;

					$time = $faker->boolean(80)
						? $faker->dateTimeBetween('-180 days', 'now')
						: null;

					$visibility = $visibilityPool[array_rand($visibilityPool)];

					$status = $faker->boolean(65)
						? $faker->randomElement(['new', 'queued', 'processed', 'approved', 'rejected', 'archived'])
						: null;

					// $this->out->writeln("TRK_PHT create: user={$userId} track={$trackId} vis={$visibility->value} img=" . ($imgPath ? '1' : '0') . " url=" . ($url ? '1' : '0'));

					$m = new TrackPhoto();
					$m->setAttribute(PJC::COL_TRK_ID, $trackId);
					$m->setAttribute(UC::COL_USER_ID, $userId);
					$m->setAttribute(PJC::COL_IMG_PATH, $imgPath);
					$m->setAttribute('url', $url);
					$m->setAttribute('time', $time);
					$m->setAttribute('visibility', $visibility->value);
					$m->setAttribute('status', $status);

					$m->save();
					$created++;

					if ($created >= 8000)
						break 2;
				}
			}

			$this->out->writeln("TrackPhotoSeeder: created={$created}");
		} catch (\Throwable $e) {
			Log::error(self::class . ' seeding failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}
	}

	private function uniqueTrackIdForUser(string $userId): string
	{
		$attempts = 0;
		do {
			$attempts++;
			// keep it string per migration (PJC::COL_TRK_ID is string)
			$candidate = 'TRK-' . Str::upper(Str::random(6)) . '-' . substr((string) Str::uuid(), 0, 8);

			try {
				$exists = DB::table(DC::TABLE_TRK_PHT)
					->where(PJC::COL_TRK_ID, $candidate)
					->where(UC::COL_USER_ID, $userId)
					->exists();

				if (!$exists)
					return $candidate;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' track_id existence check failed', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'user_id' => $userId,
					'candidate' => $candidate,
				]);
				return $candidate;
			}
		} while ($attempts < 30);

		$this->out->writeln('TrackPhotoSeeder: track_id attempt limit hit; returning fallback');
		return 'TRK-' . substr((string) Str::uuid(), 0, 12);
	}

	private function fakeImagePath($faker, string $trackId): string
	{
		$fn = Str::slug($trackId) . '-' . $faker->bothify('####') . '.jpg';
		return 'tracks/photos/' . substr($fn, 0, 64);
	}

	private function fakeSafeUrl($faker, string $trackId): string
	{
		// Keep https:// and plausible CDN-ish domain; your FiltersSecureAttachments rules don't apply here.
		$host = $faker->randomElement(['cdn.example.com', 'static.example.com', 'assets.example.com']);
		$path = '/tracks/' . rawurlencode($trackId) . '/p/' . $faker->bothify('######') . '.jpg';
		return 'https://' . $host . $path;
	}

	private function fetchIdsRaw(string $table): array
	{
		try {
			$rows = DB::select('select id from ' . $table);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && isset($r->id) ? (string) $r->id : null;
				if ($id !== null && trim($id) !== '')
					$out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed fetching ids', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
			]);
			return [];
		}
	}
}
