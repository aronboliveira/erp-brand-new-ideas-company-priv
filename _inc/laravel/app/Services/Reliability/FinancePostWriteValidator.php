<?php

namespace App\Services\Reliability;

use App\Config\Constants\{BillsConstants as BLC, DatabaseConstants as DC};
use App\Models\{BankAccount, Bill, BillPayment, Invoice, InvoicePayment, OperationLedger};

class FinancePostWriteValidator
{
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'finance.invoice.payment_created' => $this->validateInvoicePaymentCreated($payload, $ledger),
            'finance.invoice.payment_deleted' => $this->validateInvoicePaymentDeleted($payload, $ledger),
            'finance.bill.payment_created' => $this->validateBillPaymentCreated($payload, $ledger),
            'finance.bill.payment_deleted' => $this->validateBillPaymentDeleted($payload, $ledger),
            default => PostWriteValidationResult::pass(
                'finance',
                (string) ($payload['source_table'] ?? 'finance'),
                null,
                isset($payload['id']) ? (string) $payload['id'] : null,
                $payload,
                $this->originEvent($eventType, $payload, $ledger),
            ),
        };
    }

    private function validateInvoicePaymentCreated(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? null);
        $invoiceId = $this->stringOrNull($payload['invoice_id'] ?? null);
        $payment = $paymentId ? InvoicePayment::query()->find($paymentId) : null;
        $invoice = $invoiceId ? Invoice::query()->find($invoiceId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payment?->amount, $errors);
        $this->validateAccount($payload, $payment?->account_id, $errors);

        if (!$payment) {
            $errors['payment'] = 'Invoice payment was not persisted.';
        }
        if (!$invoice) {
            $errors['invoice'] = 'Invoice referenced by the payment was not found.';
        }
        if ($payment && $invoiceId && (string) $payment->invoice_id !== $invoiceId) {
            $errors['invoice_payment_link'] = 'Invoice payment points to a different invoice than the operation payload.';
        }
        if ($invoice) {
            $this->validateInvoicePaymentBalance($invoice, true, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_INV_PAY,
            InvoicePayment::class,
            $paymentId,
            [
                'payload' => $payload,
                'payment' => $payment?->getAttributes(),
                'invoice' => $invoice?->getAttributes(),
            ],
            $this->originEvent('finance.invoice.payment_created', $payload, $ledger),
        );
    }

    private function validateInvoicePaymentDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? null);
        $invoiceId = $this->stringOrNull($payload['invoice_id'] ?? null);
        $payment = $paymentId ? InvoicePayment::query()->find($paymentId) : null;
        $invoice = $invoiceId ? Invoice::query()->find($invoiceId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateAccount($payload, $payload['account_id'] ?? null, $errors);

        if ($payment) {
            $errors['payment_delete'] = 'Invoice payment still exists after the delete operation.';
        }
        if (!$invoice) {
            $errors['invoice'] = 'Invoice referenced by the deleted payment was not found.';
        }
        if ($invoice) {
            $this->validateInvoicePaymentBalance($invoice, false, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_INV_PAY,
            InvoicePayment::class,
            $paymentId,
            [
                'payload' => $payload,
                'payment_exists_after_delete' => (bool) $payment,
                'invoice' => $invoice?->getAttributes(),
            ],
            $this->originEvent('finance.invoice.payment_deleted', $payload, $ledger),
        );
    }

    private function validateBillPaymentCreated(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? null);
        $billId = $this->stringOrNull($payload['bill_id'] ?? null);
        $payment = $paymentId ? BillPayment::query()->find($paymentId) : null;
        $bill = $billId ? Bill::query()->find($billId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payment?->amount, $errors);
        $this->validateAccount($payload, $payment?->account_id, $errors);

        if (!$payment) {
            $errors['payment'] = 'Bill payment was not persisted.';
        }
        if (!$bill) {
            $errors['bill'] = 'Bill referenced by the payment was not found.';
        }
        if ($payment && $billId && (string) $payment->bill_id !== $billId) {
            $errors['bill_payment_link'] = 'Bill payment points to a different bill than the operation payload.';
        }
        if ($bill) {
            $this->validateBillPaymentBalance($bill, true, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_BL_PAY,
            BillPayment::class,
            $paymentId,
            [
                'payload' => $payload,
                'payment' => $payment?->getAttributes(),
                'bill' => $bill?->getAttributes(),
            ],
            $this->originEvent('finance.bill.payment_created', $payload, $ledger),
        );
    }

    private function validateBillPaymentDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? null);
        $billId = $this->stringOrNull($payload['bill_id'] ?? null);
        $payment = $paymentId ? BillPayment::query()->find($paymentId) : null;
        $bill = $billId ? Bill::query()->find($billId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateAccount($payload, $payload['account_id'] ?? null, $errors);

        if ($payment) {
            $errors['payment_delete'] = 'Bill payment still exists after the delete operation.';
        }
        if (!$bill) {
            $errors['bill'] = 'Bill referenced by the deleted payment was not found.';
        }
        if ($bill) {
            $this->validateBillPaymentBalance($bill, false, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_BL_PAY,
            BillPayment::class,
            $paymentId,
            [
                'payload' => $payload,
                'payment_exists_after_delete' => (bool) $payment,
                'bill' => $bill?->getAttributes(),
            ],
            $this->originEvent('finance.bill.payment_deleted', $payload, $ledger),
        );
    }

    private function validatePositiveAmount(array $payload, mixed $actualAmount, array &$errors): void
    {
        $amount = (float) ($actualAmount ?? $payload['amount'] ?? 0);
        if ($amount <= 0.0) {
            $errors['amount'] = 'Finance payment amount must be greater than zero.';
        }
    }

    private function validateAccount(array $payload, mixed $actualAccountId, array &$errors): void
    {
        $accountId = $this->stringOrNull($actualAccountId ?? $payload['account_id'] ?? null);
        if (!$accountId) {
            $errors['account_id'] = 'Finance payment account_id must be populated.';
            return;
        }

        if (!BankAccount::query()->whereKey($accountId)->exists()) {
            $errors['account_id'] = 'Finance payment account_id does not reference a bank account.';
        }
    }

    private function validateInvoicePaymentBalance(Invoice $invoice, bool $created, array &$errors): void
    {
        $total = $invoice->getTotal();
        $paid = (float) InvoicePayment::query()
            ->where(BLC::COL_INV_ID, $invoice->id)
            ->sum('amount');
        $creditNotes = $invoice->invoiceTotalCreditNote();
        $rawDue = $total - $paid - $creditNotes;

        if ($rawDue < -0.01) {
            $errors['invoice_overpaid'] = 'Invoice payments exceed invoice total and credit notes.';
        }

        $status = (int) $invoice->getRawOriginal('status');
        $expectedStatus = $created
            ? ($rawDue <= 0.01 ? 4 : 3)
            : (($rawDue > 0.01 && abs($total - $rawDue) > 0.01) ? 3 : 2);
        if ($status !== $expectedStatus) {
            $errors['invoice_status'] = 'Invoice status does not match the persisted payment balance.';
        }
    }

    private function validateBillPaymentBalance(Bill $bill, bool $created, array &$errors): void
    {
        $total = $bill->getTotal();
        $paid = (float) BillPayment::query()
            ->where(BLC::COL_BL_ID, $bill->id)
            ->sum('amount');
        $debitNotes = $bill->billTotalDebitNote();
        $rawDue = $total - $paid - $debitNotes;

        if ($rawDue < -0.01) {
            $errors['bill_overpaid'] = 'Bill payments exceed bill total and debit notes.';
        }

        $status = (int) $bill->getRawOriginal('status');
        $expectedStatus = $created
            ? ($rawDue <= 0.01 ? 4 : 3)
            : (($rawDue > 0.01 && abs($total - $rawDue) > 0.01) ? 3 : 2);
        if ($status !== $expectedStatus) {
            $errors['bill_status'] = 'Bill status does not match the persisted payment balance.';
        }
    }

    /**
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $snapshot
     * @param array<string, mixed> $originEvent
     */
    private function result(
        array $errors,
        string $sourceTable,
        string $sourceType,
        ?string $sourceRecordId,
        array $snapshot,
        array $originEvent,
    ): PostWriteValidationResult {
        if ($errors === []) {
            return PostWriteValidationResult::pass('finance', $sourceTable, $sourceType, $sourceRecordId, $snapshot, $originEvent);
        }

        return PostWriteValidationResult::fail(
            'finance',
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            $this->criteriaForErrors($errors),
            $errors,
            $snapshot,
            $originEvent,
        );
    }

    /**
     * @param array<string, mixed> $errors
     * @return array<int, string>
     */
    private function criteriaForErrors(array $errors): array
    {
        $criteria = ['C1', 'C2'];

        if (array_key_exists('amount', $errors) || array_key_exists('account_id', $errors)) {
            $criteria[] = 'C7';
        }
        if (array_key_exists('invoice_overpaid', $errors) || array_key_exists('bill_overpaid', $errors)) {
            $criteria[] = 'C4';
        }

        return array_values(array_unique($criteria));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, array $payload, ?OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'payload' => $payload,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
