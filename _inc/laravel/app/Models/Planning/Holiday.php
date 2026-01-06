<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Traits\{HasAuditFields, UsesCountryRegions, StoresManyRefJson, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use StoresManyRefJson;
    use UsesCountryRegions;

    protected $table = DC::TABLE_HLD;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'date',
        PJC::COL_E_DT,
        'occasion',
        'type',
        'observance',
        'recurring',
        'event',
        'award',
        'coupon',
        'project',
        'task',
        'meeting',
        'goal',
        PJC::COL_RDC_SHFT_BY,
        'countries',
        'states',
        'designations',
        'departments',
        'branches',
        'companies',
        'vendors',
        'customers',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'date' => 'date',
        PJC::COL_E_DT => 'date',
        'recurring' => 'boolean',
        'countries' => 'array',
        'states' => 'array',
        'designations' => 'array',
        'departments' => 'array',
        'branches' => 'array',
        'companies' => 'array',
        'vendors' => 'array',
        'customers' => 'array',
        PJC::COL_RDC_SHFT_BY => 'array',
    ];

    protected $appends = [
        'countries_constraint',
        'states_constraint',
        'scope_summary',
        'effective_companies',
        'effective_branches',
        'effective_departments',
        'effective_vendors',
        'effective_customers',
    ];

    protected $with = [
        'createdBy',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $m): void {
            $m->enforceGeoScopeOnPersistedLists();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event', 'id');
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class, 'award', 'id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task', 'id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'meeting', 'id');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal', 'id');
    }

    public function getScopeSummaryAttribute(): array
    {
        $data = $this->computeEffectiveScope();
        return [
            'has_countries' => $data['has_countries'],
            'has_states' => $data['has_states'],
            'allowed_countries' => $data['allowed_countries'],
            'allowed_states' => $data['allowed_states'],
            'counts' => [
                'companies' => count($data['effective']['companies'] ?? []),
                'branches' => count($data['effective']['branches'] ?? []),
                'departments' => count($data['effective']['departments'] ?? []),
                'vendors' => count($data['effective']['vendors'] ?? []),
                'customers' => count($data['effective']['customers'] ?? []),
            ],
        ];
    }

    public function getEffectiveCompaniesAttribute(): ?array
    {
        return $this->computeEffectiveScope()['effective']['companies'] ?? null;
    }

    public function getEffectiveBranchesAttribute(): ?array
    {
        return $this->computeEffectiveScope()['effective']['branches'] ?? null;
    }

    public function getEffectiveDepartmentsAttribute(): ?array
    {
        return $this->computeEffectiveScope()['effective']['departments'] ?? null;
    }

    public function getEffectiveVendorsAttribute(): ?array
    {
        return $this->computeEffectiveScope()['effective']['vendors'] ?? null;
    }

    public function getEffectiveCustomersAttribute(): ?array
    {
        return $this->computeEffectiveScope()['effective']['customers'] ?? null;
    }
}
