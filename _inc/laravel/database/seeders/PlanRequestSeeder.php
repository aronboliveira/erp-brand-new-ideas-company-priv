<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{Frequency, UserType};
use App\Models\PlanRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Symfony\Component\Console\Output\ConsoleOutput;

class PlanRequestSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const MIN_ROWS = 32;
	// private const HARD_CAP = 4096;
	private const HARD_CAP = 2;

	// Keep this high enough to handle "already exists" collisions when the table is partially seeded.
	private const MAX_GLOBAL_ATTEMPTS = 400000;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		try {
			DB::disableQueryLog();

			if (!Schema::hasTable(DC::TABLE_PLANS) || !Schema::hasTable(DC::TABLE_USERS)) {
				$this->out->writeln('PlanRequestSeeder: missing required tables; skipping');
				return;
			}
			if (!Schema::hasTable(DC::TABLE_PLAN_REQUESTS)) {
				$this->out->writeln('PlanRequestSeeder: plan_requests table not found; skipping');
				return;
			}

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

			$maxPossible = count($planIds) * count($userIds);
			if ($maxPossible <= 0) {
				$this->out->writeln('PlanRequestSeeder: maxPossible=0; skipping');
				return;
			}

			$cap = min(self::HARD_CAP, $maxPossible);
			$target = max(1, min($cap, self::MIN_ROWS));

			// Try to create more than MIN if there is enough room, without exceeding cap.
			// Old behavior was ~25% of eligible users x 1..3 plans; keep a similar scale but bounded by cap.
			$approx = (int) ceil(count($userIds) * 0.25) * 2; // heuristic
			$target = min($cap, max($target, $approx));

			// $hasRequestedPlanCol = Schema::hasColumn(DC::TABLE_USERS, UC::COL_RQ_PLN);
			$hasRequestedPlanCol = Schema::hasColumn(DC::TABLE_USERS, UC::COL_RP);

			$this->out->writeln(
				'PlanRequestSeeder: plans=' . count($planIds)
					. ' eligible_users=' . count($userIds)
					. ' max_possible=' . $maxPossible
					. ' target=' . $target
					. ' cap=' . $cap
					. ' min=' . self::MIN_ROWS
					. ' has_users_requested_plan_col=' . ($hasRequestedPlanCol ? 'yes' : 'no')
			);

			$freqs = Frequency::cases();
			$created = 0;
			$globalAttempts = 0;

			// Phase 1: deterministic-ish distribution across users (reduces collisions).
			shuffle($userIds);
			foreach ($userIds as $userId) {
				if ($created >= $target) break;
				if ($created >= $cap) break;

				$n = random_int(1, min(3, count($planIds)));
				$chosenPlanIds = $this->pickDistinctMany($planIds, $n);

				foreach ($chosenPlanIds as $planId) {
					if ($created >= $target) break 2;
					if ($created >= $cap) break 2;

					$globalAttempts++;
					if ($globalAttempts > self::MAX_GLOBAL_ATTEMPTS) {
						$this->out->writeln('PlanRequestSeeder: global attempt limit hit; breaking early');
						break 2;
					}

					if ($this->createIfNotExists($userId, $planId, $freqs, $hasRequestedPlanCol)) {
						$created++;
						// $this->out->writeln("PLAN_REQ create: user={$userId} plan={$planId} created={$created}/{$target}");
					}
				}
			}

			// Phase 2: pad to ensure MIN_ROWS (and up to target) using random pairs.
			$padTarget = max(min(self::MIN_ROWS, $cap), $target);

			while ($created < $padTarget && $created < $cap) {
				$globalAttempts++;
				if ($globalAttempts > self::MAX_GLOBAL_ATTEMPTS) {
					$this->out->writeln('PlanRequestSeeder: global attempt limit hit during padding; breaking');
					break;
				}

				$userId = $userIds[random_int(0, count($userIds) - 1)] ?? null;
				$planId = $planIds[random_int(0, count($planIds) - 1)] ?? null;

				if (!is_string($userId) || trim($userId) === '') continue;
				if (!is_string($planId) || trim($planId) === '') continue;

				if ($this->createIfNotExists($userId, $planId, $freqs, $hasRequestedPlanCol)) {
					$created++;
					// $this->out->writeln("PLAN_REQ create: user={$userId} plan={$planId} created={$created}/{$padTarget} (pad)");
				}
			}

			$this->out->writeln("PlanRequestSeeder: created={$created} target={$target} cap={$cap} attempts={$globalAttempts}");
		} catch (\Throwable $e) {
			Log::error(self::class . ' seeding failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
			$this->out->writeln('PlanRequestSeeder: error=' . $e->getMessage());
		}
	}

	private function createIfNotExists(string $userId, string $planId, array $freqs, bool $hasRequestedPlanCol): bool
	{
		$userId = trim($userId);
		$planId = trim($planId);
		if ($userId === '' || $planId === '') return false;

		try {
			$exists = DB::table(DC::TABLE_PLAN_REQUESTS)
				->where(UC::COL_USER_ID, $userId)
				->where(UC::COL_PLAN_ID, $planId)
				->exists();

			if ($exists) return false;
		} catch (\Throwable $e) {
			Log::warning(self::class . ' exists check failed (user_id + plan_id)', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'user_id' => $userId,
				'plan_id' => $planId,
			]);
			// If exists check fails (transient), still try create; DB constraints will protect if present.
		}

		$duration = $freqs ? $freqs[array_rand($freqs)] : null;
		$durationValue = $duration ? $duration->value : Frequency::Monthly->value;

		try {
			$m = new PlanRequest();
			$m->setAttribute(UC::COL_USER_ID, $userId);
			$m->setAttribute(UC::COL_PLAN_ID, $planId);
			$m->setAttribute('duration', $durationValue);
			$m->save();
		} catch (\Throwable $e) {
			Log::warning(self::class . ' create failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'user_id' => $userId,
				'plan_id' => $planId,
				'duration' => $durationValue,
			]);
			return false;
		}

		// Optional: reflect the requested plan on the user record (only if the column exists).
		if ($hasRequestedPlanCol) {
			try {
				DB::table(DC::TABLE_USERS)
					->where('id', $userId)
					// ->update([UC::COL_RQ_PLN => $planId]);
					->update([UC::COL_RP => $planId]);
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

		return true;
	}

	private function fetchIdsRaw(string $table): array
	{
		try {
			$rows = DB::select('select id from ' . $table);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && isset($r->id) ? (string) $r->id : null;
				if ($id !== null && trim($id) !== '') $out[] = trim($id);
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
				if ($id !== null && trim($id) !== '') $out[] = trim($id);
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
		if ($n <= 0 || !$pool) return [];

		$pool = array_values($pool);
		$n = min($n, count($pool));

		$keys = array_rand($pool, $n);
		if (!is_array($keys)) $keys = [$keys];

		$out = [];
		foreach ($keys as $k) {
			$v = $pool[$k] ?? null;
			if (!is_scalar($v)) continue;
			$s = trim((string) $v);
			if ($s === '') continue;
			$out[] = $s;
		}

		return array_values(array_unique($out));
	}
}
