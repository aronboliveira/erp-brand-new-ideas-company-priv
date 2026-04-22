<?php

namespace App\Observers\Bills;

use App\Models\Bill;
use App\Services\Ledger\LedgerActionService;
use Illuminate\Support\Facades\Log;

class BillObserver
{
    protected LedgerActionService $ledger;

    public function __construct(LedgerActionService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function updated(Bill $bill): void
    {
        // Once a bill transitions from draft (0) to sent/active (e.g., 2), log it into the ledger
        if ($bill->isDirty('status') && (int) $bill->status === 2) {
            Log::info('Bill status updated. Triggering LedgerActionService payload');
            // $this->ledger->recordSupplierBill($bill, ...);
            // Note: need mapping to IFRS accounts and currencies before executing this inside the framework.
        }
    }
}
