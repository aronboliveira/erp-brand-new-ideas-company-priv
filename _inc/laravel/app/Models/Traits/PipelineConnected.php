<?php

namespace App\Traits;

use App\Models\Pipeline;
use Illuminate\{Database\Eloquent\Relations\BelongsTo, Support\Facades\Log};

trait PipelineConnected
{
  public static function bootPipelineConnected()
  {
    static::creating(function ($model) {
      $model->mergeFillable(['pipeline_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['pipeline_id']);
    });
  }
  public function pipeline(): BelongsTo
  {
    return $this->belongsTo(Pipeline::class);
  }
  public function getPipelineById(string $pipelineId): ?Pipeline
  {
    try {
      $pl = Pipeline::find($pipelineId);
      if (!$pl) {
        Log::warning(sprintf('No Pipeline found with ID %s in %s', $pipelineId, static::class));
        return null;
      }
      return $pl;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Pipeline by ID %s: %s', $pipelineId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
  public function getPipelineByName(string $name): ?Pipeline
  {
    try {
      $pl = Pipeline::where('name', $name)->first();
      if (!$pl) {
        Log::warning(sprintf('No Pipeline found with name %s in %s', $name, static::class));
        return null;
      }
      return $pl;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Pipeline by name %s: %s', $name, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
