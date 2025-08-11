<?php

namespace App\Traits;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

trait InvoiceConnected
{
  public static function bootInvoiceConnected()
  {
    static::creating(function ($model) {
      $model->mergeFillable(['invoice_id']);
    });
    static::updating(function ($model) {
      $model->mergeFillable(['invoice_id']);
    });
  }
  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }
  public function getInvoiceById(string $invoiceId): ?Invoice
  {
    try {
      $inv = Invoice::find($invoiceId);
      if (!$inv) {
        Log::warning(sprintf('No Invoice found with ID %s in %s', $invoiceId, static::class));
        return null;
      }
      return $inv;
    } catch (\Throwable $e) {
      Log::error(sprintf('Error fetching Invoice by ID %s: %s', $invoiceId, $e->getMessage()), ['exception' => $e]);
      return null;
    }
  }
}
