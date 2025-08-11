<?php

namespace App\Models;

use App\Config\Constants\{
  ActivitiesConstants,
  DatabaseConstants,
  PermissionsConstants,
  ProjectsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Support\Facades\Auth;

class BugStatus extends Model
{
  use UsesUuids;

  private const FILLABLE_FIELDS = [
    DatabaseConstants::TABLE_CREATOR,
    ActivitiesConstants::COL_OD,
    ActivitiesConstants::COL_TT
  ];
  protected $fillable = self::FILLABLE_FIELDS;

  public function bugs(int $projectId): Collection
  {
    // * branches for 'company' and 'client' identical - consider consolidation
    $base = Bug::where(ActivitiesConstants::COL_TSK_STT, '=', $this->id)
      ->where(ProjectsConstants::COL_PJ_ID, '=', $projectId);
    return in_array(Auth::user()->type, [
      PermissionsConstants::CPN,
      PermissionsConstants::CL
    ])
      ? $base->orderBy(ActivitiesConstants::COL_OD)->get()
      : $base->whereRaw("find_in_set('" . Auth::user()->id . "'," . ProjectsConstants::COL_ASGN . ")")
      ->orderBy(ActivitiesConstants::COL_OD)->get();
    // ! ALERT: models should not contain Auth logic
  }

  public function assignBugs(int $projectId): Collection
  {
    return Bug::where(ActivitiesConstants::COL_TSK_STT, '=', $this->id)
      ->where(ProjectsConstants::COL_PJ_ID, '=', $projectId)
      ->where(ProjectsConstants::COL_ASGN, '=', Auth::user()->id)
      ->orderBy(ActivitiesConstants::COL_OD)
      ->get();
    // ! ALERT: models should not contain Auth logic
  }
}
