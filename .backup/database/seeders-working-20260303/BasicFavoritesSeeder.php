<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AppModuleType;
use App\Models\BasicFavorite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\Console\Output\ConsoleOutput;
use ReflectionClass;

class BasicFavoritesSeeder extends Seeder
{
	private ConsoleOutput $out;

	private const HARD_CAP = 32000;
	private const IDS_PER_TABLE = 2048;
	private const MAX_FAVS_PER_USER = 3;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_BSC_FV) || !Schema::hasTable(DC::TABLE_USERS))
			return;

		$users = DB::select('SELECT id FROM ' . DC::TABLE_USERS . ' WHERE id IS NOT NULL');
		$userIds = [];
		foreach ($users as $r) {
			$id = trim((string) ($r->id ?? ''));
			if ($id !== '') $userIds[] = $id;
		}
		$userIds = array_values(array_unique($userIds));
		if ($userIds === []) return;

		$targets = $this->loadTargets();
		if ($targets === []) return;

		$upper = min(self::HARD_CAP, count($targets));
		$minUsersWithFav = (int) ceil(count($userIds) * 0.20);
		$minUsersWithFav = min($minUsersWithFav, $upper);

		$rawTotal = min($upper, max($minUsersWithFav, (int) floor(count($userIds) * 0.50)));
		$total = $this->normalizeTo64MultipleNotExceeding($rawTotal, $upper);
		if ($total <= 0) return;

		shuffle($userIds);
		shuffle($targets);

		$assignments = [];
		$userCounts = [];

		$base = min($minUsersWithFav, $total, count($userIds));
		for ($i = 0; $i < $base; $i++) {
			$u = $userIds[$i];
			$userCounts[$u] = ($userCounts[$u] ?? 0) + 1;
			$assignments[] = $u;
		}

		$remaining = $total - $base;
		$userCount = count($userIds);

		for ($i = 0; $i < $remaining; $i++) {
			$attempts = 0;
			$maxAttempts = max(40, $userCount * 2);

			while ($attempts++ < $maxAttempts) {
				$idx = random_int(0, $userCount - 1);
				$u = $userIds[$idx];
				$c = $userCounts[$u] ?? 0;
				if ($c >= self::MAX_FAVS_PER_USER) continue;
				$userCounts[$u] = $c + 1;
				$assignments[] = $u;
				break;
			}

			if ($attempts >= $maxAttempts)
				break;
		}

		$total = min(count($assignments), count($targets));
		$total = $this->normalizeTo64MultipleNotExceeding($total, min(self::HARD_CAP, count($targets)));
		if ($total <= 0) return;

		/** @var array<string, bool> */
		$usedFavIds = [];
		$created = 0;

		for ($i = 0; $i < $total; $i++) {
			$userId = $assignments[$i] ?? null;
			$t = $targets[$i] ?? null;
			if (!$userId || !$t) break;

			$favId = (string) ($t['id'] ?? '');
			$tb = (string) ($t['table'] ?? '');
			if (empty($favId) || empty($tb)) {
				$this->out->writeln('BasicFavoritesSeeder: skipping invalid target at index ' . $i);
				continue;
			}

			if (isset($usedFavIds[$favId])) {
				$this->out->writeln('BasicFavoritesSeeder: skipping already used fav ID at index ' . $i);
				continue;
			}

			$attempts = 0;
			$maxAttempts = 16;
			while ($attempts++ < $maxAttempts) {
				$exists = (bool) DB::selectOne(
					'SELECT 1 FROM ' . DC::TABLE_BSC_FV . ' WHERE ' . AC::COL_FV_ID . ' = ? LIMIT 1',
					[$favId]
				);
				if (!$exists) break;
				$swapIdx = random_int(0, min(count($targets) - 1, $total - 1));
				$t2 = $targets[$swapIdx] ?? null;
				$favId2 = (string) ($t2['id'] ?? '');
				$tb2 = (string) ($t2['table'] ?? '');
				if ($t2 && !empty($favId2) && !empty($tb2) && !isset($usedFavIds[$favId2])) {
					$favId = $favId2;
					$tb = $tb2;
				}
				if (isset($usedFavIds[$favId])) $usedFavIds[$favId] = true;
				if (isset($usedFavIds[$favId2])) $usedFavIds[$favId2] = true;
			}

			if ($attempts >= $maxAttempts) {
				$this->out->writeln('BasicFavoritesSeeder: skipping, could not find unused fav ID at index ' . $i);
				continue;
			}

			$usedFavIds[$favId] = true;

			$module = $this->guessModuleForTable($tb);
			$notes = $this->maybeNote();

			$this->out->writeln(sprintf(
				'BasicFavorite user=%s module=%s table=%s fav=%s',
				substr($userId, 0, 8),
				$module->value,
				$tb,
				substr($favId, 0, 8)
			));

			try {
				BasicFavorite::create([
					AC::COL_FV_ID => $favId,
					'module' => $module->value,
					AC::COL_FV_TB => $tb,
					UC::COL_USER_ID => $userId,
					'notes' => $notes,
				]);

				$this->out->writeln("<info>[BasicFavoritesSeeder] created favorite for user " . substr($userId, 0, 8) . " fav " . substr($favId, 0, 8) . "</info>");
			} catch (\Throwable $e) {
				$this->out->writeln("<error>[BasicFavoritesSeeder] failed to create favorite for user " . substr($userId, 0, 8) . " fav " . substr($favId, 0, 8) . ": " . $e->getMessage() . "</error>");
				continue;
			}
			$created++;
		}

		$created = $this->normalizeTo64MultipleNotExceeding($created, $created);
	}

	private function loadTargets(): array
	{
		$tables = $this->candidateTables();
		if ($tables === []) return [];

		$out = [];
		foreach ($tables as $tb) {
			if (!Schema::hasTable($tb) || !Schema::hasColumn($tb, 'id')) continue;

			$maxAttempts = 3;
			$attempts = 0;
			$rows = null;

			while ($attempts++ < $maxAttempts) {
				$rows = DB::select('SELECT id FROM ' . $tb . ' WHERE id IS NOT NULL LIMIT ' . (int) self::IDS_PER_TABLE);
				if (is_array($rows)) break;
			}

			if (!is_array($rows) || $rows === []) continue;

			foreach ($rows as $r) {
				$id = trim((string) ($r->id ?? ''));
				if ($id === '') continue;
				$out[] = ['table' => $tb, 'id' => $id];
			}
		}

		return $out;
	}

	private function candidateTables(): array
	{
		$out = [];
		try {
			$ref = new ReflectionClass(DC::class);
			foreach ($ref->getConstants() as $k => $v) {
				if (!is_string($k) || !is_string($v)) continue;
				if (!str_starts_with($k, 'TABLE_')) continue;
				$vv = trim($v);
				if ($vv === '' || !preg_match('/^[a-z0-9_]+$/i', $vv)) continue;
				$out[] = $vv;
			}
		} catch (\Throwable) {
			$out = [];
		}

		$out = array_values(array_unique($out));
		$out = array_values(array_filter($out, fn(string $t) => $t !== DC::TABLE_BSC_FV));
		return $out;
	}

	private function normalizeTo64MultipleNotExceeding(int $raw, int $upper): int
	{
		$upper = max(0, $upper);
		$raw = max(0, $raw);
		if ($raw === 0 || $upper === 0) return 0;

		$wanted = $raw + ((64 - ($raw % 64)) % 64);
		$wanted = min($wanted, $upper);
		$wanted -= ($wanted % 64);

		if ($wanted === 0 && $upper >= 64)
			return 64;

		return $wanted;
	}

	private function guessModuleForTable(string $table): AppModuleType
	{
		$t = strtolower(trim($table));
		$cases = AppModuleType::cases();

		foreach ($cases as $c) {
			$v = strtolower((string) $c->value);
			if ($v !== '' && (str_contains($t, $v) || str_contains($v, $t)))
				return $c;
		}

		return AppModuleType::Other;
	}

	private function maybeNote(): ?string
	{
		$r = random_int(0, 9);
		return $r < 6 ? null : ('fav:' . bin2hex(random_bytes(6)));
	}
}
