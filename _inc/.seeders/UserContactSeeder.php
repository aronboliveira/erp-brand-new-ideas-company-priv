<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	EmailsConstants as EC,
	UsersConstants as UC
};
use App\Enums\ContactRole;
use App\Models\UserContact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class UserContactSeeder extends Seeder
{
	private const OWNER_RATIO = 0.25;
	private const MAX_PER_OWNER = 32;

	private const MAX_PICK_ATTEMPTS = 64;
	private const MAX_UNIQUE_ATTEMPTS = 48;

	public function run(): void
	{
		$out = new ConsoleOutput();

		$users = $this->fetchUsersRaw();
		$totalUsers = count($users);

		if ($totalUsers < 2) {
			$out->writeln('<comment>[UserContactSeeder]</comment> Not enough users to seed contacts.');
			return;
		}

		$owners = $this->pickOwners($users);
		if (count($owners) < 2) {
			$out->writeln('<comment>[UserContactSeeder]</comment> Not enough owners after sampling.');
			return;
		}

		$candidateIds = $this->buildCandidatePools($users);

		$tplIds = $this->fetchTemplateIdsIfAny();
		$emailIds = $this->fetchEmailIdsIfAny();

		$planned = $this->planPerOwnerCounts($owners);
		$plannedTotal = array_sum($planned);

		$capacity = count($owners) * self::MAX_PER_OWNER;
		$target = $this->resolveTargetCount($plannedTotal, $capacity);
		$planned = $this->adjustPlanToTarget($planned, $target);

		$out->writeln(sprintf(
			'<info>[UserContactSeeder]</info> owners=%d planned=%d target=%d capacity=%d',
			count($owners),
			$plannedTotal,
			array_sum($planned),
			$capacity
		));

		$created = 0;
		$perOwnerCreated = [];

		foreach ($owners as $owner) {
			$ownerId = (string) ($owner['id'] ?? '');
			if ($ownerId === '') continue;

			$limit = (int) ($planned[$ownerId] ?? 0);
			if ($limit <= 0) continue;

			$roles = $this->rolePoolForOwner($owner);
			shuffle($roles);
			$roles = array_slice($roles, 0, min(self::MAX_PER_OWNER, count($roles)));

			$attemptRoleIdx = 0;
			while (($perOwnerCreated[$ownerId] ?? 0) < $limit && $attemptRoleIdx < count($roles)) {
				$role = $roles[$attemptRoleIdx];
				$attemptRoleIdx++;

				$contactUserId = $this->pickContactUserIdForRole(
					$role,
					$candidateIds,
					$ownerId
				);

				if (!$contactUserId) continue;

				try {
					$payload = $this->buildContactPayload(
						ownerId: $ownerId,
						contactUserId: $contactUserId,
						role: $role,
						templateIds: $tplIds,
						emailIds: $emailIds
					);

					$out->writeln(
						'Creating contact for owner ' . $ownerId . ' contact ' . $contactUserId . ' role ' . $role->value
					);
					UserContact::create($payload);

					$created++;
					$perOwnerCreated[$ownerId] = (int) (($perOwnerCreated[$ownerId] ?? 0) + 1);

					if ($created % 64 === 0) {
						$out->writeln(sprintf('<info>[UserContactSeeder]</info> created=%d', $created));
					}
				} catch (\Throwable $e) {
					Log::debug('[UserContactSeeder] create failed', [
						'owner_id' => $ownerId,
						'user_id'  => $contactUserId,
						'role'     => $role->value,
						'error'    => $e->getMessage(),
					]);
				}
			}
		}

		$out->writeln(sprintf('<info>[UserContactSeeder]</info> done created=%d', $created));
	}

	private function getOut(): OutputInterface
	{
		try {
			if ($this->command) return $this->command->getOutput();
		} catch (\Throwable) {
		}
		return new ConsoleOutput();
	}

	private function fetchUsersRaw(): array
	{
		$tbl = DC::TABLE_USERS;
		$empCol = UC::COL_EMP_ID;

		$sql = "select id, type, {$empCol} as emp_id, created_at from {$tbl}";
		$rows = DB::select($sql);

		$out = [];
		foreach ($rows as $r) {
			$a = (array) $r;
			$id = (string) ($a['id'] ?? '');
			if ($id === '') continue;

			$out[] = [
				'id' => $id,
				'type' => strtolower(trim((string) ($a['type'] ?? ''))),
				'emp_id' => is_string($a['emp_id'] ?? null) ? trim((string) $a['emp_id']) : '',
				'created_at' => $a['created_at'] ?? null,
			];
		}

		return $out;
	}

	private function pickOwners(array $users): array
	{
		$n = (int) ceil(count($users) * self::OWNER_RATIO);
		$n = max(2, $n);

		if ($n % 2 !== 0 && $n < count($users)) $n++; // capacidade fica múltipla de 64 (32 * owners pares)

		$tmp = $users;
		shuffle($tmp);

		return array_slice($tmp, 0, min($n, count($tmp)));
	}

	private function buildCandidatePools(array $users): array
	{
		$businessTypes = ['admin', 'super admin', 'company', 'vendor', 'accountant'];

		$any = [];
		$business = [];
		$personal = [];

		foreach ($users as $u) {
			$id = (string) ($u['id'] ?? '');
			if ($id === '') continue;

			$any[] = $id;

			$type = (string) ($u['type'] ?? '');
			$hasEmp = is_string($u['emp_id'] ?? null) && trim((string) $u['emp_id']) !== '';

			if ($hasEmp || in_array($type, $businessTypes, true)) {
				$business[] = $id;
			} else {
				$personal[] = $id;
			}
		}

		return [
			'any' => $any,
			'business' => $business,
			'personal' => $personal,
		];
	}

	private function rolePoolForOwner(array $owner): array
	{
		$type = (string) ($owner['type'] ?? '');
		$hasEmp = is_string($owner['emp_id'] ?? null) && trim((string) $owner['emp_id']) !== '';

		$businessOnly = $hasEmp || in_array($type, ['admin', 'super admin', 'company', 'vendor', 'accountant'], true);

		$roles = ContactRole::cases();

		if ($businessOnly) {
			$out = [];
			foreach ($roles as $r) {
				if ($r->isBusinessRole()) $out[] = $r;
			}
			return $out;
		}

		$out = [];
		foreach ($roles as $r) {
			if ($r->isBusinessRole() || $r->isPersonalRole() || $r === ContactRole::Other) $out[] = $r;
		}
		return $out;
	}

	private function pickContactUserIdForRole(ContactRole $role, array $pool, string $ownerId): ?string
	{
		$kind = $role->isPersonalRole() ? 'personal' : 'business';
		$list = $pool[$kind] ?? [];
		if (!is_array($list) || empty($list)) $list = $pool['any'] ?? [];

		$tries = 0;
		while ($tries++ < self::MAX_PICK_ATTEMPTS) {
			$idx = random_int(0, max(0, count($list) - 1));
			$id = $list[$idx] ?? '';
			$id = is_string($id) ? trim($id) : '';
			if ($id === '' || $id === $ownerId) continue;
			return $id;
		}

		return null;
	}

	private function buildContactPayload(
		string $ownerId,
		string $contactUserId,
		ContactRole $role,
		array $templateIds,
		array $emailIds
	): array {
		$isBlocked = $this->randBool(0.04);
		$isMuted = $this->randBool(0.10);
		$isFav = $isBlocked ? false : $this->randBool(0.05);

		$name = $this->makeUniqueName($ownerId, $contactUserId, $role);
		$email = $this->makeUniqueEmail();
		$phoneAttemps = 0;
		do $phone = fake()->boolean(75) ? Utility::generateBrazilianPhone() : fake()->phoneNumber();
		while (
			DC::TABLE_USR_CTT
			&& $phone !== null
			&& $this->rawExists(DC::TABLE_USR_CTT, 'phone', $phone)
			&& ++$phoneAttemps < self::MAX_UNIQUE_ATTEMPTS
		);

		$birthday = $this->randomAdultBirthday();

		$lastContact = $this->randomPastDateTime(720);

		$tags = $this->makeTagsForRole($role);
		$templates = $this->pickTemplates($templateIds, $role);
		$sm = $this->makeSocialMedia();

		$emailRecordId = $this->pickOptionalId($emailIds, 0.18);

		return [
			UC::COL_USER_ID => $contactUserId,
			EC::COL_PRT_ID  => $ownerId,

			'name'    => $name,
			'company' => null,
			'role'    => $role,

			'email'   => $email,
			'phone'   => $phone,

			AC::COL_EM_ID => $emailRecordId,

			'address' => $this->randomBrazilAddressOrNull(0.55),
			AC::COL_SC_MD => $sm,

			'notes'   => $this->randBool(0.35) ? $this->randomNote($role) : null,
			'avatar'  => $this->randBool(0.30) ? ('https://example.test/avatars/' . Str::uuid() . '.png') : null,

			'birthday' => $birthday,

			AC::COL_IS_BLK => $isBlocked,
			AC::COL_IS_MT  => $isMuted,
			AC::COL_IS_FV  => $isFav,
			AC::COL_LST_CT => $lastContact,

			'tags' => $tags,
			'templates' => $templates,

			DC::COL_TABLE_UPDATER => $ownerId,
		];
	}

	private function makeUniqueName(string $ownerId, string $contactUserId, ContactRole $role): string
	{
		$tbl = DC::TABLE_USR_CTT;

		$tries = 0;
		while ($tries++ < self::MAX_UNIQUE_ATTEMPTS) {
			$s = 'ctt_' . substr($ownerId, 0, 8) . '_' . substr($contactUserId, 0, 8) . '_' . substr($role->value, 0, 24) . '_' . substr((string) Str::uuid(), 0, 8);
			if (!$this->rawExists($tbl, 'name', $s)) return $s;
		}

		return (string) Str::uuid();
	}

	private function makeUniqueEmail(): string
	{
		$tbl = DC::TABLE_USR_CTT;

		$tries = 0;
		while ($tries++ < self::MAX_UNIQUE_ATTEMPTS) {
			$local = 'contact+' . substr((string) Str::uuid(), 0, 16);
			$email = $local . '@example.test';
			if (!$this->rawExists($tbl, 'email', $email)) return $email;
		}

		return 'contact+' . substr((string) Str::uuid(), 0, 20) . '@example.test';
	}

	private function rawExists(string $table, string $column, string $value): bool
	{
		$table = trim($table);
		$column = trim($column);

		if ($table === '' || $column === '' || $value === '') return false;

		try {
			$sql = "select 1 as x from {$table} where {$column} = ? limit 1";
			return DB::selectOne($sql, [$value]) !== null;
		} catch (\Throwable) {
			return false;
		}
	}

	private function randomAdultBirthday(): ?string
	{
		$ageMin = 18;
		$ageMax = 70;

		$years = random_int($ageMin, $ageMax);
		$days = random_int(0, 365);

		return Carbon::now('America/Sao_Paulo')
			->subYears($years)
			->subDays($days)
			->toDateString();
	}

	private function randomPastDateTime(int $maxDaysBack): ?Carbon
	{
		$days = random_int(0, max(0, $maxDaysBack));
		$minutes = random_int(0, 24 * 60 - 1);

		return Carbon::now('America/Sao_Paulo')->subDays($days)->subMinutes($minutes);
	}

	private function makeTagsForRole(ContactRole $role): ?array
	{
		if (!$this->randBool(0.55)) return null;

		$base = [
			'contact',
			$role->getCategory(),
			$role->value,
		];

		$extra = [];
		if ($role->isBusinessRole()) {
			$extra = ['biz', 'priority_' . $role->getPriority()];
		} else {
			$extra = ['personal'];
		}

		$tags = collect(array_merge($base, $extra))
			->map(fn($v) => is_scalar($v) ? trim((string) $v) : '')
			->filter(fn($v) => $v !== '')
			->unique()
			->values()
			->take(16)
			->all();

		return $tags ?: null;
	}

	private function pickTemplates(array $templateIds, ContactRole $role): ?array
	{
		if (empty($templateIds)) return null;
		if (!$this->randBool($role->isBusinessRole() ? 0.35 : 0.18)) return null;

		$max = $role->isBusinessRole() ? 4 : 2;
		$count = random_int(1, $max);

		$picked = [];
		$tries = 0;

		while (count($picked) < $count && $tries++ < 32) {
			$idx = random_int(0, count($templateIds) - 1);
			$id = $templateIds[$idx] ?? null;
			if (!is_string($id) || trim($id) === '') continue;
			$picked[] = trim($id);
			$picked = array_values(array_unique($picked));
		}

		return $picked ?: null;
	}

	private function makeSocialMedia(): ?array
	{
		if (!$this->randBool(0.30)) return null;

		$options = [
			'linkedin' => 'https://www.linkedin.com/in/' . substr((string) Str::uuid(), 0, 8),
			'instagram' => 'https://www.instagram.com/' . substr((string) Str::uuid(), 0, 8),
			'github' => 'https://github.com/' . substr((string) Str::uuid(), 0, 8),
			'whatsapp' => 'https://wa.me/55' . random_int(11, 99) . '9' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
		];

		$keys = array_keys($options);
		shuffle($keys);

		$take = random_int(1, 3);

		$out = [];
		for ($i = 0; $i < $take; $i++) {
			$k = $keys[$i] ?? null;
			if (!is_string($k) || $k === '') continue;
			$out[$k] = $options[$k];
		}

		return $out ?: null;
	}

	private function randomBrazilAddressOrNull(float $probability): ?string
	{
		if (!$this->randBool($probability)) return null;

		$streets = ['Rua', 'Avenida', 'Travessa', 'Alameda'];
		$names = ['Paulista', 'Brasil', 'Ibirapuera', 'Santos Dumont', 'Copacabana', 'Sete de Setembro', 'Dom Pedro II'];
		$cities = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Salvador', 'Fortaleza', 'Recife', 'Porto Alegre'];
		$ufs = ['SP', 'RJ', 'MG', 'PR', 'BA', 'CE', 'PE', 'RS'];

		$s = $streets[random_int(0, count($streets) - 1)] . ' ' . $names[random_int(0, count($names) - 1)];
		$n = random_int(10, 5000);
		$cidx = random_int(0, count($cities) - 1);

		return "{$s}, {$n} - {$cities[$cidx]} - {$ufs[$cidx]}";
	}

	private function randomNote(ContactRole $role): string
	{
		$p = $role->getPriority();
		return "Seeded contact role={$role->value} category={$role->getCategory()} priority={$p}";
	}

	private function randBool(float $pTrue): bool
	{
		$pTrue = max(0.0, min(1.0, $pTrue));
		return (random_int(0, 10000) / 10000) < $pTrue;
	}

	private function fetchTemplateIdsIfAny(): array
	{
		$ids = [];

		$tables = [];
		if (defined(DC::class . '::TABLE_EMAIL_TEMPLATES')) $tables[] = constant(DC::class . '::TABLE_EMAIL_TEMPLATES');
		if (defined(DC::class . '::TABLE_NOTIFICATION_TEMPLATES')) $tables[] = constant(DC::class . '::TABLE_NOTIFICATION_TEMPLATES');

		foreach ($tables as $t) {
			if (!is_string($t) || $t === '' || !Schema::hasTable($t)) continue;
			try {
				$rows = DB::select("select id from {$t} limit 512");
				foreach ($rows as $r) {
					$a = (array) $r;
					$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
					if ($id !== '') $ids[] = $id;
				}
			} catch (\Throwable) {
			}
		}

		return array_values(array_unique($ids));
	}

	private function fetchEmailIdsIfAny(): array
	{
		if (!Schema::hasTable(DC::TABLE_EMAILS)) return [];

		try {
			$rows = DB::select("select id from " . DC::TABLE_EMAILS . " limit 512");
			$ids = [];
			foreach ($rows as $r) {
				$a = (array) $r;
				$id = is_string($a['id'] ?? null) ? trim((string) $a['id']) : '';
				if ($id !== '') $ids[] = $id;
			}
			return array_values(array_unique($ids));
		} catch (\Throwable) {
			return [];
		}
	}

	private function pickOptionalId(array $ids, float $p): ?string
	{
		if (empty($ids) || !$this->randBool($p)) return null;

		$tries = 0;
		while ($tries++ < 16) {
			$idx = random_int(0, count($ids) - 1);
			$id = $ids[$idx] ?? null;
			if (is_string($id) && trim($id) !== '') return trim($id);
		}

		return null;
	}

	private function planPerOwnerCounts(array $owners): array
	{
		$plan = [];
		foreach ($owners as $o) {
			$id = (string) ($o['id'] ?? '');
			if ($id === '') continue;

			$min = 8;
			$max = self::MAX_PER_OWNER;

			$plan[$id] = random_int($min, $max);
		}
		return $plan;
	}

	private function resolveTargetCount(int $rawTotal, int $capacity): int
	{
		$rawTotal = max(0, $rawTotal);
		$target = min($rawTotal, $capacity);

		if ($target <= 0) return 0;

		$mod = $target % 64;
		if ($mod === 0) return $target;

		$up = $target + (64 - $mod);
		if ($up <= $capacity) return $up;

		$down = $target - $mod;
		return max(0, $down);
	}

	private function adjustPlanToTarget(array $plan, int $target): array
	{
		$ids = array_keys($plan);
		if (empty($ids)) return $plan;

		$sum = array_sum($plan);

		if ($sum === $target) return $plan;

		$guard = 0;
		while ($sum < $target && $guard++ < 4096) {
			$changed = false;

			foreach ($ids as $id) {
				if ($sum >= $target) break;

				$cur = (int) ($plan[$id] ?? 0);
				if ($cur >= self::MAX_PER_OWNER) continue;

				$plan[$id] = $cur + 1;
				$sum++;
				$changed = true;

				if ($sum >= $target) break;
			}

			if (!$changed) break;
		}

		$guard = 0;
		while ($sum > $target && $guard++ < 4096) {
			foreach ($ids as $id) {
				if ($sum <= $target) break;

				$cur = (int) ($plan[$id] ?? 0);
				if ($cur <= 0) continue;

				$plan[$id] = $cur - 1;
				$sum--;
				if ($sum <= $target) break;
			}
		}

		return $plan;
	}
}
