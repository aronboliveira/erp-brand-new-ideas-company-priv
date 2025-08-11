<?php

namespace App\Traits;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait LeadConnected
{
  public static function bootLeadConnected()
  {
    static::creating(function ($model) {
      $model->mergeFillable(['lead_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['lead_id']);
    });
  }
  public function lead(): BelongsTo
  {
    return $this->belongsTo(Lead::class);
  }
  public function getLeadById(string $leadId): ?Lead
  {
    try {
      $ld = Lead::find($leadId);
      if (!$ld) {
        Log::warning(sprintf('No Lead found with ID %s in %s', $leadId, static::class));
        return null;
      }
      return $ld;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Lead by ID %s: %s', $leadId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
  public function getLeadByName(string $name): ?Lead
  {
    try {
      $ld = Lead::where('name', $name)->first();
      if (!$ld) {
        Log::warning(sprintf('No Lead found with name %s in %s', $name, static::class));
        return null;
      }
      return $ld;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Lead by name %s: %s', $name, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
