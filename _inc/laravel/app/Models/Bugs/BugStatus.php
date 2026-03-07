<?php

namespace App\Models;

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC
};
use App\Services\BugReportService;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model};

/**
 * @property string|null $title
 * @property string|null $description
 * @property int|null $order
 */
class BugStatus extends Model
{
  use UsesUuids, HasAuditFields;

  protected $table = DC::TABLE_BG_STT;

  protected $fillable = [
    AC::COL_TT,
    'description',
    AC::COL_OD,
  ];

  protected $guarded = [
    'id',
    DC::COL_TABLE_CREATOR,
  ];

  /**
   * Get bugs for this status in a project
   * Pure alias to BugReportService - handles auth internally
   */
  public function bugs(int|string $projectId): Collection
  {
    return app(BugReportService::class)->getBugsForStatus(
      $this->id,
      $projectId
    );
  }

  /**
   * Get bugs assigned to the authenticated user for this status
   * Pure alias to BugReportService - handles auth internally
   */
  public function assignBugs(int|string $projectId): Collection
  {
    return app(BugReportService::class)->getAssignedBugs(
      $this->id,
      $projectId
    );
  }
}
