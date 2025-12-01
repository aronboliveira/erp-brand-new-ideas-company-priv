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
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model
};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;

    protected $table = DC::TABLE_EVENTS;

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
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $event): void {
            $event->syncParticipantsFromAttributes();
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

        if (!$date) {
            return null;
        }

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

    public function getSponsorsAttribute(): array
    {
        $sponsors = $this->sponsors;
        return is_array($sponsors) ? $sponsors : [];
    }

    public function getSponsorsCountAttribute(): int
    {
        $sponsors = $this->sponsors;
        return is_array($sponsors) ? count($sponsors) : 0;
    }

    public function setSponsorsAttribute(?array $value): void
    {
        $this->attributes['sponsors'] = $value === null ? null : json_encode($value);
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


    private function syncParticipantsFromAttributes(): void
    {
        $participants = $this->participants;
        if (!is_array($participants))
            $participants = [];
        $organizers = $this->organizers;
        if (is_array($organizers))
            foreach ($organizers as $organizer)
                if (is_array($organizer))
                    $participants = $this->addParticipantIfMissing($participants, $organizer);
        $confirmed = $this->confirmed;
        if (is_array($confirmed))
            foreach ($confirmed as $attendee)
                if (is_array($attendee))
                    $participants = $this->addParticipantIfMissing($participants, $attendee);
        // host (employee_id)
        $employeeId = $this->{UC::COL_EMP_ID} ?? null;
        if ($employeeId) {
            $host = [
                'id'     => (string) $employeeId,
                'type'   => 'employee',
                'source' => 'host',
            ];
            $participants = $this->addParticipantIfMissing($participants, $host);
        }

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

            $responsible = \array_filter(
                $responsible,
                static fn($v) => $v !== null && $v !== ''
            );

            if ($responsible !== [])
                $participants = $this->addParticipantIfMissing($participants, $responsible);
        }

        $this->participants = \array_values($participants);
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
                \mb_strtolower((string) $participant['name']) === \mb_strtolower((string) $candidateName)
            )
                return true;
        }
        return false;
    }
}
