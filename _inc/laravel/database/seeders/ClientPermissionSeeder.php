<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Models\ClientPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\Console\Output\ConsoleOutput;

class ClientPermissionSeeder extends Seeder
{
	private ConsoleOutput $out;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasTable(DC::TABLE_CLT_PRM)) return;
		if (!Schema::hasColumn(DC::TABLE_USERS, 'id') || !Schema::hasColumn(DC::TABLE_USERS, UC::COL_U_TP)) return;

		$clientIds = $this->readClientUserIds();
		if ($clientIds === []) return;

		$permPool = $this->readAllowedPermissionPool();

		foreach ($clientIds as $uid) {
			$uid = trim((string) $uid);
			if ($uid === '') continue;

			$permissionsText = $this->pickPermissionsText($permPool);
			$existingId = $this->existingPermissionRowId($uid, 'user_client');

			$m = null;

			if ($existingId !== '') {
				$m = ClientPermission::query()->whereKey($existingId)->first();
				if (!$m) {
					$m = new ClientPermission();
					$m->setAttribute($m->getKeyName(), $existingId);
					$m->exists = true;
				}
			} else {
				$m = new ClientPermission();
			}

			$m->setAttribute(PJC::COL_CLIENT_ID, $uid);
			$m->setAttribute('type', 'user_client');
			$m->setAttribute(PJC::COL_DL_ID, null);
			$m->setAttribute(PJC::COL_CTC_ID, null);
			$m->setAttribute('permissions', $permissionsText);

			$this->out->writeln(sprintf(
				'[ClientPermission] client_id=%s type=%s perms=%d %s',
				$uid,
				'user_client',
				$permissionsText === '' ? 0 : count(preg_split('/\R+/', trim($permissionsText)) ?: []),
				$existingId !== '' ? '(update)' : '(create)'
			));

			$m->save();
		}
	}

	private function readClientUserIds(): array
	{
		$sql = 'SELECT id FROM ' . DC::TABLE_USERS . ' WHERE ' . UC::COL_U_TP . ' = ?';
		$rows = DB::select($sql, [UserType::Client->value]);

		$out = [];
		foreach ($rows as $r) {
			$id = $r->id ?? null;
			if (!is_scalar($id)) continue;
			$s = trim((string) $id);
			if ($s === '') continue;
			$out[] = $s;
		}

		return $out;
	}

	private function existingPermissionRowId(string $clientId, string $type): string
	{
		try {
			$v = DB::table(DC::TABLE_CLT_PRM)
				->where(PJC::COL_CLIENT_ID, $clientId)
				->where('type', $type)
				->value('id');

			return is_scalar($v) ? trim((string) $v) : '';
		} catch (\Throwable) {
			return '';
		}
	}

	private function readAllowedPermissionPool(): array
	{
		$allowedNamesLower = $this->clientLikePermissionNamesLower();
		if ($allowedNamesLower === []) return ['ids' => [], 'names' => []];

		$namesLower = array_values(array_keys($allowedNamesLower));

		$hasPerms = Schema::hasTable(DC::TABLE_PERMISSIONS)
			&& Schema::hasColumn(DC::TABLE_PERMISSIONS, 'id')
			&& Schema::hasColumn(DC::TABLE_PERMISSIONS, 'name');

		if (!$hasPerms) return ['ids' => [], 'names' => $namesLower];

		$placeholders = implode(',', array_fill(0, count($namesLower), '?'));
		$sql = 'SELECT id, name FROM ' . DC::TABLE_PERMISSIONS . ' WHERE LOWER(name) IN (' . $placeholders . ')';
		$rows = DB::select($sql, $namesLower);

		$ids = [];
		foreach ($rows as $r) {
			$id = $r->id ?? null;
			$name = $r->name ?? null;
			if (!is_scalar($id) || !is_scalar($name)) continue;
			$sid = trim((string) $id);
			$sname = trim((string) $name);
			if ($sid === '' || $sname === '') continue;
			$ln = mb_strtolower($sname);
			if (!isset($allowedNamesLower[$ln])) continue;
			$ids[$sid] = true;
		}

		return ['ids' => array_values(array_keys($ids)), 'names' => $namesLower];
	}

	private function pickPermissionsText(array $pool): string
	{
		$ids = $pool['ids'] ?? [];
		$namesLower = $pool['names'] ?? [];

		$attempts = 0;
		$maxAttempts = 8;

		while ($attempts < $maxAttempts) {
			$attempts++;

			if (is_array($ids) && $ids !== []) {
				$n = random_int(2, min(12, count($ids)));
				$picked = $this->pickRandomUnique($ids, $n);
				if ($picked !== []) return implode("\n", $picked);
			}

			if (is_array($namesLower) && $namesLower !== []) {
				$n = random_int(2, min(12, count($namesLower)));
				$picked = $this->pickRandomUnique($namesLower, $n);
				if ($picked !== []) return implode("\n", $picked);
			}
		}

		return '';
	}

	private function pickRandomUnique(array $list, int $n): array
	{
		$list = array_values($list);
		$max = count($list);
		if ($max <= 0 || $n <= 0) return [];

		$n = min($n, $max);
		$seen = [];
		$out = [];

		$attempts = 0;
		$maxAttempts = max(16, $n * 8);

		while (count($out) < $n && $attempts < $maxAttempts) {
			$attempts++;
			$i = random_int(0, $max - 1);
			$v = $list[$i] ?? null;
			if (!is_scalar($v)) continue;
			$s = trim((string) $v);
			if ($s === '') continue;
			if (isset($seen[$s])) continue;
			$seen[$s] = true;
			$out[] = $s;
		}

		return $out;
	}

	private function clientLikePermissionNamesLower(): array
	{
		$clsCandidates = [
			'Database\\Seeders\\SeedersTemplating',
			'App\\Seeders\\SeedersTemplating',
			'App\\Support\\SeedersTemplating',
			'SeedersTemplating',
		];

		$rows = null;
		foreach ($clsCandidates as $cls) {
			if (!class_exists($cls)) continue;
			if (!defined($cls . '::CLIENTLIKE_PERMS')) continue;
			$rows = constant($cls . '::CLIENTLIKE_PERMS');
			break;
		}

		$set = [];
		if (is_array($rows)) {
			foreach ($rows as $r) {
				if (!is_array($r)) continue;
				$name = $r['name'] ?? null;
				if (!is_scalar($name)) continue;
				$s = trim((string) $name);
				if ($s === '') continue;
				$set[mb_strtolower($s)] = true;
			}
		}

		return $set;
	}
}
