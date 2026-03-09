<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property bool|null $confirmed
 * @property bool|null $is_internal
 * @property array|string|null $organizers
 * @property int|string|null $responsible_id
 * @property array|string|null $sponsors
 * @property int|null $branch_id
 * @property float|int|null $colorId
 * @property int|null $department_id
 * @property int|null $employee_id
 * @property \Illuminate\Support\Carbon|string|null $endDateTime
 * @property \Illuminate\Support\Carbon|string|null $end_date
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|string|null $startDateTime
 * @property \Illuminate\Support\Carbon|string|null $start_date
 * @property string|null $color
 * @property string|null $title
 * @property string|null $description
 */
class Event extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;
    use NormalizesAddresses;

    protected $table = DC::TABLE_EVENTS;

    private const JSON_FIELDS = [
        'attachments',
        'invited',
        'conditions',
        'reminders',
        'tags',
        'organizers',
        'confirmed',
        'gifts',
        'sponsors',
        'participants',
    ];

    protected $fillable = [
        // planejamento
        'title',
        'date',
        'time',
        CC::COL_DEP_ID,
        PJC::COL_MIN_DR,
        PJC::COL_EXP_DR,
        PJC::COL_MAX_DR,
        'url',
        'location',
        'note',
        CC::COL_IS_INT,
        'attachments',
        'invited',
        'conditions',
        'reminders',
        'tags',

        // vínculos
        CC::COL_CP_ID,
        CC::COL_BRC_ID,
        UC::COL_EMP_ID,

        // responsabilidade e participação
        'responsible',
        AC::COL_RES_ID,
        'organizers',
        'confirmed',
        'gifts',
        'sponsors',
        'participants',

        // visual e conteúdo
        'color',
        'description',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = [
        'company',
        'branch',
        'department',
        'employee',
        'responsibleUser',
        'createdBy',
    ];

    protected $casts = [
        'date'                  => 'date',
        CC::COL_IS_INT         => 'bool',
        'attachments'           => 'array',
        'invited'               => 'array',
        'conditions'            => 'array',
        'reminders'             => 'array',
        'tags'                  => 'array',
        'organizers'            => 'array',
        'confirmed'             => 'array',
        'gifts'                 => 'array',
        'sponsors'              => 'array',
        'participants'          => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $event): void {
            $event->syncParticipantsFromAttributes();
            $ownerId = $event->getAttribute('id') ?? null;
            $participants = self::normalizeArrayField($event->getAttribute('participants') ?? null);
            $participants = $event->normalizeContactArrayRecursive(
                $participants,
                'event.participants',
                $ownerId
            );
            $event->setAttribute('participants', $participants);
            foreach (['organizers', 'confirmed', 'sponsors'] as $field) {
                $value = self::normalizeArrayField($event->getAttribute($field) ?? null);
                if ($value === []) {
                    $event->setAttribute($field, []);
                    continue;
                }
                $event->setAttribute($field, $event->normalizeContactArrayRecursive(
                    $value,
                    'event.' . $field,
                    $ownerId
                ));
            }
            $event->ensureJsonFieldsEncoded();
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, CC::COL_CP_ID, 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CC::COL_BRC_ID, 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CC::COL_DEP_ID, 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_RES_ID, 'id');
    }

    public function getStartAtAttribute(): ?Carbon
    {
        $date = $this->getAttribute('date');

        if (!$date)
            return null;

        $dateString = $date instanceof Carbon
            ? $date->format('Y-m-d')
            : (string) $date;
        $time = (string) ($this->getAttribute('time') ?: '00:00:00');
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $time);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getIsPastAttribute(): bool
    {
        $start = $this->start_at;
        return $start ? $start->isPast() : false;
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) ($this->{PJC::COL_EXP_DR} ?? $this->{PJC::COL_MIN_DR} ?? 0);
    }

    public function getOrganizerCountAttribute(): int
    {
        $organizers = $this->organizers;

        return is_array($organizers) ? count($organizers) : 0;
    }

    public function getSponsorsCountAttribute(): int
    {
        $sponsors = $this->sponsors;

        return is_array($sponsors) ? count($sponsors) : 0;
    }

    public function getConfirmedCountAttribute(): int
    {
        $confirmed = $this->confirmed;

        return is_array($confirmed) ? count($confirmed) : 0;
    }

    public function hasResponsibleUser(): bool
    {
        return (bool) $this->{AC::COL_RES_ID};
    }

    public function isInternal(): bool
    {
        return (bool) $this->{CC::COL_IS_INT};
    }

    public function isExternal(): bool
    {
        return !$this->isInternal();
    }

    public function setAttachmentsAttribute($value): void
    {
        $this->encodeJsonAttribute('attachments', $value);
    }

    public function setInvitedAttribute($value): void
    {
        $this->encodeJsonAttribute('invited', $value);
    }

    public function setConditionsAttribute($value): void
    {
        $this->encodeJsonAttribute('conditions', $value);
    }

    public function setRemindersAttribute($value): void
    {
        $this->encodeJsonAttribute('reminders', $value);
    }

    public function setTagsAttribute($value): void
    {
        $this->encodeJsonAttribute('tags', $value);
    }

    public function setOrganizersAttribute($value): void
    {
        $this->encodeJsonAttribute('organizers', $value);
    }

    public function setConfirmedAttribute($value): void
    {
        $this->encodeJsonAttribute('confirmed', $value);
    }

    public function setGiftsAttribute($value): void
    {
        $this->encodeJsonAttribute('gifts', $value);
    }

    public function setSponsorsAttribute($value): void
    {
        $this->encodeJsonAttribute('sponsors', $value);
    }

    public function setParticipantsAttribute($value): void
    {
        $this->encodeJsonAttribute('participants', $value);
    }

    protected function ensureJsonFieldsEncoded(): void
    {
        foreach (self::JSON_FIELDS as $field) {
            if (!array_key_exists($field, $this->attributes))
                continue;
            $current = $this->attributes[$field] ?? null;
            if (is_array($current) || is_object($current))
                $this->encodeJsonAttribute($field, $current);
            elseif (is_string($current) && $current !== '' && !self::looksLikeJson($current))
                $this->encodeJsonAttribute($field, $current);
        }
    }

    private function syncParticipantsFromAttributes(): void
    {
        $participants = $this->getAttribute('participants');
        if (!is_array($participants))
            $participants = [];
        $organizers = $this->getAttribute('organizers');
        if (is_array($organizers))
            foreach ($organizers as $organizer)
                if (is_array($organizer))
                    $participants = $this->addParticipantIfMissing($participants, $organizer);
        $confirmed = $this->getAttribute('confirmed');
        if (is_array($confirmed))
            foreach ($confirmed as $attendee)
                if (is_array($attendee))
                    $participants = $this->addParticipantIfMissing($participants, $attendee);
        // host (employee_id)
        $employeeId = $this->getAttribute(UC::COL_EMP_ID) ?? null;
        if ($employeeId)
            $participants = $this->addParticipantIfMissing($participants, [
                'id'     => (string) $employeeId,
                'type'   => 'employee',
                'source' => 'host',
            ]);
        // responsável (responsible_id + nome)
        $responsibleId   = $this->{AC::COL_RES_ID} ?? null;
        $responsibleName = $this->getAttribute('responsible');
        if ($responsibleId || $responsibleName) {
            $responsible = [
                'id'     => $responsibleId ? (string) $responsibleId : null,
                'name'   => $responsibleName ?: null,
                'type'   => 'user',
                'source' => 'responsible',
            ];
            $responsible = array_filter(
                $responsible,
                static fn($v) => $v !== null && $v !== ''
            );
            if ($responsible !== [])
                $participants = $this->addParticipantIfMissing($participants, $responsible);
        }
        $this->setAttribute('participants', array_values($participants));
    }

    private function addParticipantIfMissing(array $participants, array $candidate): array
    {
        if ($this->participantExists($participants, $candidate))
            return $participants;
        $participants[] = $candidate;
        return $participants;
    }

    private function participantExists(array $participants, array $candidate): bool
    {
        $candidateId   = $candidate['id']   ?? null;
        $candidateName = $candidate['name'] ?? null;

        foreach ($participants as $participant) {
            if (!is_array($participant))
                continue;
            if (
                $candidateId !== null && isset($participant['id']) &&
                (string) $participant['id'] === (string) $candidateId
            )
                return true;
            if (
                $candidateName !== null && isset($participant['name']) &&
                mb_strtolower((string) $participant['name']) === mb_strtolower((string) $candidateName)
            )
                return true;
        }
        return false;
    }
}
