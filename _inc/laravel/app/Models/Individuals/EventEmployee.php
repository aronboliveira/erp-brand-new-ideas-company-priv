<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\EventRole;
use App\Traits\{
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

/**
 * @property string|null $role
 */
class EventEmployee extends Model
{
    use HasAuditFields;
    use UsesUuids;

    protected $table = DC::TABLE_EV_EMP;

    private const FILLABLE_FIELDS = [
        AC::COL_EV_ID,
        UC::COL_EMP_ID,
        'role',
        'metadata',
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = [
        'event',
        'employee',
        'createdBy',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $model): void {
            $model->normalizeRole();
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, AC::COL_EV_ID, 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }


    public function getRoleEnumAttribute(): EventRole
    {
        return EventRole::normalize($this->role);
    }

    public function setRoleAttribute($value): void
    {
        if ($value instanceof EventRole) {
            $this->attributes['role'] = $value->value;
            return;
        }

        $this->attributes['role'] = EventRole::normalize(
            is_string($value) ? $value : (string) $value
        )->value;
    }

    public function isResponsible(): bool
    {
        return $this->role_enum === EventRole::Responsible;
    }

    public function isOrganizer(): bool
    {
        return $this->role_enum === EventRole::Organizer;
    }

    public function isSponsor(): bool
    {
        return $this->role_enum === EventRole::Sponsor;
    }

    public function isSpeaker(): bool
    {
        return $this->role_enum === EventRole::Speaker;
    }

    public function isAttendee(): bool
    {
        return $this->role_enum === EventRole::Attendee;
    }

    public function isVolunteer(): bool
    {
        return $this->role_enum === EventRole::Volunteer;
    }

    public function isOrganizational(): bool
    {
        return $this->role_enum->isOrganizational();
    }

    public function isParticipant(): bool
    {
        return $this->role_enum->isParticipant();
    }

    public function isStaff(): bool
    {
        return $this->role_enum->isStaff();
    }

    public function getEventMappingAttribute(): string
    {
        return $this->role_enum->getEventMapping();
    }

    public function getPriorityAttribute(): int
    {
        return $this->role_enum->getPriority();
    }

    private function normalizeRole(): void
    {
        $role = $this->attributes['role'] ?? null;

        if ($role instanceof EventRole) {
            $this->attributes['role'] = $role->value;
            return;
        }

        if ($role === null) {
            $this->attributes['role'] = EventRole::Attendee->value;
            return;
        }

        $this->attributes['role'] = EventRole::normalize((string) $role)->value;
    }
}
