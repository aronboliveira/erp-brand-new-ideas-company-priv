<?php

namespace App\Traits;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait DealConnected
{
  public static function bootDealConnected(): void
  {
    static::creating(function ($model) {
      $model->mergeFillable(['deal_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['deal_id']);
    });
  }
  public function deal(): BelongsTo
  {
    return $this->belongsTo(Deal::class);
  }
  public function getDealById(string $dealId): ?Deal
  {
    try {
      $deal = Deal::find($dealId);
      if (!$deal) {
        Log::warning(sprintf('No Deal found with ID %s in %s', $dealId, static::class));
        return null;
      }
      return $deal;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Deal by ID %s: %s', $dealId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
  public function getDealByName(string $name): ?Deal
  {
    try {
      $deal = Deal::where('name', $name)->first();
      if (!$deal) {
        Log::warning(sprintf('No Deal found with name %s in %s', $name, static::class));
        return null;
      }
      return $deal;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Deal by name %s: %s', $name, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
