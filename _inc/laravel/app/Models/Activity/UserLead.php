<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{
    LeadRole,
    UserType
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $role
 */
final class UserLead extends Model
{
    use HasFactory;
    use HasAuditFields;
    use NormalizesArrays;
    use UsesUuids;

    protected $table = DC::TABLE_USR_LD;

    protected $fillable = [
        UC::COL_USER_ID,          // user_id
        PJC::COL_LD_ID,           // lead_id
        'role',
        AC::COL_CAN_MK_DCS,       // can_make_decisions
        'logs',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'role'               => LeadRole::class,
        AC::COL_CAN_MK_DCS   => 'bool',
        'logs'               => 'array',
    ];

    protected $with = [
        'user',
        'lead',
    ];

    protected $appends = [
        'can_act_as_management',
        'log_count',
    ];

    private const FK_LEAD = PJC::COL_LD_ID;
    private const FK_USER = UC::COL_USER_ID;

    /**
     * Boot / saving hooks.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (UserLead $model): void {
            $model->normalizeRole();
            $model->syncDecisionFlagFromRoleAndUser();
            $model->filterLogsByExistingActivity();
            $model->ensureJsonAttributesAreEncoded(['logs']);
        });
    }

    public function getLeadUser(): BelongsTo
    {
        return $this->user();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::FK_USER, 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, self::FK_LEAD, 'id');
    }

    public function getCanActAsManagementAttribute(): bool
    {
        return $this->getEffectiveDecisionCapability();
    }

    public function getLogCountAttribute(): int
    {
        $logs = $this->getAttribute('logs');
        return is_array($logs) ? count($logs) : 0;
    }

    public function getCanMakeDecisionsAttribute($value): bool
    {
        $attrValue = (bool) $value;
        if ($this->getEffectiveDecisionCapability())
            return true;
        return $attrValue;
    }

    private function normalizeRole(): void
    {
        if ($this->role instanceof LeadRole)
            return;
        $raw = $this->getAttribute('role');
        $this->setAttribute('role', LeadRole::normalize(
            is_string($raw) ? $raw : null
        ));
    }

    /**
     * Ajusta AC::COL_CAN_MK_DCS com base em:
     * - role (manager/supervisor);
     * - user_type do usuário (Admin / SuperAdmin).
     */
    private function syncDecisionFlagFromRoleAndUser(): void
    {
        $role = $this->getAttribute('role') instanceof LeadRole
            ? $this->getAttribute('role')
            : LeadRole::normalize(
                is_string($this->getAttribute('role'))
                    ? $this->getAttribute('role')
                    : null
            );
        $can = $role?->isManagement() ?? false;
        if (!$can && $this->getAttribute(self::FK_USER)) {
            $user = $this->relationLoaded('user')
                ? $this->getAttribute('user')
                : $this->user()->first();
            if ($user) {
                $normalizedUserType = UserType::normalize(
                    $user->{UC::COL_U_TP} ?? null
                );
                if ($normalizedUserType && in_array(
                    $normalizedUserType,
                    [UserType::SuperAdmin, UserType::Admin],
                    true
                ))
                    $can = true;
            }
        }
        $this->setAttribute(AC::COL_CAN_MK_DCS, $can);
    }

    private function filterLogsByExistingActivity(): void
    {
        $current = self::normalizeArrayField($this->getAttribute('logs') ?? null);
        if ($current === []) {
            $this->setAttribute('logs', []);
            return;
        }
        $candidateIds = [];
        foreach ($current as $entry) {
            if (!is_array($entry))
                continue;
            $id = $entry['id'] ?? null;
            if (!is_string($id) || $id === '')
                continue;
            $candidateIds[] = $id;
        }
        if ($candidateIds === []) {
            $this->setAttribute('logs', []);
            return;
        }
        $uniqueIds = array_values(array_unique($candidateIds));
        $existingIds = LeadActivityLog::query()
            ->whereIn('id', $uniqueIds)
            ->pluck('id')
            ->all();
        if ($existingIds === []) {
            $this->setAttribute('logs', []);
            return;
        }
        $allowed = array_flip($existingIds);
        $filtered = [];
        foreach ($current as $entry) {
            if (!is_array($entry))
                continue;
            $id = $entry['id'] ?? null;
            if (is_string($id) && isset($allowed[$id]))
                $filtered[] = $entry;
        }
        $this->setAttribute('logs', $filtered);
    }

    /**
     * Capacidade efetiva de decisão, combinando:
     * - role;
     * - user_type;
     * - flag de banco.
     */
    private function getEffectiveDecisionCapability(): bool
    {
        $role = $this->getAttribute('role') instanceof LeadRole
            ? $this->getAttribute('role')
            : LeadRole::normalize(
                is_string($this->getAttribute('role'))
                    ? $this->getAttribute('role')
                    : null
            );
        if ($role && $role->isManagement())
            return true;
        $user = $this->relationLoaded('user')
            ? $this->getAttribute('user')
            : null;
        if (!$user && $this->getAttribute(self::FK_USER))
            $user = $this->user()->first();
        if ($user) {
            $normalizedUserType = UserType::normalize(
                $user->getAttribute(UC::COL_U_TP) ?? null
            );
            if ($normalizedUserType && in_array(
                $normalizedUserType,
                [UserType::SuperAdmin, UserType::Admin],
                true
            ))
                return true;
        }
        return (bool) ($this->getAttribute(AC::COL_CAN_MK_DCS) ?? false);
    }
}
