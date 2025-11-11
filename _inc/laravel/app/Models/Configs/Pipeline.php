<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids};
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};

class Pipeline extends Model
{
    use ChecksLogin, UsesUuids, HasAuditFields;

    private const CREATED_BY     = DC::TABLE_CREATOR;
    private const ORDER          = AC::COL_OD;
    private const FILLABLE_FIELDS = [
        'id', //! REMOVE AFTER TESTS
        PJC::COL_PPL_NM,
        self::CREATED_BY //! REMOVE AFTER TESTS
    ];
    // protected $guarded = [ //! UNCOMMENT AFTER TESTS
    //     'id',
    //     DC::TABLE_CREATOR,
    // ];

    protected $fillable = self::FILLABLE_FIELDS;

    public function stages(): HasMany|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $this->hasMany(Stage::class, PJC::COL_PPL_ID, 'id')
            ->where(self::CREATED_BY, '=', $user?->ownerId())
            ->orderBy(self::ORDER);
    }

    public function leadStages(): HasMany|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $this->hasMany(LeadStage::class, PJC::COL_PPL_ID, 'id')
            ->where(self::CREATED_BY, '=', $user?->ownerId())
            ->orderBy(self::ORDER);
    }
}
