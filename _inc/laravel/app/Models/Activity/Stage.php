<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
};
use App\Services\DealRequestService;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\{Http\RedirectResponse, Support\Collection};

class Stage extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $fillable = [
        PJC::COL_STG_NM,
        PJC::COL_PPL_ID,
        DC::COL_TABLE_CREATOR, // ! REMOVER APÓS TESTES
        AC::COL_OD
    ];
    protected $guarded  = [
        'id',
        // DC::COL_TABLE_CREATOR // ! ATIVAR APÓS TESTES
    ];

    public function deals(): Collection|RedirectResponse
    {
        return app(DealRequestService::class)->getDealsForStage($this);
    }
}
