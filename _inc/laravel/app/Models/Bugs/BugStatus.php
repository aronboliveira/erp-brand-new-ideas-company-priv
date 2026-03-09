<?php

namespace App\Models;

use App\Config\Constants\{
  ActivitiesConstants as AC,
  DatabaseConstants as DC
};
use App\Services\{BugReportService};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Support\Facades\{Log};

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BugStatus extends Model
{
  use HasFactory;

  use UsesUuids, HasAuditFields;

  protected $table = DC::TABLE_BG_STT;

  protected $fillable = [
    AC::COL_TT,
    'description',
    AC::COL_OD,
    DC::COL_TABLE_CREATOR,
  ];

  protected $guarded = [
    'id',
  ];

  public function bugs(int|string $projectId): Collection
  {
    try {
      return app(BugReportService::class)->getBugsForStatus(
        $this->id,
        $projectId
      );
    } catch (\Throwable $e) {
      Log::error(static::class . '::bugs — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      return collect();
    }
  }

  public function assignBugs(int|string $projectId): Collection
  {
    try {
      return app(BugReportService::class)->getAssignedBugs(
        $this->id,
        $projectId
      );
    } catch (\Throwable $e) {
      Log::error(static::class . '::assignBugs — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
      return collect();
    }
  }
}
