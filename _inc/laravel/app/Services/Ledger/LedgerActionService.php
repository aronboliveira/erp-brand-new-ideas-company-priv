<?php

namespace App\Services\Ledger;

use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

// Requires ekmungai/eloquent-ifrs
use IFRS\Models\Transaction;
use IFRS\Models\Account;
use IFRS\Models\LineItem;
use IFRS\Models\Currency;

/**
 * Service to ensure double-entry logging is idempotent, strict, and resilient.
 * Minikube local env testing supported.
 */
class LedgerActionService
{
    /**
     * Record an approved Client Invoice (Debit AR, Credit Revenue)
     * Idempotent by checking transaction reference.
     */
    public function recordClientInvoice(Invoice $invoice, Account $customerAccount, Account $revenueAccount, Currency $currency): Transaction
    {
        return DB::transaction(function () use ($invoice, $customerAccount, $revenueAccount, $currency) {
            $reference = 'INV-' . $invoice->id;

            // Idempotency Check
            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::IN)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Create Transaction header (AR Account)
            $transaction = Transaction::create([
                'account_id' => $customerAccount->id,
                'date' => $invoice->issue_date ?? now(),
                'narration' => 'Initial Posting for Invoice ' . $invoice->invoice_id,
                'currency_id' => $currency->id,
                'transaction_type' => Transaction::IN, // Client Invoice
                'reference' => $reference,
            ]);

            // Line Item (Revenue Account)
            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $revenueAccount->id,
                'amount' => $invoice->getTotal(), // Replace with proper total calc if needed
                'narration' => 'Service/Product delivery for ' . $invoice->invoice_id,
            ]);

            return $transaction;
        });
    }

    /**
     * Record a Supplier Bill (Debit Expense, Credit AP)
     */
    public function recordSupplierBill(Bill $bill, Account $supplierAccount, Account $expenseAccount, Currency $currency): Transaction
    {
        return DB::transaction(function () use ($bill, $supplierAccount, $expenseAccount, $currency) {
            $reference = 'BILL-' . $bill->id;

            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::BL)
                ->first();

            if ($existing) {
                return $existing;
            }

            $transaction = Transaction::create([
                'account_id' => $supplierAccount->id,
                'date' => $bill->bill_date ?? now(),
                'narration' => 'Initial Posting for Bill ' . $bill->bill_id,
                'currency_id' => $currency->id,
                'transaction_type' => Transaction::BL, // Supplier Bill
                'reference' => $reference,
            ]);

            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $expenseAccount->id,
                'amount' => $bill->getTotal(), // Ensure $bill->getTotal() exists
                'narration' => 'Purchase goods/services for ' . $bill->bill_id,
            ]);

            return $transaction;
        });
    }

    /**
     * Record a Payment Received (Debit Bank/Cash, Credit AR)
     */
    public function recordClientReceipt(Payment $payment, Account $bankAccount, Account $customerAccount, Currency $currency): Transaction
    {
        return DB::transaction(function () use ($payment, $bankAccount, $customerAccount, $currency) {
            $reference = 'RCPT-' . $payment->id;

            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::RC)
                ->first();

            if ($existing) {
                return $existing;
            }

            $transaction = Transaction::create([
                'account_id' => $bankAccount->id,
                'date' => $payment->date ?? now(),
                'narration' => 'Payment received: ' . $payment->reference,
                'currency_id' => $currency->id,
                'transaction_type' => Transaction::RC, // Client Receipt
                'reference' => $reference,
            ]);

            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $customerAccount->id,
                'amount' => $payment->amount,
                'narration' => 'Settlement of AR',
            ]);

            return $transaction;
        });
    }

    /**
     * Hard guardrail: Enforce correct accounting periods before any manual entries
     */
    public function assertPeriodIsOpen(\DateTimeInterface $date): void
    {
        // Out of scope for dummy/test logic, but in prod you check against a 'Periods' table
        // to prevent backdating
        $closedDate = env('ACCOUNTING_CLOSE_DATE');
        if ($closedDate && $date < new \DateTime($closedDate)) {
            throw new RuntimeException("Cannot post transaction: The accounting period for {$date->format('Y-m-d')} is closed.");
        }
    }
}