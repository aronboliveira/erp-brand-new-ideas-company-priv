<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Models\ClientPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ClientPermissionSeeder extends Seeder
{
    private ConsoleOutput $out;

    private const MIN_ROWS = 64;
    private const HARD_CAP = 2048;
    private const MIN_PER_TYPE = 8;

    /** @var string[] */
    private const TYPES = [
        'client',
        'customer',
        'vendor',
        'company',
        'user_client',
        'user_customer',
        'user_vendor',
        'user_company',
    ];

    public function __construct()
    {
        $this->out = new ConsoleOutput();
    }

    public function run(): void
    {
        if (!$this->hasTableAndColumns()) return;

        $currentTotal = $this->countRowsSafe(DC::TABLE_CLT_PRM);
        if ($currentTotal >= self::HARD_CAP) {
            $this->out->writeln('<comment>[ClientPermissionSeeder]</comment> Hard cap reached. Skipping.');
            return;
        }

        $permPool = $this->readAllowedPermissionPool();

        $dealIds = $this->fetchIdsSafe(defined(DC::class . '::TABLE_DEALS') ? DC::TABLE_DEALS : '', 1024);
        $contractIds = $this->fetchIdsSafe(defined(DC::class . '::TABLE_CONTRACTS') ? DC::TABLE_CONTRACTS : '', 1024);

        $poolsByType = $this->buildPoolsByType();

        $existingCountsByType = $this->existingCountsByType();
        $pairsToCreate = $this->planPairsToCreate($poolsByType, $existingCountsByType, $currentTotal);

        if ($pairsToCreate === []) {
            $this->out->writeln('<comment>[ClientPermissionSeeder]</comment> Nothing to create (no available pairs).');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($pairsToCreate as $pair) {
            if ($this->countRowsSafe(DC::TABLE_CLT_PRM) >= self::HARD_CAP) break;

            $type = (string)($pair['type'] ?? '');
            $clientId = (string)($pair[PJC::COL_CLIENT_ID] ?? '');

            if ($type === '' || $clientId === '') {
                $skipped++;
                continue;
            }

            // Defensive: avoid unique collision if something else inserted concurrently
            if ($this->pairExists($clientId, $type)) {
                $skipped++;
                continue;
            }

            $permissionsText = $this->pickPermissionsTextNonEmpty($permPool);

            $m = new ClientPermission();
            $m->setAttribute('id', (string) Str::uuid());
            $m->setAttribute(PJC::COL_CLIENT_ID, $clientId);
            $m->setAttribute('type', $type);

            // Keep these sparse and only if we have valid referenced IDs
            $m->setAttribute(PJC::COL_DL_ID, ($dealIds && random_int(0, 99) < 12) ? $this->pickRandomId($dealIds) : null);
            $m->setAttribute(PJC::COL_CTC_ID, ($contractIds && random_int(0, 99) < 12) ? $this->pickRandomId($contractIds) : null);

            $m->setAttribute('permissions', $permissionsText);

            try {
                $m->save();
                $created++;

                $this->out->writeln(sprintf(
                    '[ClientPermission] create client_id=%s type=%s perms=%d',
                    $clientId,
                    $type,
                    $permissionsText === '' ? 0 : count(preg_split('/\R+/', trim($permissionsText)) ?: [])
                ));
            } catch (\Throwable $e) {
                $skipped++;
                Log::warning(static::class . ' failed saving ClientPermission', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'client_id' => $clientId,
                    'type' => $type,
                ]);
            }
        }

        $finalTotal = $this->countRowsSafe(DC::TABLE_CLT_PRM);
        $finalCounts = $this->existingCountsByType();

        $this->out->writeln(sprintf(
            '<comment>[ClientPermissionSeeder]</comment> Done. created=%d skipped=%d total=%d',
            $created,
            $skipped,
            $finalTotal
        ));

        foreach (self::TYPES as $t) {
            $this->out->writeln(sprintf(
                '<comment>[ClientPermissionSeeder]</comment> type=%s count=%d',
                $t,
                (int)($finalCounts[$t] ?? 0)
            ));
        }
    }

    private function hasTableAndColumns(): bool
    {
        try {
            if (!Schema::hasTable(DC::TABLE_CLT_PRM)) return false;

            foreach (['id', PJC::COL_CLIENT_ID, 'type', 'permissions'] as $col) {
                if (!Schema::hasColumn(DC::TABLE_CLT_PRM, $col)) return false;
            }

            if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasColumn(DC::TABLE_USERS, 'id')) return false;
            if (!Schema::hasColumn(DC::TABLE_USERS, UC::COL_U_TP) && !Schema::hasColumn(DC::TABLE_USERS, 'type')) return false;

            // Model validates non-user types against clients table today
            if (!defined(DC::class . '::TABLE_CLIENTS')) return false;
            if (!Schema::hasTable(DC::TABLE_CLIENTS) || !Schema::hasColumn(DC::TABLE_CLIENTS, 'id')) return false;

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Pools aligned with current ClientPermission::normalizeClientIdentity():
     * - non-user types MUST use DC::TABLE_CLIENTS ids
     * - user_* types use users filtered by UserType
     */
    private function buildPoolsByType(): array
    {
        $clients = $this->fetchIdsSafe(DC::TABLE_CLIENTS, 50000);

        $userClient = $this->readUserIdsByUserType(UserType::Client->value);
        $userCustomer = $this->readUserIdsByUserType(UserType::Customer->value);
        $userVendor = $this->readUserIdsByUserType(UserType::Vendor->value);
        $userCompany = $this->readUserIdsByUserType(UserType::Company->value);

        return [
            // non-user types -> clients table only (no fallback to users; model would reject)
            'client' => $clients,
            'customer' => $clients,
            'vendor' => $clients,
            'company' => $clients,

            // user_* -> users table by UC::COL_U_TP
            'user_client' => $userClient,
            'user_customer' => $userCustomer,
            'user_vendor' => $userVendor,
            'user_company' => $userCompany,
        ];
    }

    private function planPairsToCreate(array $poolsByType, array $existingCountsByType, int $currentTotal): array
    {
        $pairs = [];
        $remaining = max(0, self::HARD_CAP - $currentTotal);
        if ($remaining <= 0) return [];

        // 1) Per-type quota first
        foreach (self::TYPES as $type) {
            if ($remaining <= 0) break;

            $pool = $this->normalizeIdList($poolsByType[$type] ?? []);
            if ($pool === []) {
                $this->out->writeln(sprintf(
                    '<comment>[ClientPermissionSeeder]</comment> type=%s pool empty; cannot satisfy MIN_PER_TYPE=%d.',
                    $type,
                    self::MIN_PER_TYPE
                ));
                continue;
            }

            $existing = (int)($existingCountsByType[$type] ?? 0);
            $need = max(0, self::MIN_PER_TYPE - $existing);
            if ($need <= 0) continue;

            $used = array_fill_keys($this->existingClientIdsForType($type), true);

            $available = [];
            foreach ($pool as $id) {
                if (!isset($used[$id])) $available[] = $id;
            }

            if ($available === []) continue;

            shuffle($available);
            $take = min($need, count($available), $remaining);

            for ($i = 0; $i < $take; $i++) {
                $pairs[] = ['type' => $type, PJC::COL_CLIENT_ID => $available[$i]];
                $remaining--;
                $currentTotal++;
            }
        }

        if ($remaining <= 0) return $pairs;

        // 2) Pad to MIN_ROWS overall (best-effort)
        $targetTotal = min(self::HARD_CAP, max(self::MIN_ROWS, $currentTotal));
        $needTotal = max(0, $targetTotal - $currentTotal);

        if ($needTotal <= 0) return $pairs;

        $availableByType = [];
        foreach (self::TYPES as $type) {
            $pool = $this->normalizeIdList($poolsByType[$type] ?? []);
            if ($pool === []) continue;

            $used = array_fill_keys($this->existingClientIdsForType($type), true);

            // also exclude already planned in this run for that type
            foreach ($pairs as $p) {
                if (($p['type'] ?? null) === $type) {
                    $cid = (string)($p[PJC::COL_CLIENT_ID] ?? '');
                    if ($cid !== '') $used[$cid] = true;
                }
            }

            $avail = [];
            foreach ($pool as $id) {
                if (!isset($used[$id])) $avail[] = $id;
            }

            if ($avail !== []) {
                shuffle($avail);
                $availableByType[$type] = $avail;
            }
        }

        $types = array_values(array_keys($availableByType));
        if ($types === []) return $pairs;

        $idx = 0;
        $attempts = 0;
        $attemptLimit = max(256, $needTotal * 24);

        while ($needTotal > 0 && $remaining > 0 && $attempts < $attemptLimit) {
            $attempts++;

            $type = $types[$idx % count($types)] ?? null;
            $idx++;

            if (!$type) break;

            $list = $availableByType[$type] ?? [];
            if ($list === []) {
                unset($availableByType[$type]);
                $types = array_values(array_keys($availableByType));
                if ($types === []) break;
                continue;
            }

            $clientId = array_pop($availableByType[$type]);
            if (!$clientId) continue;

            $pairs[] = ['type' => $type, PJC::COL_CLIENT_ID => $clientId];
            $needTotal--;
            $remaining--;
            $currentTotal++;
        }

        return $pairs;
    }

    private function pairExists(string $clientId, string $type): bool
    {
        try {
            return DB::table(DC::TABLE_CLT_PRM)
                ->where(PJC::COL_CLIENT_ID, $clientId)
                ->where('type', $type)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function existingCountsByType(): array
    {
        try {
            $rows = DB::select('SELECT type, COUNT(*) AS c FROM ' . DC::TABLE_CLT_PRM . ' GROUP BY type');
            $out = [];
            foreach ($rows as $r) {
                $t = is_object($r) ? ($r->type ?? null) : ($r['type'] ?? null);
                $c = is_object($r) ? ($r->c ?? null) : ($r['c'] ?? null);
                if (!is_scalar($t)) continue;
                $ts = trim((string)$t);
                if ($ts === '') continue;
                $out[$ts] = (int)(is_scalar($c) ? $c : 0);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    private function existingClientIdsForType(string $type): array
    {
        $type = trim($type);
        if ($type === '') return [];

        try {
            $rows = DB::select(
                'SELECT ' . PJC::COL_CLIENT_ID . ' AS cid FROM ' . DC::TABLE_CLT_PRM . ' WHERE type = ?',
                [$type]
            );

            $out = [];
            foreach ($rows as $r) {
                $v = is_object($r) ? ($r->cid ?? null) : ($r['cid'] ?? null);
                if (!is_scalar($v)) continue;
                $s = trim((string)$v);
                if ($s !== '') $out[$s] = true;
            }
            return array_values(array_keys($out));
        } catch (\Throwable) {
            return [];
        }
    }

    private function readUserIdsByUserType(string $userTypeValue): array
    {
        $userTypeValue = trim((string)$userTypeValue);
        if ($userTypeValue === '') return [];

        $typeCol = Schema::hasColumn(DC::TABLE_USERS, UC::COL_U_TP) ? UC::COL_U_TP : (Schema::hasColumn(DC::TABLE_USERS, 'type') ? 'type' : null);
        if ($typeCol === null) return [];

        try {
            $sql = 'SELECT id FROM ' . DC::TABLE_USERS . ' WHERE ' . $typeCol . ' = ?';
            $rows = DB::select($sql, [$userTypeValue]);

            $out = [];
            foreach ($rows as $r) {
                $id = is_object($r) ? ($r->id ?? null) : ($r['id'] ?? null);
                if (!is_scalar($id)) continue;
                $s = trim((string)$id);
                if ($s !== '') $out[$s] = true;
            }

            return array_values(array_keys($out));
        } catch (\Throwable $e) {
            Log::debug(static::class . ' readUserIdsByUserType failed', [
                'error' => $e->getMessage(),
                'user_type' => $userTypeValue,
            ]);
            return [];
        }
    }

    private function fetchIdsSafe(string $table, ?int $limit = 2048): array
    {
        $table = trim((string)$table);
        if ($table === '') return [];
        if (!$this->hasTableSafe($table) || !$this->hasColumnSafe($table, 'id')) return [];

        try {
            $sql = 'SELECT id FROM ' . $table;
            if (is_int($limit) && $limit > 0) $sql .= ' LIMIT ' . $limit;

            $rows = DB::select($sql);

            $out = [];
            foreach ($rows as $r) {
                $id = is_object($r) ? ($r->id ?? null) : ($r['id'] ?? null);
                if (!is_scalar($id)) continue;
                $s = trim((string)$id);
                if ($s !== '') $out[$s] = true;
            }

            return array_values(array_keys($out));
        } catch (\Throwable) {
            return [];
        }
    }

    private function pickRandomId(array $ids): ?string
    {
        $ids = array_values(array_filter($ids, fn($v) => is_string($v) && trim($v) !== ''));
        if (!$ids) return null;
        return $ids[random_int(0, count($ids) - 1)] ?? null;
    }

    private function countRowsSafe(string $table): int
    {
        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function normalizeIdList(array $list): array
    {
        $out = [];
        foreach ($list as $v) {
            if (!is_scalar($v)) continue;
            $s = trim((string)$v);
            if ($s === '') continue;
            $out[$s] = true;
        }
        return array_values(array_keys($out));
    }

    private function pickPermissionsTextNonEmpty(array $pool): string
    {
        $text = $this->pickPermissionsText($pool);
        $text = is_string($text) ? trim($text) : '';

        if ($text !== '') return $text;

        $ids = $pool['ids'] ?? [];
        if (is_array($ids) && $ids) return (string)$ids[random_int(0, count($ids) - 1)];

        $names = $pool['names'] ?? [];
        if (is_array($names) && $names) return (string)$names[random_int(0, count($names) - 1)];

        return 'read';
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
            $sid = trim((string)$id);
            $sname = trim((string)$name);
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
            $s = trim((string)$v);
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
                $s = trim((string)$name);
                if ($s === '') continue;
                $set[mb_strtolower($s)] = true;
            }
        }

        return $set;
    }

    private function hasTableSafe(string $table): bool
    {
        try {
            return $table !== '' && Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function hasColumnSafe(string $table, string $column): bool
    {
        try {
            return $table !== '' && Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
