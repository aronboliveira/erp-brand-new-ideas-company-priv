<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};

class Pipeline extends Model
{
    use UsesUuids;

    private const CREATED_BY     = DatabaseConstants::TABLE_CREATOR;
    private const ORDER          = ActivitiesConstants::COL_OD;
    private const FILLABLE_FIELDS = ['id', ProjectsConstants::COL_PPL_NM, self::CREATED_BY];

    protected $fillable = self::FILLABLE_FIELDS;

    public function stages(): HasMany
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $this->hasMany(Stage::class, ProjectsConstants::COL_PPL_ID, 'id')
            ->where(self::CREATED_BY, '=', $user?->ownerId())
            ->orderBy(self::ORDER);
    }

    public function leadStages(): HasMany
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $this->hasMany(LeadStage::class, ProjectsConstants::COL_PPL_ID, 'id')
            ->where(self::CREATED_BY, '=', $user?->ownerId())
            ->orderBy(self::ORDER);
    }
}
