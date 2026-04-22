<?php

namespace App\Observers\Bills;

use App\Models\Invoice;
use App\Services\Ledger\LedgerActionService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    protected LedgerActionService $ledger;

    public function __construct(LedgerActionService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function updated(Invoice $invoice): void
    {
        // Once an invoice transitions from draft (0) to sent/active (e.g., 2), log it into the ledger
        if ($invoice->isDirty('status') && (int) $invoice->status === 2) {
            Log::info('Invoice status updated. Triggering LedgerActionService payload');
            // $this->ledger->recordClientInvoice($invoice, ...);
            // Note: need mapping to IFRS accounts and currencies before executing this inside the framework.
        }
    }
}
