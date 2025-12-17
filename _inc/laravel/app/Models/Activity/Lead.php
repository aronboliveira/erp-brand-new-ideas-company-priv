<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Traits\{
    HasAuditFields,
    NormalizesAddresses,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany,
    HasMany
};
use Illuminate\Support\Collection;

class Lead extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use NormalizesArrays;
    use UsesUuids;

    protected $table = DC::TABLE_LEADS;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        UC::COL_USER_ID,
        PJC::COL_PPL_ID,
        PJC::COL_STG_ID,
        'sources',
        'products',
        'labels',
        'order',
        'notes',
        PJC::COL_CNV,
        PJC::COL_CRT,
        'date',
        'caller',
        'involved',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'order'          => 'integer',
        PJC::COL_CNV     => 'boolean',
        PJC::COL_CRT     => 'boolean',
        'date'           => 'date',
        'involved'       => 'array',
    ];

    protected $with = [
        'stage',
        'pipeline',
        'user',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (Lead $lead) {
            $attributes = $lead->getAttributes();

            $involved = $lead->normalizeInvolved(
                is_array($lead->getAttribute('involved')) ? $lead->getAttribute('involved') : ($lead->getAttribute('involved') ?? [])
            );

            $userId = $attributes[UC::COL_USER_ID] ?? null;
            $callerId = $attributes['caller'] ?? null;
            $creatorId = $attributes[DC::COL_TABLE_CREATOR] ?? null;

            if ($userId)
                $involved['users'][] = $userId;
            if ($callerId)
                $involved['employees'][] = $callerId;
            if ($creatorId)
                $involved['users'][] = $creatorId;

            $lead->setAttribute('involved', $lead->uniqueInvolved($involved));

            if ($lead->getAttribute('email'))
                $lead->setAttribute('email', self::normalizeEmail($lead->getAttribute('email'), 'Lead email', $lead->getAttribute('id') ?? null) ?: null);
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            if ($lead->getAttribute('phone') && $isNormalizePhoneCallable) {
                $lead->setAttribute('phone', static::normalizePhone(
                    $lead->getAttribute('phone') ?? null,
                    'pos_billing',
                    $lead->getAttribute('id') ?? null
                ));
            }

            $lead->setAttribute('involved', self::normalizeArrayField($lead->getAttribute('involved') ?? null));

            if ($lead->getAttribute(PJC::COL_CNV) === null)
                $lead->setAttribute(PJC::COL_CNV, false);
            if ($lead->getAttribute(PJC::COL_CRT) === null)
                $lead->setAttribute(PJC::COL_CRT, false);
        });
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, PJC::COL_STG_ID);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, PJC::COL_PPL_ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID);
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'caller');
    }

    public function files(): HasMany
    {
        return $this->hasMany(LeadFile::class, PJC::COL_LD_ID);
    }

    public function activities(): HasMany
    {
        return $this
            ->hasMany(LeadActivityLog::class, PJC::COL_LD_ID)
            ->orderByDesc('id');
    }

    public function discussions(): HasMany
    {
        return $this
            ->hasMany(LeadDiscussion::class, PJC::COL_LD_ID)
            ->orderByDesc('id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(LeadCall::class, PJC::COL_LD_ID);
    }

    public function emails(): HasMany
    {
        return $this
            ->hasMany(LeadEmail::class, PJC::COL_LD_ID)
            ->orderByDesc('id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_leads',
            PJC::COL_LD_ID,
            UC::COL_USER_ID
        );
    }

    public function labels(): Collection
    {
        if (!$this->labels) return collect();

        return Label::whereIn('id', explode(',', $this->labels))->get();
    }

    public function products(): Collection
    {
        if (!$this->products) return collect();

        return ProductService::whereIn('id', explode(',', $this->products))->get();
    }

    public function sources(): Collection
    {
        if (!$this->sources) return collect();

        return Source::whereIn('id', explode(',', $this->sources))->get();
    }

    public function involvedUsers(): Collection
    {
        $ids = $this->involved['users'] ?? [];

        if (!$ids) return collect();

        return User::whereIn('id', $ids)->get();
    }

    public function involvedEmployees(): Collection
    {
        $ids = $this->involved['employees'] ?? [];

        if (!$ids) return collect();

        return Employee::whereIn('id', $ids)->get();
    }

    public function isCritical(): bool
    {
        return (bool) $this->{PJC::COL_CRT};
    }

    public function markCritical(): self
    {
        $this->{PJC::COL_CRT} = true;

        $this->save();

        return $this;
    }

    public function unmarkCritical(): self
    {
        $this->{PJC::COL_CRT} = false;

        $this->save();

        return $this;
    }

    public function isConverted(): bool
    {
        return (bool) $this->{PJC::COL_CNV};
    }

    public function scopeCritical($query)
    {
        return $query->where(PJC::COL_CRT, true);
    }

    public function scopeForPipeline($query, string $pipelineId)
    {
        return $query->where(PJC::COL_PPL_ID, $pipelineId);
    }

    public function scopeInvolvedWith($query, string $id)
    {
        return $query
            ->where(function ($q) use ($id) {
                $q->whereJsonContains('involved->users', $id)
                    ->orWhereJsonContains('involved->employees', $id);
            });
    }

    protected function normalizeInvolved($raw): array
    {
        $payload = [
            'users'     => [],
            'employees' => [],
        ];

        if (!is_array($raw)) return $payload;

        if (array_key_exists('users', $raw) || array_key_exists('employees', $raw)) {
            if (isset($raw['users']) && is_array($raw['users']))
                $payload['users'] = array_values(array_filter($raw['users']));

            if (isset($raw['employees']) && is_array($raw['employees']))
                $payload['employees'] = array_values(array_filter($raw['employees']));

            return $payload;
        }

        foreach ($raw as $id)
            if (is_string($id) && $id !== '')
                $payload['users'][] = $id;

        return $payload;
    }

    protected function uniqueInvolved(array $payload): array
    {
        $payload['users'] = array_values(array_unique($payload['users'] ?? []));
        $payload['employees'] = array_values(array_unique($payload['employees'] ?? []));

        return $payload;
    }
}
