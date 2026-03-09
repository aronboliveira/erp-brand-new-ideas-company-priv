<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Services\PipelineRequestService;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\{Collection, Model};
/**
 * @property string|null $name
 * @property array|string|null $stages
 */

class Pipeline extends Model
{
    use UsesUuids, HasAuditFields;

    private const CREATED_BY     = DC::COL_TABLE_CREATOR;
    private const ORDER          = AC::COL_OD;
    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        PJC::COL_PPL_NM,
    ];

    /**
     * Get stages for this pipeline
     */
    public function stages(): Collection|RedirectResponse
    {
        return app(PipelineRequestService::class)->getStagesForPipeline($this);
    }

    /**
     * Get lead stages for this pipeline
     */
    public function leadStages(): Collection|RedirectResponse
    {
        return app(PipelineRequestService::class)->getLeadStagesForPipeline($this);
    }
}
