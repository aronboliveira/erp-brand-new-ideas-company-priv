<?php

namespace App\Traits;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait EmployeeConnected
{
  public static function bootEmployeeConnected()
  {
    static::creating(function ($model) {
      $model->mergeFillable(['employee_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['employee_id']);
    });
  }
  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }
  public function getEmployeeById(string $empId): ?Employee
  {
    try {
      $emp = Employee::find($empId);
      if (!$emp) {
        Log::warning(sprintf('No Employee found with ID %s in %s', $empId, static::class));
        return null;
      }
      return $emp;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Employee by ID %s: %s', $empId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
  public function getEmployeeByName(string $name): ?Employee
  {
    try {
      $emp = Employee::where('name', $name)->first();
      if (!$emp) {
        Log::warning(sprintf('No Employee found with name %s in %s', $name, static::class));
        return null;
      }
      return $emp;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Employee by name %s: %s', $name, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
