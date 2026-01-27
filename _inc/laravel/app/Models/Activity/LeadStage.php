<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
};
use App\Services\LeadRequestService;
use App\Traits\{
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model, Relations\BelongsTo, Relations\HasMany};
use Illuminate\Http\RedirectResponse;

class LeadStage extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;

    protected $table = DC::TABLE_LEAD_STAGES;

    protected $fillable = [
        PJC::COL_STG_NM,
        PJC::COL_PPL_ID,
        AC::COL_OD,
        'notes',
        PJC::COL_EST_CC,
        PJC::COL_CRT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        AC::COL_OD     => 'integer',
        PJC::COL_EST_CC => 'integer',
        PJC::COL_CRT   => 'boolean',
    ];

    protected $with = [
        'pipeline',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, PJC::COL_PPL_ID);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, PJC::COL_STG_ID);
    }

    public function lead(): Collection|RedirectResponse
    {
        return app(LeadRequestService::class)->getLeadsForStage($this);
    }

    public function isCritical(): bool
    {
        return (bool) $this->getAttribute(PJC::COL_CRT);
    }

    public function hasNotes(): bool
    {
        return (bool) $this->getAttribute('notes');
    }

    public function estimatedCloseChance(): int
    {
        return (int) $this->getAttribute(PJC::COL_EST_CC);
    }
}
