<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\{ParticipationStatus, ProjectRole, UserType};
use App\Traits\{DefinesDates, HasAuditFields, NormalizesArrays, UsesUuids};
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class ProjectUser extends Model
{
    use UsesUuids, HasAuditFields, NormalizesArrays, DefinesDates;

    protected $table = DC::TABLE_PRJ_USR;

    public const INV_PATTERN = '/^INV-[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}-\d{10,}$/i';

    protected $with = [
        'project',
        'user',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        PJC::COL_PJ_ID,
        UC::COL_USER_ID,

        UC::COL_IA,

        PJC::COL_INV_BY,
        PJC::COL_INV_AT,
        PJC::COL_INV_STT,
        PJC::COL_INV_URL,
        PJC::COL_INV_CD,

        PJC::COL_JND_AT,
        PJC::COL_ACC_AT,
        PJC::COL_ACC_BY,

        PJC::COL_LFT_AT,
        PJC::COL_RMV_BY,

        PJC::COL_IS_TMP,
        PJC::COL_EXP_AT,
        PJC::COL_LST_EDT_AT,

        'role',

        PJC::COL_CAN_WRT_OWN,
        PJC::COL_CAN_WRT_OTH,
        PJC::COL_CAN_RD_OTH,
        PJC::COL_IS_PRJ_LD,

        PJC::COL_HR_PRC,
        PJC::COL_BLB_HRS,

        PJC::COL_ALW_EML_NTF,
        PJC::COL_ALW_PSH_NTF,
        PJC::COL_ALW_MNT_NTF,
        PJC::COL_ALW_STT_UPD_NTF,

        PJC::COL_TTL_HRS,

        'notes',
        'metadata',
        'preferences',

        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        UC::COL_IA => 'boolean',

        PJC::COL_INV_AT => 'datetime',
        PJC::COL_JND_AT => 'datetime',
        PJC::COL_ACC_AT => 'datetime',
        PJC::COL_LFT_AT => 'datetime',
        PJC::COL_EXP_AT => 'datetime',
        PJC::COL_LST_EDT_AT => 'datetime',

        PJC::COL_IS_TMP => 'boolean',
        PJC::COL_CAN_WRT_OWN => 'boolean',
        PJC::COL_CAN_WRT_OTH => 'boolean',
        PJC::COL_CAN_RD_OTH => 'boolean',
        PJC::COL_IS_PRJ_LD => 'boolean',

        PJC::COL_ALW_EML_NTF => 'boolean',
        PJC::COL_ALW_PSH_NTF => 'boolean',
        PJC::COL_ALW_MNT_NTF => 'boolean',
        PJC::COL_ALW_STT_UPD_NTF => 'boolean',

        PJC::COL_HR_PRC => 'decimal:4',
        PJC::COL_BLB_HRS => 'decimal:4',
        PJC::COL_TTL_HRS => 'decimal:4',

        PJC::COL_INV_STT => ParticipationStatus::class,
        'role' => ProjectRole::class,

        'metadata' => 'array',
        'preferences' => 'array',
    ];

    protected $appends = [
        'invite_status_label',
        'role_label',
        'is_active_effective',
        'is_invitation_open',
    ];

    protected static array $userTypeCache = [];
    protected static array $projectLeaderCache = [];

    protected static function booted(): void
    {
        static::saving(static function (self $m): void {
            try {
                $m->normalizeEnums();
                $m->enforceTemporalConsistency();
                $m->enforceActiveFlagRules();
                $m->ensureInviteCode();
                $m->applyLeadershipRules();
                $m->applyPermissionRules();
                $m->autoAcceptIfEligible();
                $m->syncPreferencesAggregate();

                $m->ensureJsonAttributesAreEncoded([
                    'metadata',
                    'preferences',
                ]);
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving hook failed: ' . $e->getMessage(), [
                    'id' => (string) ($m->getAttribute('id') ?? ''),
                    'project_id' => (string) ($m->getAttribute(PJC::COL_PJ_ID) ?? ''),
                    'user_id' => (string) ($m->getAttribute(UC::COL_USER_ID) ?? ''),
                ]);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_INV_BY, 'id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_ACC_BY, 'id');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_RMV_BY, 'id');
    }

    public function scopeForProject(Builder $q, string $projectId): Builder
    {
        return $q->where(PJC::COL_PJ_ID, $projectId);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull(PJC::COL_LFT_AT)->where(UC::COL_IA, true);
    }

    public function scopeInvitationOpen(Builder $q): Builder
    {
        return $q
            ->whereNull(PJC::COL_LFT_AT)
            ->where(function (Builder $w): void {
                $w->whereNull(PJC::COL_EXP_AT)->orWhere(PJC::COL_EXP_AT, '>', CarbonImmutable::now());
            })
            ->whereIn(PJC::COL_INV_STT, [
                ParticipationStatus::Invited->value,
                ParticipationStatus::Pending->value,
                ParticipationStatus::Awaiting->value,
                ParticipationStatus::Unconfirmed->value,
                ParticipationStatus::Maybe->value,
                ParticipationStatus::Tentative->value,
            ]);
    }

    public function getInviteStatusLabelAttribute(): string
    {
        try {
            $st = $this->getAttribute(PJC::COL_INV_STT);
            if ($st instanceof ParticipationStatus)
                return $st->label();

            $norm = ParticipationStatus::normalize(is_scalar($st) ? (string) $st : null);
            return $norm?->label() ?? 'Pending';
        } catch (\Throwable $e) {
            Log::debug(self::class . ' invite_status_label failed: ' . $e->getMessage());
            return 'Pending';
        }
    }

    public function getRoleLabelAttribute(): string
    {
        try {
            $r = $this->getAttribute('role');
            if ($r instanceof ProjectRole)
                return $r->label();

            $norm = ProjectRole::normalize(is_scalar($r) ? (string) $r : null);
            return $norm?->label() ?? 'Member';
        } catch (\Throwable $e) {
            Log::debug(self::class . ' role_label failed: ' . $e->getMessage());
            return 'Member';
        }
    }

    public function getIsActiveEffectiveAttribute(): bool
    {
        try {
            if ($this->getAttribute(PJC::COL_LFT_AT) !== null)
                return false;

            $isTmp = (bool) ($this->getAttribute(PJC::COL_IS_TMP) ?? false);
            if ($isTmp) {
                $exp = $this->getAttribute(PJC::COL_EXP_AT);
                if ($exp !== null && CarbonImmutable::parse($exp)->lte(CarbonImmutable::now()))
                    return false;
            }

            return (bool) ($this->getAttribute(UC::COL_IA) ?? false);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' is_active_effective failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getIsInvitationOpenAttribute(): bool
    {
        try {
            if ($this->getAttribute(PJC::COL_LFT_AT) !== null)
                return false;

            $isTmp = (bool) ($this->getAttribute(PJC::COL_IS_TMP) ?? false);
            if ($isTmp) {
                $exp = $this->getAttribute(PJC::COL_EXP_AT);
                if ($exp !== null && CarbonImmutable::parse($exp)->lte(CarbonImmutable::now()))
                    return false;
            }

            $st = $this->getAttribute(PJC::COL_INV_STT);
            $st = $st instanceof ParticipationStatus
                ? $st
                : ParticipationStatus::normalize(is_scalar($st) ? (string) $st : null);

            if ($st === null)
                return false;

            return $st->isPending();
        } catch (\Throwable $e) {
            Log::debug(self::class . ' is_invitation_open failed: ' . $e->getMessage());
            return false;
        }
    }

    public function accept(?string $byUserId = null): bool
    {
        try {
            $now = CarbonImmutable::now()->toDateTimeString();

            if ($this->getAttribute(PJC::COL_ACC_AT) === null)
                $this->setAttribute(PJC::COL_ACC_AT, $now);

            if ($this->getAttribute(PJC::COL_JND_AT) === null)
                $this->setAttribute(PJC::COL_JND_AT, $now);

            $this->setAttribute(PJC::COL_INV_STT, ParticipationStatus::Accepted->value);

            if (is_string($byUserId) && trim($byUserId) !== '')
                $this->setAttribute(PJC::COL_ACC_BY, $byUserId);

            if ($this->getAttribute(PJC::COL_LFT_AT) === null)
                $this->setAttribute(UC::COL_IA, true);

            return true;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' accept failed: ' . $e->getMessage(), [
                'id' => (string) ($this->getAttribute('id') ?? ''),
            ]);
            return false;
        }
    }

    public function decline(?string $byUserId = null): bool
    {
        try {
            $this->setAttribute(PJC::COL_INV_STT, ParticipationStatus::Declined->value);

            if (is_string($byUserId) && trim($byUserId) !== '')
                $this->setAttribute(PJC::COL_RMV_BY, $byUserId);

            $this->setAttribute(UC::COL_IA, false);
            return true;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' decline failed: ' . $e->getMessage(), [
                'id' => (string) ($this->getAttribute('id') ?? ''),
            ]);
            return false;
        }
    }

    public function leave(?string $byUserId = null): bool
    {
        try {
            $this->setAttribute(PJC::COL_LFT_AT, CarbonImmutable::now()->toDateTimeString());
            $this->setAttribute(UC::COL_IA, false);

            if (is_string($byUserId) && trim($byUserId) !== '')
                $this->setAttribute(PJC::COL_RMV_BY, $byUserId);

            return true;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' leave failed: ' . $e->getMessage(), [
                'id' => (string) ($this->getAttribute('id') ?? ''),
            ]);
            return false;
        }
    }

    private function normalizeEnums(): void
    {
        try {
            $st = $this->getAttribute(PJC::COL_INV_STT);
            if ($st instanceof ParticipationStatus) {
                $this->setAttribute(PJC::COL_INV_STT, $st->value);
            } else {
                $norm = ParticipationStatus::normalize(is_scalar($st) ? (string) $st : null)
                    ?? ParticipationStatus::Pending;
                $this->setAttribute(PJC::COL_INV_STT, $norm->value);
            }

            $role = $this->getAttribute('role');
            if ($role instanceof ProjectRole) {
                $this->setAttribute('role', $role->value);
            } else {
                $norm = ProjectRole::normalize(is_scalar($role) ? (string) $role : null)
                    ?? ProjectRole::Member;
                $this->setAttribute('role', $norm->value);
            }
        } catch (\Throwable $e) {
            Log::debug(self::class . ' normalizeEnums failed: ' . $e->getMessage());
        }
    }

    private function enforceTemporalConsistency(): void
    {
        try {
            $isTmp = (bool) ($this->getAttribute(PJC::COL_IS_TMP) ?? false);
            $expAt = $this->getAttribute(PJC::COL_EXP_AT);

            if (!$isTmp && $expAt !== null)
                $this->setAttribute(PJC::COL_EXP_AT, null);

            if ($isTmp && $expAt === null) {
                $this->setAttribute(PJC::COL_EXP_AT, CarbonImmutable::now()->addDays(7)->toDateTimeString());
                Log::debug(self::class . ' expires_at defaulted for temporary project user', [
                    'project_id' => (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? ''),
                    'user_id' => (string) ($this->getAttribute(UC::COL_USER_ID) ?? ''),
                ]);
            }
        } catch (\Throwable $e) {
            Log::debug(self::class . ' enforceTemporalConsistency failed: ' . $e->getMessage());
        }
    }

    private function enforceActiveFlagRules(): void
    {
        try {
            if ($this->getAttribute(PJC::COL_LFT_AT) !== null) {
                $this->setAttribute(UC::COL_IA, false);
                return;
            }

            $st = $this->getAttribute(PJC::COL_INV_STT);
            $st = $st instanceof ParticipationStatus
                ? $st
                : ParticipationStatus::normalize(is_scalar($st) ? (string) $st : null);

            $shouldBeActive = $st !== null && $st->isPositive();
            if ($shouldBeActive && $this->getAttribute(UC::COL_IA) !== true)
                $this->setAttribute(UC::COL_IA, true);

            if (!$shouldBeActive && $this->getAttribute(PJC::COL_ACC_AT) === null && $this->getAttribute(PJC::COL_JND_AT) === null)
                $this->setAttribute(UC::COL_IA, (bool) ($this->getAttribute(UC::COL_IA) ?? false));
        } catch (\Throwable $e) {
            Log::debug(self::class . ' enforceActiveFlagRules failed: ' . $e->getMessage());
        }
    }

    private function ensureInviteCode(): void
    {
        try {
            $raw = $this->getAttribute(PJC::COL_INV_CD);
            $raw = is_string($raw) ? trim($raw) : '';

            if ($raw !== '' && preg_match(self::INV_PATTERN, $raw) === 1)
                return;

            $attempts = 0;
            $cap = 40;

            do {
                $attempts++;
                $uuid = Str::uuid()->toString();
                $ts = (string) CarbonImmutable::now()->timestamp;
                $code = "INV-{$uuid}-{$ts}";
                $exists = (bool) self::query()->where(PJC::COL_INV_CD, $code)->exists();
            } while ($exists && $attempts < $cap);

            if ($exists) {
                Log::warning(self::class . ' invite_code generation attempts exhausted', [
                    'project_id' => (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? ''),
                    'user_id' => (string) ($this->getAttribute(UC::COL_USER_ID) ?? ''),
                ]);
                return;
            }

            $this->setAttribute(PJC::COL_INV_CD, $code);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' ensureInviteCode failed: ' . $e->getMessage());
        }
    }

    private function applyLeadershipRules(): void
    {
        try {
            $projectId = (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? '');
            if ($projectId === '')
                return;

            $isLeader = (bool) ($this->getAttribute(PJC::COL_IS_PRJ_LD) ?? false);
            if (!$isLeader)
                return;

            $this->setAttribute(PJC::COL_CAN_WRT_OWN, true);
            $this->setAttribute(PJC::COL_CAN_WRT_OTH, true);
            $this->setAttribute(PJC::COL_CAN_RD_OTH, true);

            $id = (string) ($this->getAttribute('id') ?? '');
            if ($id === '')
                return;

            $cap = 10;
            $attempts = 0;

            while ($attempts < $cap) {
                $attempts++;
                $changed = 0;

                try {
                    $changed = DB::table($this->getTable())
                        ->where(PJC::COL_PJ_ID, $projectId)
                        ->where('id', '!=', $id)
                        ->where(PJC::COL_IS_PRJ_LD, true)
                        ->update([PJC::COL_IS_PRJ_LD => false]);
                } catch (\Throwable $e) {
                    Log::warning(self::class . ' leader uniqueness update failed: ' . $e->getMessage(), [
                        'project_id' => $projectId,
                        'id' => $id,
                    ]);
                    return;
                }

                if ($changed >= 0)
                    break;
            }

            self::$projectLeaderCache[$projectId] = $id;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' applyLeadershipRules failed: ' . $e->getMessage());
        }
    }

    private function applyPermissionRules(): void
    {
        try {
            $role = $this->getAttribute('role');
            $role = $role instanceof ProjectRole
                ? $role
                : (ProjectRole::normalize(is_scalar($role) ? (string) $role : null) ?? ProjectRole::Member);

            $inviterId = $this->getAttribute(PJC::COL_INV_BY);
            $inviterType = is_string($inviterId) && trim($inviterId) !== ''
                ? self::getUserTypeCached($inviterId)
                : null;

            $isLeaderRole = in_array($role, [ProjectRole::Owner, ProjectRole::Admin, ProjectRole::Manager], true);

            $isLeaderType = $inviterType !== null && in_array($inviterType, [
                UserType::Company,
                UserType::Vendor,
                UserType::SuperAdmin,
                UserType::Admin,
                UserType::Hr,
                UserType::Accountant,
            ], true);

            $isLeader = $isLeaderRole || $isLeaderType || (bool) ($this->getAttribute(PJC::COL_IS_PRJ_LD) ?? false);

            if ($isLeader) {
                $this->setAttribute(PJC::COL_CAN_WRT_OWN, true);
                $this->setAttribute(PJC::COL_CAN_RD_OTH, true);

                if (in_array($role, [ProjectRole::Owner, ProjectRole::Admin], true) || ($inviterType !== null && in_array($inviterType, [UserType::Company, UserType::SuperAdmin, UserType::Admin], true)))
                    $this->setAttribute(PJC::COL_CAN_WRT_OTH, true);
            }
        } catch (\Throwable $e) {
            Log::debug(self::class . ' applyPermissionRules failed: ' . $e->getMessage());
        }
    }

    private function autoAcceptIfEligible(): void
    {
        try {
            $inviterId = $this->getAttribute(PJC::COL_INV_BY);
            if (!is_string($inviterId) || trim($inviterId) === '')
                return;

            $st = $this->getAttribute(PJC::COL_INV_STT);
            $st = $st instanceof ParticipationStatus
                ? $st
                : ParticipationStatus::normalize(is_scalar($st) ? (string) $st : null);

            if ($st === ParticipationStatus::Accepted)
                return;

            $role = $this->getAttribute('role');
            $role = $role instanceof ProjectRole
                ? $role
                : ProjectRole::normalize(is_scalar($role) ? (string) $role : null);

            $roleEligible = $role !== null && in_array($role, [ProjectRole::Owner, ProjectRole::Admin, ProjectRole::Manager], true);

            $invType = self::getUserTypeCached($inviterId);
            $typeEligible = $invType !== null && in_array($invType, [UserType::SuperAdmin, UserType::Admin, UserType::Company], true);

            if (!$roleEligible && !$typeEligible)
                return;

            $this->accept((string) $inviterId);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' autoAcceptIfEligible failed: ' . $e->getMessage());
        }
    }

    private function syncPreferencesAggregate(): void
    {
        try {
            $pref = self::normalizeArrayField($this->getAttribute('preferences'));

            $pref['columns'] = [
                UC::COL_IA => (bool) ($this->getAttribute(UC::COL_IA) ?? false),

                PJC::COL_INV_STT => (string) ($this->getAttribute(PJC::COL_INV_STT) ?? ParticipationStatus::Pending->value),
                'role' => (string) ($this->getAttribute('role') ?? ProjectRole::Member->value),

                PJC::COL_CAN_WRT_OWN => (bool) ($this->getAttribute(PJC::COL_CAN_WRT_OWN) ?? true),
                PJC::COL_CAN_WRT_OTH => (bool) ($this->getAttribute(PJC::COL_CAN_WRT_OTH) ?? false),
                PJC::COL_CAN_RD_OTH => (bool) ($this->getAttribute(PJC::COL_CAN_RD_OTH) ?? false),
                PJC::COL_IS_PRJ_LD => (bool) ($this->getAttribute(PJC::COL_IS_PRJ_LD) ?? false),

                PJC::COL_IS_TMP => (bool) ($this->getAttribute(PJC::COL_IS_TMP) ?? false),
                PJC::COL_EXP_AT => $this->getAttribute(PJC::COL_EXP_AT),

                PJC::COL_HR_PRC => $this->getAttribute(PJC::COL_HR_PRC),
                PJC::COL_BLB_HRS => $this->getAttribute(PJC::COL_BLB_HRS),
                PJC::COL_TTL_HRS => $this->getAttribute(PJC::COL_TTL_HRS),

                PJC::COL_ALW_EML_NTF => (bool) ($this->getAttribute(PJC::COL_ALW_EML_NTF) ?? true),
                PJC::COL_ALW_PSH_NTF => (bool) ($this->getAttribute(PJC::COL_ALW_PSH_NTF) ?? true),
                PJC::COL_ALW_MNT_NTF => (bool) ($this->getAttribute(PJC::COL_ALW_MNT_NTF) ?? true),
                PJC::COL_ALW_STT_UPD_NTF => (bool) ($this->getAttribute(PJC::COL_ALW_STT_UPD_NTF) ?? true),
            ];

            $this->setAttribute('preferences', $pref);
        } catch (\Throwable $e) {
            Log::debug(self::class . ' syncPreferencesAggregate failed: ' . $e->getMessage());
        }
    }

    private static function getUserTypeCached(string $userId): ?UserType
    {
        $userId = trim($userId);
        if ($userId === '')
            return null;

        if (isset(self::$userTypeCache[$userId]) && self::$userTypeCache[$userId] instanceof UserType)
            return self::$userTypeCache[$userId];

        try {
            $typeRaw = DB::table(DC::TABLE_USERS)
                ->where('id', $userId)
                ->value(UC::COL_TP);

            $enum = UserType::normalize(is_string($typeRaw) ? $typeRaw : null);
            self::$userTypeCache[$userId] = $enum;

            return $enum;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' getUserTypeCached failed: ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);
            return null;
        }
    }
}
