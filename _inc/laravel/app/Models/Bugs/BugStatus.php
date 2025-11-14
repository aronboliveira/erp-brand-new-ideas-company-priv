<?php

namespace App\Models;

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC,
  PermissionsConstants as PMC,
  ProjectsConstants as PJC,
  UsersConstants as UC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Support\Facades\Auth;

class BugStatus extends Model
{
  use UsesUuids, HasAuditFields;

  protected $fillable = [
    AC::COL_OD,
    AC::COL_TT
  ];
  protected $guarded = [
    'id',
    DC::TABLE_CREATOR,
  ];

  public function bugs(int $projectId): Collection
  {
    // * branches for 'company' and 'client' identical - consider consolidation
    $base = Bug::where(AC::COL_TSK_STT, '=', $this->id)
      ->where(PJC::COL_PJ_ID, '=', $projectId);
    $user = Auth::user();
    return in_array($user->{UC::COL_TP}, [
      PMC::CPN,
      PMC::CL
    ])
      ? $base->orderBy(AC::COL_OD)->get()
      : $base->whereRaw("find_in_set('" . $user->id . "'," . PJC::COL_ASGN . ")")
      ->orderBy(AC::COL_OD)->get();
    // ! ALERT: models should not contain Auth logic
  }

  // ! Alerta de design: lógica com Auth (Auth::user()) dentro do Model acopla domínio à sessão. Preferir Scopes/Services.
  public function assignBugs(int $projectId): Collection
  {
    return Bug::where(AC::COL_TSK_STT, '=', $this->id)
      ->where(PJC::COL_PJ_ID, '=', $projectId)
      ->where(PJC::COL_ASGN, '=', Auth::user()?->id)
      ->orderBy(AC::COL_OD)
      ->get();
    // ! ALERT: models should not contain Auth logic
  }
}
