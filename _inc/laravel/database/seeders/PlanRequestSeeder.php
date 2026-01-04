<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{Frequency, UserType};
use App\Models\PlanRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class PlanRequestSeeder extends Seeder
{
	private ConsoleOutput $out;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		try {
			$planIds = $this->fetchIdsRaw(DC::TABLE_PLANS);
			if (!$planIds) {
				$this->out->writeln('PlanRequestSeeder: no plans found; skipping');
				return;
			}

			$eligibleTypes = [
				UserType::Client->value,
				UserType::Vendor->value,
				UserType::Customer->value,
			];

			$userIds = $this->fetchUserIdsByTypesRaw($eligibleTypes);
			if (!$userIds) {
				$this->out->writeln('PlanRequestSeeder: no eligible users found; skipping');
				return;
			}

			$targetUsers = (int) ceil(count($userIds) * 0.25);
			if ($targetUsers < 1) $targetUsers = 1;

			shuffle($userIds);
			$selectedUsers = array_values(array_slice($userIds, 0, min($targetUsers, count($userIds))));

			$this->out->writeln(
				'PlanRequestSeeder: eligible_users=' . count($userIds)
					. ' selected_users=' . count($selectedUsers)
					. ' target_users=' . $targetUsers
					. ' cap=8000'
			);

			$freqs = Frequency::cases();
			$created = 0;
			$globalAttempts = 0;

			foreach ($selectedUsers as $userId) {
				$n = random_int(1, 3);

				$chosenPlanIds = $this->pickDistinctMany($planIds, $n);
				if (!$chosenPlanIds) continue;

				foreach ($chosenPlanIds as $planId) {
					$globalAttempts++;
					if ($globalAttempts > 200000) {
						$this->out->writeln('PlanRequestSeeder: global attempt limit hit; breaking early');
						break 2;
					}

					if ($created >= 8000)
						break 2;

					$duration = $freqs[array_rand($freqs)];

					$exists = false;
					try {
						$exists = DB::table(DC::TABLE_PLAN_REQUESTS)
							->where(UC::COL_USER_ID, $userId)
							->where(UC::COL_PLAN_ID, $planId)
							->exists();
					} catch (\Throwable $e) {
						Log::warning(self::class . ' exists check failed (user_id + plan_id)', [
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'error' => $e->getMessage(),
							'user_id' => $userId,
							'plan_id' => $planId,
						]);
					}

					if ($exists) {
						$this->out->writeln("PLAN_REQ skip: user={$userId} plan={$planId} (already exists)");
						continue;
					}

					$this->out->writeln("PLAN_REQ create: user={$userId} plan={$planId} duration={$duration->value}");

					$m = new PlanRequest();
					$m->setAttribute(UC::COL_USER_ID, $userId);
					$m->setAttribute(UC::COL_PLAN_ID, $planId);
					$m->setAttribute('duration', $duration->value);
					$m->save();

					$created++;

					// Optional: reflect the requested plan on the user record (migration adds UC::COL_RQ_PLN)
					try {
						DB::table(DC::TABLE_USERS)
							->where('id', $userId)
							->update([UC::COL_RQ_PLN => $planId]);
					} catch (\Throwable $e) {
						Log::debug(self::class . ' failed updating user requested_plan', [
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'error' => $e->getMessage(),
							'user_id' => $userId,
							'plan_id' => $planId,
						]);
					}
				}
			}

			$this->out->writeln("PlanRequestSeeder: created={$created}");
		} catch (\Throwable $e) {
			Log::error(self::class . ' seeding failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}
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

	private function fetchUserIdsByTypesRaw(array $types): array
	{
		try {
			$types = array_values(array_filter($types, fn($v) => is_string($v) && trim($v) !== ''));
			if (!$types) return [];

			$placeholders = implode(',', array_fill(0, count($types), '?'));
			$sql = 'select id from ' . DC::TABLE_USERS . ' where type in (' . $placeholders . ')';

			$rows = DB::select($sql, $types);

			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && isset($r->id) ? (string) $r->id : null;
				if ($id !== null && trim($id) !== '')
					$out[] = $id;
			}

			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed fetching user ids by types', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'types' => $types,
			]);
			return [];
		}
	}

	private function pickDistinctMany(array $pool, int $n): array
	{
		if ($n <= 0 || !$pool)
			return [];

		$n = min($n, count($pool));

		$keys = array_rand($pool, $n);
		if (!is_array($keys))
			$keys = [$keys];

		$out = [];
		foreach ($keys as $k)
			$out[] = (string) $pool[$k];

		return array_values(array_unique($out));
	}
}
