<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants, DatabaseConstants};
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class JobStage extends Model
{
    use ChecksLogin, UsesUuids;

    private const FILLABLE_FIELDS = [
        DatabaseConstants::COL_TABLE_CREATOR,
        ActivitiesConstants::COL_OD,
        ActivitiesConstants::COL_TT
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function applications(array $filter): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $query = JobApplication::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
            ->where('is_archive', 0)
            ->where('stage', $this->id)
            ->where('created_at', '>=', $filter['start_date'])
            ->where('created_at', '<=', $filter['end_date']);
        if (!empty($filter['job']))
            $query->where('job', $filter['job']);
        return $query->orderBy(ActivitiesConstants::COL_OD)->get();
        // * consider moving Auth logic out of model
    }
}
