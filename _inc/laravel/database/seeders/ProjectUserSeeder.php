<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{ParticipationStatus, ProjectRole};
use App\Models\ProjectUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProjectUserSeeder extends Seeder
{
	private ConsoleOutput $out;

	/** @var array<string,bool> */
	private array $auditCols = [];

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$table = DC::TABLE_PRJ_USR;

		if (!Schema::hasTable($table)) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: table '{$table}' does not exist.");
			return;
		}

		if (!Schema::hasTable(DC::TABLE_PROJECTS)) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: projects table does not exist.");
			return;
		}

		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: users table does not exist.");
			return;
		}

		$projects = $this->fetchIds(DC::TABLE_PROJECTS, 200000);
		if (!$projects) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: no projects found.");
			return;
		}

		$userTypeCol = $this->resolveUsersTypeColumn();
		$userRows = $this->fetchRows(DC::TABLE_USERS, ['id', $userTypeCol], 200000);
		$userIds = array_values(array_filter(array_map(
			static fn($r) => is_string($r->id ?? null) || is_numeric($r->id ?? null) ? (string) $r->id : '',
			$userRows
		), static fn(string $v) => $v !== ''));

		if (!$userIds) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: no users found.");
			return;
		}

		$usersCount = $this->safeCountUsers();
		$hardCap = 2; // was (int) floor(max(0, $usersCount) * 0.25)

		$existing = $this->safeCountExisting($table);
		$remainingCap = max(0, $hardCap - $existing);

		if ($remainingCap <= 0) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: hard cap reached (cap={$hardCap}). Existing={$existing}.");
			return;
		}

		$this->auditCols = $this->resolveAuditColumns($table);

		$inviterEligibleIds = $this->buildTypeEligibleUserIds($userRows, $userTypeCol, ['super admin', 'admin', 'company']);
		$actorEligibleIds   = $this->buildTypeEligibleUserIds($userRows, $userTypeCol, ['company', 'vendor', 'super admin', 'admin', 'hr']);

		$plan = $this->planCountsPerProject($projects, $remainingCap);
		$plannedTotal = array_sum($plan);

		if ($plannedTotal <= 0) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: planning resulted in 0 rows (remainingCap={$remainingCap}).");
			return;
		}

		$targetTotal = min($plannedTotal, $remainingCap);
		if ($targetTotal >= 64) {
			$targetTotal = $targetTotal - ($targetTotal % 64);
			if ($targetTotal <= 0) $targetTotal = min($plannedTotal, $remainingCap);
		} else {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Warning: remainingCap={$remainingCap} < 64; cannot enforce 64xN strictly.");
		}

		if ($targetTotal <= 0) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped: targetTotal=0 after normalization.");
			return;
		}

		$plan = $this->trimPlanToTarget($plan, $targetTotal);
		$targetTotal = array_sum($plan);

		$requiredRoles = $this->buildRequiredRoles($targetTotal);
		$requiredRoleIdx = 0;

		$this->out->writeln("<info>[ProjectUserSeeder]</info> Start. Projects=" . count($projects) . " Users=" . count($userIds) . " cap={$hardCap} existing={$existing} target={$targetTotal}");

		$created = 0;
		$nowBase = CarbonImmutable::now();

		foreach ($plan as $projectId => $count) {
			if ($created >= $targetTotal) break;

			$count = (int) $count;
			if ($count <= 0) continue;

			$existingUserSet = $this->existingUsersForProject($table, $projectId);
			$availableUserIds = $this->pickDistinctUsersForProject($userIds, $existingUserSet, $count);

			if (!$availableUserIds) {
				$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Skipped project={$projectId}: no available users to assign (already linked or none).");
				continue;
			}

			$rows = [];
			foreach ($availableUserIds as $uid) {
				if ($created + count($rows) >= $targetTotal) break;

				$role = $this->pickRoleWeighted();
				if ($requiredRoleIdx < count($requiredRoles)) {
					$role = $requiredRoles[$requiredRoleIdx];
					$requiredRoleIdx++;
				}

				$inviterId = $this->pickId($inviterEligibleIds, 0.45);
				$invitedAt = $this->chance(0.90)
					? $nowBase->subDays(random_int(0, 120))->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					: null;

				$isLeaderCandidate = in_array($role, [ProjectRole::Owner, ProjectRole::Admin, ProjectRole::Manager], true);

				$inviteStatus = $this->pickParticipationStatus($isLeaderCandidate, $inviterId !== null);

				$acceptedAt = null;
				$joinedAt = null;
				$acceptedBy = null;

				if ($inviteStatus === ParticipationStatus::Accepted) {
					$acceptedAt = ($invitedAt ?? $nowBase)->addDays(random_int(0, 10));
					$joinedAt = $acceptedAt->addMinutes(random_int(0, 240));
					$acceptedBy = $inviterId ?: $this->pickId($actorEligibleIds, 0.25);
				}

				$leftAt = null;
				if ($this->chance(0.05) && $joinedAt !== null) {
					$leftAt = $joinedAt->addDays(random_int(1, 120));
				}

				$isActive = $leftAt ? false : ($inviteStatus === ParticipationStatus::Accepted);

				$isTemporary = $this->chance(0.08);
				$expiresAt = $isTemporary
					? ($nowBase->addDays(random_int(1, 90))->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59)))
					: null;

				$lastEditedAt = $this->chance(0.40)
					? $nowBase->subDays(random_int(0, 60))->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59))
					: null;

				$hourlyPrice = $this->chance(0.35)
					? $this->randomDecimalString(0, 850, 4)
					: null;

				$billableHours = $this->chance(0.40)
					? $this->randomDecimalString(0, 320, 4)
					: null;

				$totalHours = $this->chance(0.55)
					? $this->randomDecimalString(0, 2000, 4)
					: null;

				$perm = $this->resolvePermissions($role);

				$inviteCode = $this->generateInviteCodeUnique(30);
				$inviteUrl = $this->chance(0.85) && $inviteCode
					? "https://example.test/projects/invite/{$inviteCode}"
					: null;

				$notes = $this->chance(0.25)
					? $this->randomNotes($role->value, $inviteStatus->value)
					: null;

				$metadata = $this->chance(0.60)
					? $this->encodeJsonSafe([
						'seed' => true,
						'variant' => 'project_user',
						'project_id' => $projectId,
						'user_id' => $uid,
						'role' => $role->value,
						'invite_status' => $inviteStatus->value,
						'accepted_by' => $acceptedBy,
						'invited_by' => $inviterId,
					])
					: null;

				$preferences = $this->chance(0.70)
					? $this->encodeJsonSafe([
						'allow_email_notifications' => $this->chance(0.92),
						'allow_push_notifications' => $this->chance(0.85),
						'allow_mention_notifications' => $this->chance(0.88),
						'allow_status_update_notifications' => $this->chance(0.90),
						'permissions' => $perm,
						'role' => $role->value,
						'is_temporary' => $isTemporary,
						'expires_at' => $expiresAt?->toDateTimeString(),
					])
					: null;

				$createdBy = $this->pickId($actorEligibleIds ?: $userIds, 0.20);
				$updatedBy = $this->chance(0.45) ? ($createdBy ?: $this->pickId($userIds, 0.15)) : null;

				$row = [
					PJC::COL_PJ_ID => $projectId,
					UC::COL_USER_ID => $uid,

					UC::COL_IA => $isActive,

					PJC::COL_INV_BY => $inviterId,
					PJC::COL_INV_AT => $invitedAt?->toDateTimeString(),
					PJC::COL_INV_STT => $inviteStatus->value,
					PJC::COL_INV_URL => $inviteUrl,
					PJC::COL_INV_CD => $inviteCode,

					PJC::COL_JND_AT => $joinedAt?->toDateTimeString(),
					PJC::COL_ACC_AT => $acceptedAt?->toDateTimeString(),
					PJC::COL_ACC_BY => $acceptedBy,

					PJC::COL_LFT_AT => $leftAt?->toDateTimeString(),
					PJC::COL_RMV_BY => null,

					PJC::COL_IS_TMP => $isTemporary,
					PJC::COL_EXP_AT => $expiresAt?->toDateTimeString(),
					PJC::COL_LST_EDT_AT => $lastEditedAt?->toDateTimeString(),

					'role' => $role->value,

					PJC::COL_CAN_WRT_OWN => $perm['can_write_own_files'],
					PJC::COL_CAN_WRT_OTH => $perm['can_write_others_files'],
					PJC::COL_CAN_RD_OTH  => $perm['can_read_others_files'],

					PJC::COL_IS_PRJ_LD => false,

					PJC::COL_HR_PRC => $hourlyPrice,
					PJC::COL_BLB_HRS => $billableHours,

					PJC::COL_ALW_EML_NTF => $this->chance(0.92),
					PJC::COL_ALW_PSH_NTF => $this->chance(0.85),
					PJC::COL_ALW_MNT_NTF => $this->chance(0.88),
					PJC::COL_ALW_STT_UPD_NTF => $this->chance(0.90),

					PJC::COL_TTL_HRS => $totalHours,

					'notes' => $notes,
					'metadata' => $metadata,
					'preferences' => $preferences,
				];

				if ($this->auditCols[DC::COL_TABLE_CREATOR] ?? false) $row[DC::COL_TABLE_CREATOR] = $createdBy;
				if ($this->auditCols[DC::COL_TABLE_UPDATER] ?? false) $row[DC::COL_TABLE_UPDATER] = $updatedBy;

				$rows[] = $row;
			}

			if (!$rows) continue;

			$leaderIdx = $this->pickLeaderIndex($rows);
			foreach ($rows as $idx => &$r) {
				$isLeader = ($idx === $leaderIdx);
				$r[PJC::COL_IS_PRJ_LD] = $isLeader;

				if ($isLeader) {
					$r[PJC::COL_CAN_WRT_OWN] = true;
					$r[PJC::COL_CAN_WRT_OTH] = true;
					$r[PJC::COL_CAN_RD_OTH]  = true;
				}
			}
			unset($r);

			foreach ($rows as $payload) {
				if ($created >= $targetTotal) break;

				$this->out->writeln(
					// "[PRJUSR " . ($created + 1) . "/{$targetTotal}] pj={$payload[PJC::COL_PJ_ID]} usr={$payload[UC::COL_USER_ID]} role={$payload['role']} leader=" . ((bool) ($payload[PJC::COL_IS_PRJ_LD] ?? false) ? 1 : 0) . " stt={$payload[PJC::COL_INV_STT]}"
					"[PRJUSR " . ($created + 1) . "/{$targetTotal}]"
				);

				try {
					$m = new ProjectUser();
					$m->forceFill($payload);
					$m->save();
					$created++;
				} catch (\Throwable $e) {
					Log::error(self::class . ' failed saving ProjectUser: ' . $e->getMessage(), [
						'project_id' => $payload[PJC::COL_PJ_ID] ?? null,
						'user_id' => $payload[UC::COL_USER_ID] ?? null,
						'role' => $payload['role'] ?? null,
						'invite_status' => $payload[PJC::COL_INV_STT] ?? null,
						'invite_code' => $payload[PJC::COL_INV_CD] ?? null,
					]);
				}
			}
		}

		$this->out->writeln("<info>[ProjectUserSeeder]</info> Done. Inserted={$created} (existing={$existing}, cap={$hardCap}).");
	}

	private function resolveUsersTypeColumn(): string
	{
		try {
			if (Schema::hasColumn(DC::TABLE_USERS, UC::COL_TP)) return UC::COL_TP;
			if (Schema::hasColumn(DC::TABLE_USERS, 'type')) return 'type';
		} catch (\Throwable $e) {
			Log::debug(self::class . ' resolveUsersTypeColumn failed: ' . $e->getMessage());
		}
		return 'type';
	}

	private function safeCountUsers(): int
	{
		try {
			$r = DB::select("select count(*) as c from " . DC::TABLE_USERS);
			return (int) ($r[0]->c ?? 0);
		} catch (\Throwable $e) {
			Log::debug(self::class . ' safeCountUsers failed: ' . $e->getMessage());
			return 0;
		}
	}

	private function safeCountExisting(string $table): int
	{
		try {
			$r = DB::select("select count(*) as c from {$table}");
			return (int) ($r[0]->c ?? 0);
		} catch (\Throwable $e) {
			Log::debug(self::class . " safeCountExisting failed for {$table}: " . $e->getMessage());
			return 0;
		}
	}

	/** @return array<string,bool> */
	private function resolveAuditColumns(string $table): array
	{
		$cols = [
			DC::COL_TABLE_CREATOR,
			DC::COL_TABLE_UPDATER,
		];
		$out = [];
		foreach ($cols as $c) {
			try {
				$out[$c] = Schema::hasColumn($table, $c);
			} catch (\Throwable $e) {
				Log::debug(self::class . " resolveAuditColumns failed for {$table}.{$c}: " . $e->getMessage());
				$out[$c] = false;
			}
		}
		return $out;
	}

	/** @return array<string,int> projectId => count */
	private function planCountsPerProject(array $projectIds, int $remainingCap): array
	{
		$plan = [];
		$total = 0;

		foreach ($projectIds as $pid) {
			if ($total >= $remainingCap) break;

			$n = $this->sampleUserCountPerProject(32);
			$n = max(1, min(32, $n));

			if ($total + $n > $remainingCap) $n = max(1, $remainingCap - $total);
			$plan[$pid] = $n;
			$total += $n;
		}

		if ($total < count($projectIds)) {
			$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Warning: cap prevents covering all projects. Planned={$total} remainingCap={$remainingCap} projectsCovered=" . count($plan) . "/" . count($projectIds));
		}

		return $plan;
	}

	/** @param array<string,int> $plan */
	private function trimPlanToTarget(array $plan, int $targetTotal): array
	{
		$sum = array_sum($plan);
		if ($sum <= $targetTotal) return $plan;

		$keys = array_keys($plan);
		for ($i = count($keys) - 1; $i >= 0 && $sum > $targetTotal; $i--) {
			$pid = $keys[$i];
			$canReduce = $sum - $targetTotal;
			$current = (int) ($plan[$pid] ?? 0);

			if ($current <= 1) {
				unset($plan[$pid]);
				$sum -= $current;
				continue;
			}

			$reduceBy = min($canReduce, $current - 1);
			$plan[$pid] = $current - $reduceBy;
			$sum -= $reduceBy;
		}

		if (array_sum($plan) > $targetTotal) {
			// last resort: drop whole projects from end
			$keys = array_keys($plan);
			for ($i = count($keys) - 1; $i >= 0 && array_sum($plan) > $targetTotal; $i--) {
				$pid = $keys[$i];
				$sum = array_sum($plan);
				$excess = $sum - $targetTotal;
				$cur = (int) ($plan[$pid] ?? 0);
				if ($cur <= $excess) unset($plan[$pid]);
				else $plan[$pid] = max(1, $cur - $excess);
			}
		}

		return $plan;
	}

	private function sampleUserCountPerProject(int $max): int
	{
		// Heavily skew to 1, else inverse-quadratic over 2..$max
		if ($this->chance(0.78)) return 1;

		$max = max(2, $max);
		$weights = [];
		$total = 0.0;
		for ($k = 2; $k <= $max; $k++) {
			$w = 1.0 / ($k * $k);
			$weights[$k] = $w;
			$total += $w;
		}

		$r = (mt_rand() / mt_getrandmax()) * $total;
		$acc = 0.0;
		for ($k = 2; $k <= $max; $k++) {
			$acc += $weights[$k];
			if ($r <= $acc) return $k;
		}

		return 2;
	}

	/**
	 * @param array<int,object> $userRows
	 * @return string[]
	 */
	private function buildTypeEligibleUserIds(array $userRows, string $typeCol, array $allowedCanonical): array
	{
		$allowed = array_fill_keys($allowedCanonical, true);
		$out = [];

		foreach ($userRows as $r) {
			$id = (string) ($r->id ?? '');
			if ($id === '') continue;

			$rawType = (string) ($r->{$typeCol} ?? '');
			$t = $this->normalizeUserType($rawType);
			if ($t !== null && isset($allowed[$t])) $out[] = $id;
		}

		return array_values(array_unique($out));
	}

	private function normalizeUserType(?string $raw): ?string
	{
		$v = strtolower(trim((string) $raw));
		if ($v === '') return null;

		$v = str_replace(['-', '_'], ' ', $v);
		$v = preg_replace('/\s+/', ' ', $v);

		// common short codes / aliases
		if (in_array($v, ['sa', 'root', 'superusuario', 'super usuário'], true)) return 'super admin';
		if (in_array($v, ['adm', 'administrator', 'administrador', 'administração', 'manager', 'gerente'], true)) return 'admin';
		if (in_array($v, ['company', 'empresa', 'companhia', 'corporation', 'business', 'enterprise', 'cpn', 'emp'], true)) return 'company';
		if (in_array($v, ['vendor', 'fornecedor', 'supplier', 'seller', 'vd'], true)) return 'vendor';
		if (in_array($v, ['hr', 'rh', 'human resources', 'humanresources', 'recursos humanos'], true)) return 'hr';

		if ($v === 'super admin' || $v === 'superadmin' || $v === 'super administrator' || $v === 'superadministrator') return 'super admin';
		if ($v === 'admin') return 'admin';

		return $v;
	}

	/** @return array<string,bool> */
	private function existingUsersForProject(string $table, string $projectId): array
	{
		try {
			$rows = DB::select("select " . UC::COL_USER_ID . " as uid from {$table} where " . PJC::COL_PJ_ID . " = ? limit 200000", [$projectId]);
			$set = [];
			foreach ($rows as $r) {
				$uid = (string) ($r->uid ?? '');
				if ($uid !== '') $set[$uid] = true;
			}
			return $set;
		} catch (\Throwable $e) {
			Log::debug(self::class . ' existingUsersForProject failed: ' . $e->getMessage(), ['project_id' => $projectId]);
			return [];
		}
	}

	/**
	 * @param string[] $allUserIds
	 * @param array<string,bool> $existingSet
	 * @return string[]
	 */
	private function pickDistinctUsersForProject(array $allUserIds, array $existingSet, int $count): array
	{
		if ($count <= 0) return [];

		$pool = $allUserIds;
		shuffle($pool);

		$out = [];
		foreach ($pool as $uid) {
			if (isset($existingSet[$uid])) continue;
			$out[] = $uid;
			if (count($out) >= $count) break;
		}

		return $out;
	}

	private function pickLeaderIndex(array $rows): int
	{
		$bestIdx = 0;
		$bestLevel = -1;

		foreach ($rows as $i => $r) {
			$role = ProjectRole::normalize($r['role'] ?? null);
			$level = $role ? $role->getLevel() : 0;

			if ($level > $bestLevel) {
				$bestLevel = $level;
				$bestIdx = (int) $i;
			}
		}

		return $bestIdx;
	}

	private function pickParticipationStatus(bool $isLeaderCandidate, bool $hasInviter): ParticipationStatus
	{
		// If inviter is eligible OR role is leader-ish, bias to Accepted.
		if ($hasInviter || $isLeaderCandidate) {
			if ($this->chance(0.88)) return ParticipationStatus::Accepted;
			if ($this->chance(0.06)) return ParticipationStatus::Pending;
			return ParticipationStatus::Invited;
		}

		$r = mt_rand() / mt_getrandmax();
		if ($r < 0.55) return ParticipationStatus::Pending;
		if ($r < 0.80) return ParticipationStatus::Invited;
		if ($r < 0.88) return ParticipationStatus::Maybe;
		if ($r < 0.94) return ParticipationStatus::Tentative;
		if ($r < 0.97) return ParticipationStatus::Declined;
		return ParticipationStatus::Expired;
	}

	/** @return array{can_write_own_files: bool, can_write_others_files: bool, can_read_others_files: bool} */
	private function resolvePermissions(ProjectRole $role): array
	{
		$writeOwn = !in_array($role, [ProjectRole::Viewer, ProjectRole::Guest], true);

		$writeOthers = in_array($role, [ProjectRole::Owner, ProjectRole::Admin], true)
			|| ($role === ProjectRole::Manager && $this->chance(0.80))
			|| ($role === ProjectRole::Developer && $this->chance(0.55));

		$readOthers = in_array($role, [ProjectRole::Owner, ProjectRole::Admin, ProjectRole::Manager, ProjectRole::Developer, ProjectRole::Contributor], true)
			|| ($role === ProjectRole::Reporter && $this->chance(0.60));

		return [
			'can_write_own_files' => $writeOwn,
			'can_write_others_files' => $writeOthers,
			'can_read_others_files' => $readOthers,
		];
	}

	private function pickRoleWeighted(): ProjectRole
	{
		// Larger count the more permissive the role is (per request).
		$roles = [
			ProjectRole::Owner,
			ProjectRole::Admin,
			ProjectRole::Manager,
			ProjectRole::Developer,
			ProjectRole::Member,
			ProjectRole::Contributor,
			ProjectRole::Reporter,
			ProjectRole::Viewer,
			ProjectRole::Guest,
		];

		$weights = [
			ProjectRole::Owner->value => 9,
			ProjectRole::Admin->value => 8,
			ProjectRole::Manager->value => 7,
			ProjectRole::Developer->value => 6,
			ProjectRole::Member->value => 5,
			ProjectRole::Contributor->value => 4,
			ProjectRole::Reporter->value => 3,
			ProjectRole::Viewer->value => 2,
			ProjectRole::Guest->value => 1,
		];

		$total = array_sum($weights);
		$r = (mt_rand() / mt_getrandmax()) * $total;
		$acc = 0.0;

		foreach ($roles as $role) {
			$acc += (float) ($weights[$role->value] ?? 1);
			if ($r <= $acc) return $role;
		}

		return ProjectRole::Member;
	}

	/** @return ProjectRole[] */
	private function buildRequiredRoles(int $targetTotal): array
	{
		$order = [
			ProjectRole::Owner,
			ProjectRole::Admin,
			ProjectRole::Manager,
			ProjectRole::Developer,
			ProjectRole::Member,
			ProjectRole::Contributor,
			ProjectRole::Reporter,
			ProjectRole::Viewer,
			ProjectRole::Guest,
		];

		$need = min(count($order), max(0, $targetTotal));
		return array_slice($order, 0, $need);
	}

	private function chance(float $p): bool
	{
		return mt_rand() / mt_getrandmax() < max(0.0, min(1.0, $p));
	}

	private function pickId(array $ids, float $nullChance = 0.1): ?string
	{
		if (!$ids || $this->chance($nullChance)) return null;
		return (string) $ids[array_rand($ids)];
	}

	private function generateInviteCodeUnique(int $attemptCap): ?string
	{
		// Pattern requested: INV-{UUID}-{timestamp}
		if ($attemptCap <= 0) $attemptCap = 10;

		$attempts = 0;
		do {
			$attempts++;
			$uuid = Str::uuid()->toString();
			$ts = CarbonImmutable::now()->timestamp;
			$code = "INV-{$uuid}-{$ts}";

			try {
				$exists = ProjectUser::query()->where(PJC::COL_INV_CD, $code)->exists();
				if (!$exists) return $code;
			} catch (\Throwable $e) {
				Log::debug(self::class . ' generateInviteCodeUnique exists check failed: ' . $e->getMessage());
				return $code;
			}
		} while ($attempts < $attemptCap);

		$this->out->writeln("<comment>[ProjectUserSeeder]</comment> Invite code attempts exhausted; returning null.");
		return null;
	}

	private function randomDecimalString(int $minWhole, int $maxWhole, int $scale): string
	{
		$whole = random_int($minWhole, $maxWhole);
		$fracMax = (int) pow(10, max(0, $scale)) - 1;
		$frac = random_int(0, max(0, $fracMax));
		return (string) $whole . '.' . str_pad((string) $frac, $scale, '0', STR_PAD_LEFT);
	}

	private function encodeJsonSafe(mixed $value): ?string
	{
		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable $e) {
			Log::debug(self::class . ' json_encode failed: ' . $e->getMessage());
			return null;
		}
	}

	private function randomNotes(string $role, string $status): string
	{
		$base = [
			"Seeded membership for role={$role}.",
			"Seeded invite_status={$status}.",
			"Seeded relationship: verify permissions and leader uniqueness.",
			"Seeded entry: validate invite code/url workflows.",
		];
		return $base[array_rand($base)];
	}

	private function fetchIds(string $table, int $limit = 20000): array
	{
		if (!Schema::hasTable($table)) return [];

		$limit = max(1, min(500000, $limit));

		try {
			$rows = DB::select("select id from {$table} limit {$limit}");
			$out = [];
			foreach ($rows as $r) {
				$id = (string) ($r->id ?? '');
				if ($id !== '') $out[] = $id;
			}
			return array_values($out);
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchIds failed for {$table}: " . $e->getMessage());
			return [];
		}
	}

	private function fetchRows(string $table, array $cols, int $limit = 20000): array
	{
		if (!Schema::hasTable($table)) return [];

		$limit = max(1, min(500000, $limit));
		$colsSql = implode(', ', array_map(static fn($c) => trim((string) $c), $cols));

		try {
			return DB::select("select {$colsSql} from {$table} limit {$limit}");
		} catch (\Throwable $e) {
			Log::debug(self::class . " fetchRows failed for {$table}: " . $e->getMessage(), ['cols' => $cols]);
			return [];
		}
	}
}
