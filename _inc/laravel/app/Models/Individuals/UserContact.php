<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    UsersConstants as UC
};
use App\Enums\{ContactRole};
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Carbon\{Carbon};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{DB, Log};

class UserContact extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesAddresses;

    protected $table = DC::TABLE_USR_CTT;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        UC::COL_USER_ID,
        EC::COL_PRT_ID,
        'name',
        'company',
        'role',
        'email',
        'phone',
        AC::COL_EM_ID,
        'address',
        AC::COL_SC_MD,
        'notes',
        'avatar',
        'birthday',
        AC::COL_IS_BLK,
        AC::COL_IS_MT,
        AC::COL_IS_FV,
        AC::COL_LST_CT,
        'tags',
        'templates',
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        UC::COL_USER_ID => 'string',
        EC::COL_PRT_ID  => 'string',
        'company'       => 'string',
        'name'          => 'string',
        'role'          => ContactRole::class,
        'email'         => 'string',
        'phone'         => 'string',
        AC::COL_EM_ID   => 'string',
        'address'       => 'string',
        AC::COL_SC_MD   => 'array',
        'notes'         => 'string',
        'avatar'        => 'string',
        'birthday'      => 'date',
        AC::COL_IS_BLK  => 'boolean',
        AC::COL_IS_MT   => 'boolean',
        AC::COL_IS_FV   => 'boolean',
        AC::COL_LST_CT  => 'datetime',
        'tags'          => 'array',
        'templates'     => 'array',
    ];

    protected $with = [
        'user',
        'emailRecord',
    ];

    protected $appends = [
        'role_enum',
        'role_label',
        'role_category',
        'role_icon',
        'role_color',
        'role_priority',
        'display_name',
        'is_business_role',
        'tags_count',
    ];

        private static array $cache = [
        'user_row'     => [],
        'user_type'    => [],
        'employee_row' => [],
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $m->ensureDefaults();
                $m->normalizeAndSyncFromUser();
                $m->normalizeJsonFields();
                $m->enforceRules();
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving failed', [
                    'id'       => (string) ($m->getAttribute('id') ?? ''),
                    'user_id'  => (string) ($m->getAttribute(UC::COL_USER_ID) ?? ''),
                    'owner_id' => (string) ($m->getAttribute(EC::COL_PRT_ID) ?? ''),
                    'error'    => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, EC::COL_PRT_ID, 'id');
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company', 'id');
    }

    public function emailRecord(): BelongsTo
    {
        return $this->belongsTo(Email::class, AC::COL_EM_ID, 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
    }

    public function getRoleEnumAttribute(): ContactRole
    {
        try {
            $raw = $this->getAttribute('role');
            if ($raw instanceof ContactRole) return $raw;

            $v = is_string($raw) ? $raw : null;
            return ContactRole::normalize($v);
        } catch (\Throwable $e) {
            Log::error(static::class . '::getRoleEnumAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return null;
        }
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->getRoleEnumAttribute()->label(DC::DEFAULT_LANG);
    }

    public function getRoleCategoryAttribute(): string
    {
        return $this->getRoleEnumAttribute()->getCategory();
    }

    public function getRoleIconAttribute(): string
    {
        return $this->getRoleEnumAttribute()->getIcon();
    }

    public function getRoleColorAttribute(): string
    {
        return $this->getRoleEnumAttribute()->getColor();
    }

    public function getRolePriorityAttribute(): int
    {
        return $this->getRoleEnumAttribute()->getPriority();
    }

    public function getIsBusinessRoleAttribute(): bool
    {
        return $this->getRoleEnumAttribute()->isBusinessRole();
    }

    public function getTagsCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('tags')));
    }

    public function getDisplayNameAttribute(): string
    {
        try {
            $name = trim((string) ($this->getAttribute('name') ?? ''));
            if ($name !== '') return $name;

            $email = trim((string) ($this->getAttribute('email') ?? ''));
            if ($email !== '') return $email;

            $phone = trim((string) ($this->getAttribute('phone') ?? ''));
            if ($phone !== '') return $phone;

            $uid = (string) ($this->getAttribute(UC::COL_USER_ID) ?? '');
            return $uid !== '' ? mb_substr($uid, 0, 8) : 'contact';
        } catch (\Throwable $e) {
            Log::error(static::class . '::getDisplayNameAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    private function ensureDefaults(): void
    {
        try {
                    $name = $this->getAttribute('name');
            if (!is_string($name) || trim($name) === '')
                $this->setAttribute('name', (string) Str::uuid());

                    $role = $this->getAttribute('role');
            if ($role === null || $role === '')
                $this->setAttribute('role', ContactRole::Other);

                    foreach ([AC::COL_IS_BLK, AC::COL_IS_MT, AC::COL_IS_FV] as $b) {
                $raw = $this->getAttribute($b);
                if ($raw === null) $this->setAttribute($b, false);
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::ensureDefaults — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeAndSyncFromUser(): void
    {
        try {
            $userId  = trim((string) ($this->getAttribute(UC::COL_USER_ID) ?? ''));
            $ownerId = trim((string) ($this->getAttribute(EC::COL_PRT_ID) ?? ''));

            if ($userId !== '' && $ownerId !== '' && $userId === $ownerId)
                throw new \InvalidArgumentException('UserContact: owner (parent_id) must be different from user_id (saved user).');

                    try {
                $rawRole = $this->getAttribute('role');
                $role = $rawRole instanceof ContactRole ? $rawRole : ContactRole::normalize(is_string($rawRole) ? $rawRole : null);
                $this->setAttribute('role', $role);
            } catch (\Throwable $e) {
                Log::warning(self::class . ' failed normalizing role', ['error' => $e->getMessage()]);
                $this->setAttribute('role', ContactRole::Other);
            }

                    $curEmail = $this->getAttribute('email');
            $curPhone = $this->getAttribute('phone');

            $this->setAttribute('email', self::normalizeEmail(is_string($curEmail) ? $curEmail : null, 'user_contact.email', $userId));
            $this->setAttribute('phone', self::normalizePhone(is_string($curPhone) ? $curPhone : null, 'user_contact.phone', $userId, false));

                    if ($userId === '' || !Utility::looksLikeUuid($userId)) return;

            $u = $this->getUserRowCached($userId);
            if (!$u) return;

                    $empId = trim((string) ($u[UC::COL_EMP_ID] ?? ''));
            $emp   = $empId !== '' && Utility::looksLikeUuid($empId) ? $this->getEmployeeRowCached($empId) : null;

            try {
                $dob = $emp ? ($emp['dob'] ?? null) : null;
                if ($dob) {
                    $parsed = $dob instanceof Carbon ? $dob : Carbon::parse((string) $dob);
                    $this->setAttribute('birthday', $parsed->toDateString());
                }
            } catch (\Throwable $e) {
                Log::debug(self::class . ' failed syncing birthday from employee.dob', [
                    'user_id' => $userId,
                    'emp_id'  => $empId,
                    'error'   => $e->getMessage(),
                ]);
            }

                    $email = $this->getAttribute('email');
            if ($email === null || trim((string) $email) === '') {
                $src = $u[UC::COL_EM] ?? null;
                if (!$src && $emp) $src = $emp[UC::COL_EM] ?? ($emp['email'] ?? null);

                $norm = self::normalizeEmail(is_string($src) ? $src : null, 'user_contact.sync.email', $userId);
                if ($norm) $this->setAttribute('email', $norm);
            }

                    $phone = $this->getAttribute('phone');
            if ($phone === null || trim((string) $phone) === '') {
                $src = $u[UC::COL_TEL] ?? ($u['phone'] ?? null);
                if (!$src && $emp) $src = $emp[UC::COL_TEL] ?? ($emp['phone'] ?? null);

                $norm = self::normalizePhone(is_string($src) ? $src : null, 'user_contact.sync.phone', $userId, false);
                if ($norm) $this->setAttribute('phone', $norm);
            }

                    $addr = $this->getAttribute('address');
            if (!is_string($addr) || trim($addr) === '') {
                $src = $u[UC::COL_ADR] ?? ($u['address'] ?? null);
                if (!$src && $emp) $src = $emp[UC::COL_ADR] ?? ($emp['address'] ?? ($emp['branch_location'] ?? null));

                if (is_string($src) && trim($src) !== '')
                    $this->setAttribute('address', trim($src));
            }

                    $type = strtolower(trim((string) ($u['type'] ?? '')));
            if (in_array($type, ['company', 'vendor'], true)) {
                $this->setAttribute('company', $userId);

                $roleEnum = $this->getRoleEnumAttribute();
                if (!$roleEnum->isBusinessRole())
                    $this->setAttribute('role', ContactRole::Other);
            } else {
                $companyId = trim((string) ($this->getAttribute('company') ?? ''));
                if ($companyId !== '' && Utility::looksLikeUuid($companyId)) {
                    $ct = $this->getUserTypeCached($companyId);
                    if (!in_array($ct, ['company', 'vendor'], true))
                        $this->setAttribute('company', null);
                } elseif ($companyId !== '') {
                    $this->setAttribute('company', null);
                }
            }

                    try {
                $lct = $this->getAttribute(AC::COL_LST_CT);
                $uca = $u['created_at'] ?? null;

                if ($lct && $uca) {
                    $lctC = $lct instanceof Carbon ? $lct : Carbon::parse((string) $lct);
                    $ucaC = $uca instanceof Carbon ? $uca : Carbon::parse((string) $uca);

                    if ($lctC->lt($ucaC)) $this->setAttribute(AC::COL_LST_CT, $ucaC);
                }
            } catch (\Throwable $e) {
                Log::debug(self::class . ' failed clamping last_contacted_at', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }

                    $email = $this->getAttribute('email');
            $phone = $this->getAttribute('phone');

            $this->setAttribute('email', self::normalizeEmail(is_string($email) ? $email : null, 'user_contact.email', $userId));
            $this->setAttribute('phone', self::normalizePhone(is_string($phone) ? $phone : null, 'user_contact.phone', $userId, false));

                    if ((bool) ($this->getAttribute(AC::COL_IS_BLK) ?? false))
                $this->setAttribute(AC::COL_IS_FV, false);

                    $this->avoidUniqueCollisionsOrNullify();
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeAndSyncFromUser — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeJsonFields(): void
    {
        try {
                    $tags = self::normalizeArrayField($this->getAttribute('tags'));
            $tags = array_values(array_unique(array_filter(array_map(fn($v) => is_scalar($v) ? trim((string) $v) : '', $tags))));
            $this->setAttribute('tags', array_slice($tags, 0, 256));

                    $tpl = self::normalizeArrayField($this->getAttribute('templates'));
            $tpl = array_values(array_unique(array_filter(array_map(function ($v) {
                if (!is_string($v)) return null;
                $t = trim($v);
                return $t !== '' && Utility::looksLikeUuid($t) ? $t : null;
            }, $tpl))));
            $this->setAttribute('templates', array_slice($tpl, 0, 512));

                    $sm = self::normalizeArrayField($this->getAttribute(AC::COL_SC_MD));
            $this->setAttribute(AC::COL_SC_MD, $this->filterSocialMedia($sm));
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeJsonFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function filterSocialMedia(array $value): array
    {
        try {
            if (!$value) return [];

            $allowedHosts = [
                'facebook.com',
                'instagram.com',
                'linkedin.com',
                'twitter.com',
                'x.com',
                'tiktok.com',
                'youtube.com',
                'youtu.be',
                'whatsapp.com',
                'wa.me',
                'telegram.me',
                't.me',
                'github.com',
            ];

            $out = [];

            foreach ($value as $k => $v) {
                $key = is_string($k) ? self::normalizeContactKey($k) : null;

                            if ($key === null && is_string($v)) {
                    $url = trim($v);
                    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
                    $sch  = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));

                    if ($url !== '' && in_array($sch, ['http', 'https'], true)) {
                        foreach ($allowedHosts as $ah) {
                            if ($host === $ah || str_ends_with($host, '.' . $ah)) {
                                $out[] = $url;
                                break;
                            }
                        }
                    }
                    continue;
                }

                            if ($key !== null && is_string($v)) {
                    $url = trim($v);
                    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
                    $sch  = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));

                    if ($url !== '' && in_array($sch, ['http', 'https'], true)) {
                        foreach ($allowedHosts as $ah) {
                            if ($host === $ah || str_ends_with($host, '.' . $ah)) {
                                $out[$key] = $url;
                                break;
                            }
                        }
                    }
                }
            }

                    if (array_is_list($out))
                return array_slice(array_values(array_unique($out)), 0, 64);

            return array_slice($out, 0, 64, true);
        } catch (\Throwable $e) {
            Log::error(static::class . '::filterSocialMedia — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function enforceRules(): void
    {
        try {
            $userId  = trim((string) ($this->getAttribute(UC::COL_USER_ID) ?? ''));
            $ownerId = trim((string) ($this->getAttribute(EC::COL_PRT_ID) ?? ''));

            if ($userId === '' || !Utility::looksLikeUuid($userId))
                throw new \InvalidArgumentException('UserContact requires a valid user_id (saved user).');

            if ($ownerId !== '' && !Utility::looksLikeUuid($ownerId))
                $this->setAttribute(EC::COL_PRT_ID, null);

            if ($ownerId !== '' && $ownerId === $userId)
                throw new \InvalidArgumentException('UserContact: owner (parent_id) must be different from user_id (saved user).');

            $email = $this->getAttribute('email');
            $phone = $this->getAttribute('phone');

            $hasEmail = is_string($email) && trim($email) !== '';
            $hasPhone = is_string($phone) && trim($phone) !== '';

            if (!$hasEmail && !$hasPhone)
                throw new \InvalidArgumentException('UserContact requires at least one of: email or phone.');

                    $companyId = trim((string) ($this->getAttribute('company') ?? ''));
            if ($companyId !== '' && !Utility::looksLikeUuid($companyId))
                $this->setAttribute('company', null);

            if ($companyId !== '' && Utility::looksLikeUuid($companyId)) {
                $ct = $this->getUserTypeCached($companyId);
                if (!in_array($ct, ['company', 'vendor'], true))
                    $this->setAttribute('company', null);
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::enforceRules — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function avoidUniqueCollisionsOrNullify(): void
    {
        try {
            $id = (string) ($this->getAttribute('id') ?? '');

            $email = $this->getAttribute('email');
            if (is_string($email) && trim($email) !== '') {
                try {
                    $q = self::query()->where('email', trim($email));
                    if ($id !== '') $q->where('id', '!=', $id);

                    if ($q->exists()) {
                        Log::warning(self::class . ' email collision; nullifying', ['email' => $email, 'id' => $id]);
                        $this->setAttribute('email', null);
                    }
                } catch (\Throwable $e) {
                    Log::debug(self::class . ' failed checking email collision', ['error' => $e->getMessage()]);
                }
            }

            $phone = $this->getAttribute('phone');
            if (is_string($phone) && trim($phone) !== '') {
                try {
                    $q = self::query()->where('phone', trim($phone));
                    if ($id !== '') $q->where('id', '!=', $id);

                    if ($q->exists()) {
                        Log::warning(self::class . ' phone collision; nullifying', ['phone' => $phone, 'id' => $id]);
                        $this->setAttribute('phone', null);
                    }
                } catch (\Throwable $e) {
                    Log::debug(self::class . ' failed checking phone collision', ['error' => $e->getMessage()]);
                }
            }

                    $email = $this->getAttribute('email');
            $phone = $this->getAttribute('phone');

            $hasEmail = is_string($email) && trim($email) !== '';
            $hasPhone = is_string($phone) && trim($phone) !== '';

            if (!$hasEmail && !$hasPhone)
                throw new \InvalidArgumentException('UserContact cannot be saved: email and phone were both nullified (unique collisions).');
        } catch (\Throwable $e) {
            Log::error(static::class . '::avoidUniqueCollisionsOrNullify — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }


    private function getUserRowCached(string $userId): ?array
    {
        try {
            $key = trim($userId);
            if ($key === '') return null;

            if (array_key_exists($key, self::$cache['user_row']))
                return self::$cache['user_row'][$key];

            try {
                $row = DB::table(DC::TABLE_USERS)->where('id', $key)->first();
                if (!$row) return self::$cache['user_row'][$key] = null;

                $data = (array) $row;

                            if (!array_key_exists(UC::COL_EM, $data) && array_key_exists('email', $data))
                    $data[UC::COL_EM] = $data['email'];
                if (!array_key_exists(UC::COL_TEL, $data) && array_key_exists('phone', $data))
                    $data[UC::COL_TEL] = $data['phone'];
                if (!array_key_exists(UC::COL_ADR, $data) && array_key_exists('address', $data))
                    $data[UC::COL_ADR] = $data['address'];

                return self::$cache['user_row'][$key] = $data;
            } catch (\Throwable $e) {
                Log::debug(self::class . ' failed fetching user row', ['user_id' => $key, 'error' => $e->getMessage()]);
                return self::$cache['user_row'][$key] = null;
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::getUserRowCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function getEmployeeRowCached(string $empId): ?array
    {
        try {
            $key = trim($empId);
            if ($key === '') return null;

            if (array_key_exists($key, self::$cache['employee_row']))
                return self::$cache['employee_row'][$key];

            try {
                $row = DB::table(DC::TABLE_EMPLOYEES)->where('id', $key)->first();
                return self::$cache['employee_row'][$key] = ($row ? (array) $row : null);
            } catch (\Throwable $e) {
                Log::debug(self::class . ' failed fetching employee row', ['emp_id' => $key, 'error' => $e->getMessage()]);
                return self::$cache['employee_row'][$key] = null;
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::getEmployeeRowCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    private function getUserTypeCached(string $userId): string
    {
        try {
            $key = trim($userId);
            if ($key === '') return '';

            if (array_key_exists($key, self::$cache['user_type']))
                return (string) (self::$cache['user_type'][$key] ?? '');

            try {
                $type = (string) (DB::table(DC::TABLE_USERS)->where('id', $key)->value('type') ?? '');
                $type = strtolower(trim($type));
                return self::$cache['user_type'][$key] = $type;
            } catch (\Throwable $e) {
                Log::debug(self::class . ' failed fetching user type', ['user_id' => $key, 'error' => $e->getMessage()]);
                return self::$cache['user_type'][$key] = '';
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::getUserTypeCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }
}
