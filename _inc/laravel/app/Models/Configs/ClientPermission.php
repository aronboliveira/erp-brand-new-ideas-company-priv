<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Helpers\ErrorHandler;
use App\Models\Utility;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log, Schema};

use Illuminate\Database\Eloquent\Factories\HasFactory;
class ClientPermission extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_CLT_PRM;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        PJC::COL_CLIENT_ID,
        'type',
        PJC::COL_DL_ID,
        PJC::COL_CTC_ID,
        'permissions',
    ];

    protected $appends = ['permissions_list'];

    private static array $resolvedPermissionsCache = [];
    private static ?array $clientLikePermNamesLowerCache = null;

    protected static function booted(): void
    {
        static::saving(function (Model $m): void {
            if (!$m instanceof self) return;
            try {
                self::normalizeClientIdentity($m);
                self::enforceUniqueComposite($m);
                $m->setAttribute('permissions', self::normalizePermissionsText($m->getAttribute('permissions'), $m->getKey()));
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed normalizing ClientPermission', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'model_id' => $m->getKey(),
                    'table' => $m->getTable(),
                ]);
                throw $e;
            }
        });
    }

    public function client(): ?BelongsTo
    {
        return Utility::getClient($this);
    }

    public function deal(): ?BelongsTo
    {
        return $this->belongsTo(Deal::class, PJC::COL_DL_ID, 'id');
    }

    public function contract(): ?BelongsTo
    {
        return $this->belongsTo(Contract::class, PJC::COL_CTC_ID, 'id');
    }

    public function getPermissionsListAttribute(): array
    {
        $raw = $this->getAttribute('permissions');

        if ($raw === null) return [];

        $tokens = [];

        if (is_array($raw)) {
            foreach ($raw as $v) {
                if (!is_scalar($v)) continue;
                $s = trim((string) $v);
                if ($s === '') continue;
                $tokens[] = $s;
            }
        } else {
            $s = trim((string) $raw);
            if ($s === '') return [];
            $parts = preg_split('/[\r\n,;]+/', $s) ?: [];
            foreach ($parts as $p) {
                $pp = trim((string) $p);
                if ($pp === '') continue;
                $tokens[] = $pp;
            }
        }

        $out = [];
        foreach ($tokens as $t) {
            $k = trim((string) $t);
            if ($k === '') continue;
            $out[$k] = true;
        }

        return array_values(array_keys($out));
    }

    public function resolvePermissions(bool $useCache = true): array
    {
        $id = (string) ($this->getKey() ?? '');
        if ($useCache && $id !== '' && array_key_exists($id, self::$resolvedPermissionsCache))
            return self::$resolvedPermissionsCache[$id];

        $list = $this->getAttribute('permissions_list');
        if (!is_array($list) || $list === []) {
            $out = [];
            if ($id !== '') self::$resolvedPermissionsCache[$id] = $out;
            return $out;
        }

        $allowedLower = self::clientLikePermissionNamesLower();

        if (!Schema::hasTable(DC::TABLE_PERMISSIONS) || !Schema::hasColumn(DC::TABLE_PERMISSIONS, 'id') || !Schema::hasColumn(DC::TABLE_PERMISSIONS, 'name')) {
            $out = [];
            foreach ($list as $v) {
                $sv = trim((string) $v);
                if ($sv === '') continue;
                if (!Utility::looksLikeUuid($sv)) {
                    $lv = mb_strtolower($sv);
                    if (!isset($allowedLower[$lv])) continue;
                }
                $out[] = ['raw' => $sv];
            }
            if ($id !== '') self::$resolvedPermissionsCache[$id] = $out;
            return $out;
        }

        $ids = [];
        foreach ($list as $v) {
            $sv = trim((string) $v);
            if ($sv === '') continue;
            if (Utility::looksLikeUuid($sv)) $ids[$sv] = true;
        }

        if ($ids === []) {
            $out = [];
            if ($id !== '') self::$resolvedPermissionsCache[$id] = $out;
            return $out;
        }

        $out = [];
        try {
            $rows = DB::table(DC::TABLE_PERMISSIONS)
                ->whereIn('id', array_values(array_keys($ids)))
                ->get(['id', 'name'])
                ?->toArray() ?? [];

            foreach ($rows as $r) {
                $rid = (string) ($r->id ?? '');
                $rname = (string) ($r->name ?? '');
                if ($rid === '' || $rname === '') continue;
                $ln = mb_strtolower($rname);
                if (!isset($allowedLower[$ln])) continue;
                $out[] = ['id' => $rid, 'name' => $rname];
            }
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed resolving permissions', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'model_id' => $this->getKey(),
            ]);
        }

        if ($id !== '') self::$resolvedPermissionsCache[$id] = $out;
        return $out;
    }

    private static function normalizeClientIdentity(self $m): void
    {
        $clientId = '';
        $type = '';
        try {
            $clientId = $m->getAttribute(PJC::COL_CLIENT_ID);
            $clientId = is_scalar($clientId) ? trim((string) $clientId) : '';
            if ($clientId === '' || !Utility::looksLikeUuid($clientId))
                throw new \InvalidArgumentException(static::class . ' invalid client_id');

            $type = $m->getAttribute('type');
            $type = is_scalar($type) ? trim(mb_strtolower((string) $type)) : '';
            $allowedTypes = [
                'client' => true,
                'customer' => true,
                'vendor' => true,
                'company' => true,
                'user_client' => true,
                'user_customer' => true,
                'user_vendor' => true,
                'user_company' => true,
            ];
            $userTypeMap = [
                'user_client' => UserType::Client->value,
                'user_customer' => UserType::Customer->value,
                'user_vendor' => UserType::Vendor->value,
                'user_company' => UserType::Company->value,
            ];
            $clientsTable = defined(DC::class . '::TABLE_CLIENTS') ? (string) constant(DC::class . '::TABLE_CLIENTS') : null;
            if ($type === '') {
                $inUsers = false;
                $inClients = false;

                try {
                    $inUsers = Schema::hasTable(DC::TABLE_USERS)
                        && Schema::hasColumn(DC::TABLE_USERS, 'id')
                        && Schema::hasColumn(DC::TABLE_USERS, UC::COL_U_TP)
                        && DB::table(DC::TABLE_USERS)->where('id', $clientId)->whereIn(UC::COL_U_TP, array_values($userTypeMap))->exists();
                } catch (\Throwable) {
                    $inUsers = false;
                }

                try {
                    $inClients = $clientsTable
                        && Schema::hasTable($clientsTable)
                        && Schema::hasColumn($clientsTable, 'id')
                        && DB::table($clientsTable)->where('id', $clientId)->exists();
                } catch (\Throwable) {
                    $inClients = false;
                }

                if ($inUsers) {
                    $ut = DB::table(DC::TABLE_USERS)->where('id', $clientId)->value(UC::COL_U_TP);
                    $ut = is_scalar($ut) ? (string) $ut : '';
                    $type = match ($ut) {
                        UserType::Client->value => 'user_client',
                        UserType::Customer->value => 'user_customer',
                        UserType::Vendor->value => 'user_vendor',
                        UserType::Company->value => 'user_company',
                        default => 'user_client',
                    };
                } elseif ($inClients) {
                    $type = 'client';
                } else {
                    throw new \InvalidArgumentException(static::class . ' client_id not found in users/clients tables');
                }
            }
            if (!isset($allowedTypes[$type]))
                throw new \InvalidArgumentException(static::class . ' invalid polymorphic type');

            if (str_starts_with($type, 'user_')) {
                $expected = $userTypeMap[$type] ?? null;
                if ($expected === null)
                    throw new \InvalidArgumentException(static::class . ' invalid user_* type mapping');
                $userTypeColumn = Schema::hasColumn(DC::TABLE_USERS, 'type') ? 'type' : (Schema::hasColumn(DC::TABLE_USERS, UC::COL_U_TP) ? UC::COL_U_TP : null);
                if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasColumn(DC::TABLE_USERS, 'id') || !$userTypeColumn)
                    throw new \RuntimeException(static::class . ' users table/columns missing for validation');
                $ok = DB::table(DC::TABLE_USERS)->where('id', $clientId)->where($userTypeColumn, $expected)->exists();
                if (!$ok)
                    throw new \InvalidArgumentException(static::class . ' client_id not a valid user for the given type');
            } else {
                if (!$clientsTable || !Schema::hasTable($clientsTable) || !Schema::hasColumn($clientsTable, 'id'))
                    throw new \RuntimeException(static::class . ' clients table missing for validation');
                $ok = DB::table($clientsTable)->where('id', $clientId)->exists();
                if (!$ok)
                    throw new \InvalidArgumentException(static::class . ' client_id not found in clients table');
            }
            $m->setAttribute(PJC::COL_CLIENT_ID, $clientId);
            $m->setAttribute('type', $type);
        } catch (\Throwable $e) {
            /** @var string $clientId */
            /** @var string $type */
            ErrorHandler::evaluateExistenceToLogChannel(
                'client_permission_errors',
                [
                    'message' => 'failed normalizing client identity',
                    'context' => [
                        'message' => $e->getMessage(),
                        'model' => get_class($m),
                        'model_id' => $m->getKey(),
                        'table' => $m->getTable(),
                        'client_id' => $clientId,
                        'type' => $type,
                        'clients_table' => $clientsTable ?? null,
                    ],
                ],
                'debug',
                'error',
                static::class . ' normalization'
            );
        }
    }

    private static function enforceUniqueComposite(self $m): void
    {
        $clientId = '';
        $type = '';
        try {
            $clientId = (string) $m->getAttribute(PJC::COL_CLIENT_ID);
            $type = (string) $m->getAttribute('type');
            if ($clientId === '' || $type === '') return;
            if (!Schema::hasTable(DC::TABLE_CLT_PRM)) return;
            $existingId = null;
            try {
                $existingId = DB::table(DC::TABLE_CLT_PRM)
                    ->where(PJC::COL_CLIENT_ID, $clientId)
                    ->where('type', $type)
                    ->value('id');
            } catch (\Throwable) {
                $existingId = null;
            }
            $existingId = is_scalar($existingId) ? trim((string) $existingId) : '';
            if ($existingId === '') return;
            $currId = is_scalar($m->getKey()) ? trim((string) $m->getKey()) : '';
            if ($currId !== '' && $currId === $existingId) return;
            $m->setAttribute($m->getKeyName(), $existingId);
            $m->exists = true;
        } catch (\Throwable $e) {
            /** @var string $clientId */
            /** @var string $type */
            ErrorHandler::evaluateExistenceToLogChannel(
                'client_permission_errors',
                [
                    'message' => 'failed enforcing unique composite',
                    'context' => [
                        'message' => $e->getMessage(),
                        'model' => get_class($m),
                        'model_id' => $m->getKey(),
                        'table' => $m->getTable(),
                        'client_id' => $clientId,
                        'type' => $type,
                    ],
                ],
            );
        }
    }

    private static function normalizePermissionsText(mixed $raw, string|int|null $modelId = null): string
    {
        $tokens = [];

        if ($raw === null) return '';

        if (is_array($raw)) {
            foreach ($raw as $v) {
                if (!is_scalar($v)) continue;
                $s = trim((string) $v);
                if ($s === '') continue;
                $tokens[] = $s;
            }
        } else {
            $s = trim((string) $raw);
            if ($s === '') return '';
            $parts = preg_split('/[\r\n,;]+/', $s) ?: [];
            foreach ($parts as $p) {
                $pp = trim((string) $p);
                if ($pp === '') continue;
                $tokens[] = $pp;
            }
        }

        $uniq = [];
        foreach ($tokens as $t) {
            $k = trim((string) $t);
            if ($k === '') continue;
            $uniq[$k] = true;
        }
        $tokens = array_values(array_keys($uniq));
        if ($tokens === []) return '';

        $allowedLower = self::clientLikePermissionNamesLower();

        $hasPerms = Schema::hasTable(DC::TABLE_PERMISSIONS)
            && Schema::hasColumn(DC::TABLE_PERMISSIONS, 'id')
            && Schema::hasColumn(DC::TABLE_PERMISSIONS, 'name');

        $outIds = [];
        $outNames = [];

        foreach ($tokens as $tok) {
            $tok = trim((string) $tok);
            if ($tok === '') continue;

            try {
                if ($hasPerms && Utility::looksLikeUuid($tok)) {
                    $row = DB::table(DC::TABLE_PERMISSIONS)->where('id', $tok)->first(['id', 'name']);
                    $rid = $row?->id ? trim((string) $row->id) : '';
                    $rname = $row?->name ? trim((string) $row->name) : '';
                    if ($rid === '' || $rname === '') continue;
                    $ln = mb_strtolower($rname);
                    if (!isset($allowedLower[$ln])) continue;
                    $outIds[$rid] = true;
                    continue;
                }

                $lnTok = mb_strtolower($tok);
                if (!isset($allowedLower[$lnTok])) continue;

                if ($hasPerms) {
                    $row = DB::table(DC::TABLE_PERMISSIONS)
                        ->whereRaw('LOWER(name) = ?', [$lnTok])
                        ->first(['id', 'name']);

                    $rid = $row?->id ? trim((string) $row->id) : '';
                    $rname = $row?->name ? trim((string) $row->name) : '';
                    if ($rid === '' || $rname === '') continue;

                    $ln = mb_strtolower($rname);
                    if (!isset($allowedLower[$ln])) continue;

                    $outIds[$rid] = true;
                } else {
                    $outNames[$tok] = true;
                }
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed normalizing permissions token', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'token' => $tok,
                    'model_id' => $modelId,
                ]);
            }
        }

        if ($hasPerms) return implode("\n", array_values(array_keys($outIds)));
        return implode("\n", array_values(array_keys($outNames)));
    }

    private static function clientLikePermissionNamesLower(): array
    {
        if (self::$clientLikePermNamesLowerCache !== null)
            return self::$clientLikePermNamesLowerCache;

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

        self::$clientLikePermNamesLowerCache = $set;
        return self::$clientLikePermNamesLowerCache;
    }
}
