<?php

namespace App\Traits;

use App\Models\Department;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait DepartmentConnected
{
  public static function bootDepartmentConnected()
  {
    static::creating(function ($model) {
      $model->mergeFillable(['department_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['department_id']);
    });
  }
  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }
  public function getDepartmentById(string $deptId): ?Department
  {
    try {
      $dept = Department::find($deptId);
      if (!$dept) {
        Log::warning(sprintf('No Department found with ID %s in %s', $deptId, static::class));
        return null;
      }
      return $dept;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Department by ID %s: %s', $deptId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
  public function getDepartmentByName(string $name): ?Department
  {
    try {
      $dept = Department::where('name', $name)->first();
      if (!$dept) {
        Log::warning(sprintf('No Department found with name %s in %s', $name, static::class));
        return null;
      }
      return $dept;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Department by name %s: %s', $name, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
