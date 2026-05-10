<?php

namespace App\Services\Ledger;

use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\OperationLedger;
use App\Services\Reliability\CriticalOperationService;
use App\Services\Reliability\ReliabilityPolicy;
use RuntimeException;

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
    private CriticalOperationService $operations;

    public function __construct(?CriticalOperationService $operations = null)
    {
        $this->operations = $operations ?? new CriticalOperationService();
    }

    /**
     * Record an approved Client Invoice (Debit AR, Credit Revenue)
     * Idempotent by checking transaction reference.
     */
    public function recordClientInvoice(Invoice $invoice, Account $customerAccount, Account $revenueAccount, Currency $currency): Transaction
    {
        $reference = 'INV-' . $invoice->id;

        $transaction = $this->operations->run('finance.invoice.record_client_invoice', function (?OperationLedger $ledger) use ($invoice, $customerAccount, $revenueAccount, $currency, $reference) {
            // Idempotency Check
            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::IN)
                ->first();
            $this->operations->recordStep($ledger, 'ledger.idempotency_check', 'Check IFRS transaction reference', [
                'step_type' => 'validation',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 20,
                'payload' => ['reference' => $reference],
                'result' => ['existing' => (bool) $existing],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

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
            $this->operations->recordStep($ledger, 'ledger.transaction_header', 'Create IFRS transaction header', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 30,
                'payload' => ['reference' => $reference, 'transaction_type' => Transaction::IN],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            // Line Item (Revenue Account)
            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $revenueAccount->id,
                'amount' => $invoice->getTotal(), // Replace with proper total calc if needed
                'narration' => 'Service/Product delivery for ' . $invoice->invoice_id,
            ]);
            $this->operations->recordStep($ledger, 'ledger.line_item', 'Create IFRS revenue line item', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 40,
                'payload' => ['account_id' => $revenueAccount->id],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            return $transaction;
        }, $this->financeOperationOptions(
            'finance',
            'Record approved client invoice to IFRS ledger',
            Invoice::class,
            $invoice->id,
            'finance.invoice.recorded',
            $reference,
            ['invoice_id' => (string) $invoice->id, 'reference' => $reference]
        ));

        return $this->assertLedgerTransaction($transaction);
    }

    /**
     * Record a Supplier Bill (Debit Expense, Credit AP)
     */
    public function recordSupplierBill(Bill $bill, Account $supplierAccount, Account $expenseAccount, Currency $currency): Transaction
    {
        $reference = 'BILL-' . $bill->id;

        $transaction = $this->operations->run('finance.bill.record_supplier_bill', function (?OperationLedger $ledger) use ($bill, $supplierAccount, $expenseAccount, $currency, $reference) {
            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::BL)
                ->first();
            $this->operations->recordStep($ledger, 'ledger.idempotency_check', 'Check IFRS transaction reference', [
                'step_type' => 'validation',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 20,
                'payload' => ['reference' => $reference],
                'result' => ['existing' => (bool) $existing],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

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
            $this->operations->recordStep($ledger, 'ledger.transaction_header', 'Create IFRS supplier bill header', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 30,
                'payload' => ['reference' => $reference, 'transaction_type' => Transaction::BL],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $expenseAccount->id,
                'amount' => $bill->getTotal(), // Ensure $bill->getTotal() exists
                'narration' => 'Purchase goods/services for ' . $bill->bill_id,
            ]);
            $this->operations->recordStep($ledger, 'ledger.line_item', 'Create IFRS expense line item', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 40,
                'payload' => ['account_id' => $expenseAccount->id],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            return $transaction;
        }, $this->financeOperationOptions(
            'finance',
            'Record supplier bill to IFRS ledger',
            Bill::class,
            $bill->id,
            'finance.bill.recorded',
            $reference,
            ['bill_id' => (string) $bill->id, 'reference' => $reference]
        ));

        return $this->assertLedgerTransaction($transaction);
    }

    /**
     * Record a Payment Received (Debit Bank/Cash, Credit AR)
     */
    public function recordClientReceipt(Payment $payment, Account $bankAccount, Account $customerAccount, Currency $currency): Transaction
    {
        $reference = 'RCPT-' . $payment->id;

        $transaction = $this->operations->run('finance.payment.record_client_receipt', function (?OperationLedger $ledger) use ($payment, $bankAccount, $customerAccount, $currency, $reference) {
            $existing = Transaction::where('reference', $reference)
                ->where('transaction_type', Transaction::RC)
                ->first();
            $this->operations->recordStep($ledger, 'ledger.idempotency_check', 'Check IFRS transaction reference', [
                'step_type' => 'validation',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 20,
                'payload' => ['reference' => $reference],
                'result' => ['existing' => (bool) $existing],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

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
            $this->operations->recordStep($ledger, 'ledger.transaction_header', 'Create IFRS receipt header', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 30,
                'payload' => ['reference' => $reference, 'transaction_type' => Transaction::RC],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            LineItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $customerAccount->id,
                'amount' => $payment->amount,
                'narration' => 'Settlement of AR',
            ]);
            $this->operations->recordStep($ledger, 'ledger.line_item', 'Create IFRS receivable settlement line item', [
                'step_type' => 'ledger_post',
                'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                'sequence' => 40,
                'payload' => ['account_id' => $customerAccount->id],
                'result' => ['transaction_id' => $transaction->id],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            return $transaction;
        }, $this->financeOperationOptions(
            'finance',
            'Record client receipt to IFRS ledger',
            Payment::class,
            $payment->id,
            'finance.payment.receipt_recorded',
            $reference,
            ['payment_id' => (string) $payment->id, 'reference' => $reference]
        ));

        return $this->assertLedgerTransaction($transaction);
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

    /**
     * @return array<string, mixed>
     */
    private function financeOperationOptions(string $domain, string $summary, string $subjectType, mixed $subjectId, string $eventType, string $reference, array $payload): array
    {
        return [
            'domain' => $domain,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'summary' => $summary,
            'subject_type' => $subjectType,
            'subject_id' => (string) $subjectId,
            'context' => ['reference' => $reference],
            'final_status' => ReliabilityPolicy::STATUS_POSTED_TO_LEDGER,
            'outbox' => [
                'message_key' => $eventType . ':' . $reference,
                'stream' => 'finance.ledger',
                'event_type' => $eventType,
                'aggregate_type' => $subjectType,
                'aggregate_id' => (string) $subjectId,
                'payload' => $payload,
                'metadata' => ['reference' => $reference],
            ],
        ];
    }

    private function assertLedgerTransaction(mixed $transaction): Transaction
    {
        if (!$transaction instanceof Transaction) {
            throw new RuntimeException('Ledger operation did not return an IFRS transaction.');
        }

        return $transaction;
    }
}
