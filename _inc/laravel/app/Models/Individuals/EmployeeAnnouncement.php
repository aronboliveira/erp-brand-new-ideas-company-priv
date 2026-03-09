<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\RecruitmentRole;
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * @property mixed $role
 */

class EmployeeAnnouncement extends Model
{
    use HasAuditFields, UsesUuids;

    protected $table = DC::TABLE_EANC;

    protected $fillable = [
        AC::COL_ANC_ID,
        UC::COL_EMP_ID,
        'role',
        AC::COL_PRT,
        'participation',
        'notes',
        DC::COL_TABLE_CREATOR,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'role'          => RecruitmentRole::class,
        AC::COL_PRT     => 'array',
        'participation' => 'array',
    ];

    protected $with = [
        'announcement',
        'employee',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, AC::COL_ANC_ID);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID);
    }

    public function roleEnum(): ?RecruitmentRole
    {
        if ($this->role instanceof RecruitmentRole) return $this->role;

        if ($this->role === null) return null;

        try {
            return RecruitmentRole::from($this->role);
        } catch (\ValueError) {
            return null;
        }
    }

    public function hasParticipationData(): bool
    {
        return !empty($this->getAttribute(AC::COL_PRT))
            || !empty($this->getAttribute('participation'));
    }
}
