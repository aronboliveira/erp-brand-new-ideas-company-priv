<?php

namespace App\Services\Reliability;

use App\Config\Constants\{BillsConstants as BLC, DatabaseConstants as DC};
use App\Models\{
    BankAccount,
    BankTransfer,
    Bill,
    BillAccount,
    BillPayment,
    BillProduct,
    CreditNote,
    DebitNote,
    Invoice,
    InvoicePayment,
    JournalEntry,
    JournalItem,
    OperationLedger,
    Payment,
    PurchasePayment,
    Revenue,
    Transaction
};
use Illuminate\Support\Facades\DB;

class FinancePostWriteValidator
{
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'finance.invoice.payment_created' => $this->validateInvoicePaymentCreated($payload, $ledger),
            'finance.invoice.payment_deleted' => $this->validateInvoicePaymentDeleted($payload, $ledger),
            'finance.bill.payment_created' => $this->validateBillPaymentCreated($payload, $ledger),
            'finance.bill.payment_deleted' => $this->validateBillPaymentDeleted($payload, $ledger),
            'finance.revenue.created', 'finance.revenue.updated' => $this->validateRevenuePersisted($eventType, $payload, $ledger),
            'finance.revenue.deleted' => $this->validateRevenueDeleted($payload, $ledger),
            'finance.payment.created', 'finance.payment.updated' => $this->validatePaymentPersisted($eventType, $payload, $ledger),
            'finance.payment.deleted' => $this->validatePaymentDeleted($payload, $ledger),
            'finance.bank_transfer.created', 'finance.bank_transfer.updated' => $this->validateBankTransferPersisted($eventType, $payload, $ledger),
            'finance.bank_transfer.deleted' => $this->validateBankTransferDeleted($payload, $ledger),
            'finance.purchase.payment_created' => $this->validatePurchasePaymentCreated($payload, $ledger),
            'finance.purchase.payment_deleted' => $this->validatePurchasePaymentDeleted($payload, $ledger),
            'finance.expense.created', 'finance.expense.updated' => $this->validateExpensePersisted($eventType, $payload, $ledger),
            'finance.expense.deleted' => $this->validateExpenseDeleted($payload, $ledger),
            'finance.expense.line_deleted' => $this->validateExpenseLineDeleted($payload, $ledger),
            'finance.credit_note.created', 'finance.credit_note.updated' => $this->validateCreditNotePersisted($eventType, $payload, $ledger),
            'finance.credit_note.deleted' => $this->validateCreditNoteDeleted($payload, $ledger),
            'finance.debit_note.created', 'finance.debit_note.updated' => $this->validateDebitNotePersisted($eventType, $payload, $ledger),
            'finance.debit_note.deleted' => $this->validateDebitNoteDeleted($payload, $ledger),
            'finance.journal_entry.created', 'finance.journal_entry.updated' => $this->validateJournalEntryPersisted($eventType, $payload, $ledger),
            'finance.journal_entry.deleted' => $this->validateJournalEntryDeleted($payload, $ledger),
            'finance.journal_item.deleted' => $this->validateJournalItemDeleted($payload, $ledger),
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

    private function validateRevenuePersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $revenueId = $this->stringOrNull($payload['revenue_id'] ?? $payload['id'] ?? null);
        $revenue = $revenueId ? DB::table(DC::TABLE_RVN)->find($revenueId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $revenue->amount ?? null, $errors);
        $this->validateAccount($payload, $revenue->account_id ?? null, $errors);

        if (!$revenue) {
            $errors['revenue'] = 'Revenue row was not persisted.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionPresent($revenueId, 'Revenue', 'Customer', $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_RVN,
            Revenue::class,
            $revenueId,
            ['payload' => $payload, 'revenue' => $this->rowSnapshot($revenue)],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateRevenueDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $revenueId = $this->stringOrNull($payload['revenue_id'] ?? $payload['id'] ?? null);
        $revenue = $revenueId ? DB::table(DC::TABLE_RVN)->find($revenueId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateAccount($payload, $payload['account_id'] ?? null, $errors);
        if ($revenue) {
            $errors['revenue_delete'] = 'Revenue row still exists after the delete operation.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionRemoved($revenueId, 'Revenue', 'Customer', $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_RVN,
            Revenue::class,
            $revenueId,
            ['payload' => $payload, 'revenue_exists_after_delete' => (bool) $revenue],
            $this->originEvent('finance.revenue.deleted', $payload, $ledger),
        );
    }

    private function validatePaymentPersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? $payload['id'] ?? null);
        $payment = $paymentId ? DB::table(DC::TABLE_PAY)->find($paymentId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payment->amount ?? null, $errors);
        $this->validateAccount($payload, $payment->account_id ?? null, $errors);

        if (!$payment) {
            $errors['payment_record'] = 'Payment row was not persisted.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionPresent($paymentId, 'Payment', 'Vendor', $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PAY,
            Payment::class,
            $paymentId,
            ['payload' => $payload, 'payment_record' => $this->rowSnapshot($payment)],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validatePaymentDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? $payload['id'] ?? null);
        $payment = $paymentId ? DB::table(DC::TABLE_PAY)->find($paymentId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateAccount($payload, $payload['account_id'] ?? null, $errors);
        if ($payment) {
            $errors['payment_record_delete'] = 'Payment row still exists after the delete operation.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionRemoved($paymentId, 'Payment', 'Vendor', $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PAY,
            Payment::class,
            $paymentId,
            ['payload' => $payload, 'payment_record_exists_after_delete' => (bool) $payment],
            $this->originEvent('finance.payment.deleted', $payload, $ledger),
        );
    }

    private function validateBankTransferPersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $transferId = $this->stringOrNull($payload['bank_transfer_id'] ?? $payload['transfer_id'] ?? $payload['id'] ?? null);
        $transfer = $transferId ? DB::table(DC::TABLE_BNK_TRF)->find($transferId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $transfer->amount ?? null, $errors);
        $this->validateTransferAccounts(
            $transfer->from_account ?? $payload['from_account'] ?? null,
            $transfer->to_account ?? $payload['to_account'] ?? null,
            $errors,
        );

        if (!$transfer) {
            $errors['bank_transfer'] = 'Bank transfer row was not persisted.';
        }

        return $this->result(
            $errors,
            DC::TABLE_BNK_TRF,
            BankTransfer::class,
            $transferId,
            ['payload' => $payload, 'bank_transfer' => $this->rowSnapshot($transfer)],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateBankTransferDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $transferId = $this->stringOrNull($payload['bank_transfer_id'] ?? $payload['transfer_id'] ?? $payload['id'] ?? null);
        $transfer = $transferId ? DB::table(DC::TABLE_BNK_TRF)->find($transferId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateTransferAccounts($payload['from_account'] ?? null, $payload['to_account'] ?? null, $errors);
        if ($this->rowIsActive($transfer)) {
            $errors['bank_transfer_delete'] = 'Bank transfer row still exists after the delete operation.';
        }

        return $this->result(
            $errors,
            DC::TABLE_BNK_TRF,
            BankTransfer::class,
            $transferId,
            ['payload' => $payload, 'bank_transfer_exists_after_delete' => $this->rowIsActive($transfer)],
            $this->originEvent('finance.bank_transfer.deleted', $payload, $ledger),
        );
    }

    private function validatePurchasePaymentCreated(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['purchase_payment_id'] ?? $payload['payment_id'] ?? $payload['id'] ?? null);
        $purchaseId = $this->stringOrNull($payload['purchase_id'] ?? null);
        $payment = $paymentId ? DB::table(DC::TABLE_PRC_PAY)->find($paymentId) : null;
        $purchase = $purchaseId ? DB::table(DC::TABLE_PURCHASES)->find($purchaseId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payment->amount ?? null, $errors);
        $this->validateAccount($payload, $payment->account_id ?? null, $errors);
        if (!$payment) {
            $errors['purchase_payment'] = 'Purchase payment row was not persisted.';
        }
        if (!$purchase) {
            $errors['purchase'] = 'Purchase referenced by the payment was not found.';
        }
        if ($payment && $purchaseId && (string) ($payment->purchase_id ?? '') !== $purchaseId) {
            $errors['purchase_payment_link'] = 'Purchase payment points to a different purchase than the operation payload.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionPresent($paymentId, 'Partial', 'Vendor', $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PRC_PAY,
            PurchasePayment::class,
            $paymentId,
            ['payload' => $payload, 'purchase_payment' => $this->rowSnapshot($payment), 'purchase' => $this->rowSnapshot($purchase)],
            $this->originEvent('finance.purchase.payment_created', $payload, $ledger),
        );
    }

    private function validatePurchasePaymentDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $paymentId = $this->stringOrNull($payload['purchase_payment_id'] ?? $payload['payment_id'] ?? $payload['id'] ?? null);
        $purchaseId = $this->stringOrNull($payload['purchase_id'] ?? null);
        $payment = $paymentId ? DB::table(DC::TABLE_PRC_PAY)->find($paymentId) : null;
        $purchase = $purchaseId ? DB::table(DC::TABLE_PURCHASES)->find($purchaseId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        $this->validateAccount($payload, $payload['account_id'] ?? null, $errors);
        if ($payment) {
            $errors['purchase_payment_delete'] = 'Purchase payment row still exists after the delete operation.';
        }
        if (!$purchase) {
            $errors['purchase'] = 'Purchase referenced by the deleted payment was not found.';
        }
        if ((bool) ($payload['expects_transaction'] ?? false)) {
            $this->validateTransactionRemoved($paymentId, 'Partial', 'Vendor', $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PRC_PAY,
            PurchasePayment::class,
            $paymentId,
            ['payload' => $payload, 'purchase_payment_exists_after_delete' => (bool) $payment, 'purchase' => $this->rowSnapshot($purchase)],
            $this->originEvent('finance.purchase.payment_deleted', $payload, $ledger),
        );
    }

    private function validateExpensePersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $billId = $this->stringOrNull($payload['expense_id'] ?? $payload['bill_id'] ?? $payload['id'] ?? null);
        $paymentId = $this->stringOrNull($payload['payment_id'] ?? null);
        $bill = $billId ? Bill::query()->find($billId) : null;
        $payment = $paymentId ? BillPayment::query()->find($paymentId) : null;
        $lineCount = $billId ? BillProduct::query()->where(BLC::COL_BL_ID, $billId)->count() : 0;
        $accountLineCount = $billId ? BillAccount::query()->where(BLC::COL_REF_ID, $billId)->count() : 0;
        $errors = [];

        $actualAmount = $payment?->amount ?? $payload['amount'] ?? $payload['total_amount'] ?? $bill?->getTotal();
        $this->validatePositiveAmount($payload, $actualAmount, $errors);

        if ($paymentId || $payment) {
            $this->validateAccount($payload, $payment?->account_id ?? $payload['account_id'] ?? null, $errors);
        }

        if (!$bill) {
            $errors['expense'] = 'Expense bill row was not persisted.';
        } elseif (strtolower((string) $bill->type) !== 'expense') {
            $errors['expense_type'] = 'Expense operation persisted a non-expense bill row.';
        }

        if ($paymentId && !$payment) {
            $errors['payment'] = 'Expense payment row was not persisted.';
        }
        if ($payment && $billId && (string) $payment->bill_id !== $billId) {
            $errors['bill_payment_link'] = 'Expense payment points to a different bill than the operation payload.';
        }
        if ($lineCount + $accountLineCount <= 0) {
            $errors['expense_lines'] = 'Expense has no product or account lines after persistence.';
        }

        return $this->result(
            $errors,
            DC::TABLE_BILLS,
            Bill::class,
            $billId,
            [
                'payload' => $payload,
                'expense' => $bill?->getAttributes(),
                'payment' => $payment?->getAttributes(),
                'line_count' => $lineCount,
                'account_line_count' => $accountLineCount,
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateExpenseDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $billId = $this->stringOrNull($payload['expense_id'] ?? $payload['bill_id'] ?? $payload['id'] ?? null);
        $bill = $billId ? Bill::query()->find($billId) : null;
        $payments = $billId ? BillPayment::query()->where(BLC::COL_BL_ID, $billId)->count() : 0;
        $lines = $billId ? BillProduct::query()->where(BLC::COL_BL_ID, $billId)->count() : 0;
        $accounts = $billId ? BillAccount::query()->where(BLC::COL_REF_ID, $billId)->count() : 0;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? $payload['total_amount'] ?? 0, $errors);
        if ($bill) {
            $errors['expense_delete'] = 'Expense bill row still exists after delete operation.';
        }
        if ($payments > 0) {
            $errors['payment_delete'] = 'Expense payments still reference the deleted expense.';
        }
        if ($lines > 0 || $accounts > 0) {
            $errors['expense_lines_delete'] = 'Expense product/account lines still reference the deleted expense.';
        }

        return $this->result(
            $errors,
            DC::TABLE_BILLS,
            Bill::class,
            $billId,
            [
                'payload' => $payload,
                'expense_exists_after_delete' => (bool) $bill,
                'payments_after_delete' => $payments,
                'lines_after_delete' => $lines,
                'account_lines_after_delete' => $accounts,
            ],
            $this->originEvent('finance.expense.deleted', $payload, $ledger),
        );
    }

    private function validateExpenseLineDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $lineId = $this->stringOrNull($payload['bill_product_id'] ?? $payload['line_id'] ?? $payload['id'] ?? null);
        $billId = $this->stringOrNull($payload['expense_id'] ?? $payload['bill_id'] ?? null);
        $line = $lineId ? BillProduct::query()->find($lineId) : null;
        $bill = $billId ? Bill::query()->find($billId) : null;
        $errors = [];

        if ($line) {
            $errors['expense_line_delete'] = 'Expense line still exists after delete operation.';
        }
        if (!$bill) {
            $errors['expense'] = 'Expense referenced by the deleted line was not found.';
        } elseif (strtolower((string) $bill->type) !== 'expense') {
            $errors['expense_type'] = 'Expense line operation references a non-expense bill row.';
        }

        return $this->result(
            $errors,
            DC::TABLE_BL_PRD,
            BillProduct::class,
            $lineId,
            [
                'payload' => $payload,
                'expense_line_exists_after_delete' => (bool) $line,
                'expense' => $bill?->getAttributes(),
            ],
            $this->originEvent('finance.expense.line_deleted', $payload, $ledger),
        );
    }

    private function validateCreditNotePersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $noteId = $this->stringOrNull($payload['credit_note_id'] ?? $payload['note_id'] ?? $payload['id'] ?? null);
        $invoiceId = $this->stringOrNull($payload['invoice_id'] ?? $payload['invoice'] ?? null);
        $note = $noteId ? DB::table(DC::TABLE_CR_NOTES)->find($noteId) : null;
        $invoiceId = $invoiceId ?? $this->stringOrNull($note->invoice ?? $note->invoice_id ?? null);
        $invoice = $invoiceId ? Invoice::query()->find($invoiceId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $note->amount ?? null, $errors);
        if (!$note) {
            $errors['credit_note'] = 'Credit note row was not persisted.';
        }
        if (!$invoice) {
            $errors['invoice'] = 'Invoice referenced by the credit note was not found.';
        } elseif ($invoice->getDue() < -0.01) {
            $errors['invoice_overpaid'] = 'Credit notes and payments exceed invoice total.';
        }

        return $this->result(
            $errors,
            DC::TABLE_CR_NOTES,
            CreditNote::class,
            $noteId,
            ['payload' => $payload, 'credit_note' => $this->rowSnapshot($note), 'invoice' => $invoice?->getAttributes()],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateCreditNoteDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $noteId = $this->stringOrNull($payload['credit_note_id'] ?? $payload['note_id'] ?? $payload['id'] ?? null);
        $invoiceId = $this->stringOrNull($payload['invoice_id'] ?? $payload['invoice'] ?? null);
        $note = $noteId ? DB::table(DC::TABLE_CR_NOTES)->find($noteId) : null;
        $invoice = $invoiceId ? Invoice::query()->find($invoiceId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        if ($note) {
            $errors['credit_note_delete'] = 'Credit note row still exists after the delete operation.';
        }
        if (!$invoice) {
            $errors['invoice'] = 'Invoice referenced by the deleted credit note was not found.';
        }

        return $this->result(
            $errors,
            DC::TABLE_CR_NOTES,
            CreditNote::class,
            $noteId,
            ['payload' => $payload, 'credit_note_exists_after_delete' => (bool) $note, 'invoice' => $invoice?->getAttributes()],
            $this->originEvent('finance.credit_note.deleted', $payload, $ledger),
        );
    }

    private function validateDebitNotePersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $noteId = $this->stringOrNull($payload['debit_note_id'] ?? $payload['note_id'] ?? $payload['id'] ?? null);
        $billId = $this->stringOrNull($payload['bill_id'] ?? $payload['bill'] ?? null);
        $note = $noteId ? DB::table(DC::TABLE_DB_NOTES)->find($noteId) : null;
        $billId = $billId ?? $this->stringOrNull($note->bill ?? $note->bill_id ?? null);
        $bill = $billId ? Bill::query()->find($billId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $note->amount ?? null, $errors);
        if (!$note) {
            $errors['debit_note'] = 'Debit note row was not persisted.';
        }
        if (!$bill) {
            $errors['bill'] = 'Bill referenced by the debit note was not found.';
        } elseif ($bill->getDue() < -0.01) {
            $errors['bill_overpaid'] = 'Debit notes and payments exceed bill total.';
        }

        return $this->result(
            $errors,
            DC::TABLE_DB_NOTES,
            DebitNote::class,
            $noteId,
            ['payload' => $payload, 'debit_note' => $this->rowSnapshot($note), 'bill' => $bill?->getAttributes()],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateDebitNoteDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $noteId = $this->stringOrNull($payload['debit_note_id'] ?? $payload['note_id'] ?? $payload['id'] ?? null);
        $billId = $this->stringOrNull($payload['bill_id'] ?? $payload['bill'] ?? null);
        $note = $noteId ? DB::table(DC::TABLE_DB_NOTES)->find($noteId) : null;
        $bill = $billId ? Bill::query()->find($billId) : null;
        $errors = [];

        $this->validatePositiveAmount($payload, $payload['amount'] ?? null, $errors);
        if ($note) {
            $errors['debit_note_delete'] = 'Debit note row still exists after the delete operation.';
        }
        if (!$bill) {
            $errors['bill'] = 'Bill referenced by the deleted debit note was not found.';
        }

        return $this->result(
            $errors,
            DC::TABLE_DB_NOTES,
            DebitNote::class,
            $noteId,
            ['payload' => $payload, 'debit_note_exists_after_delete' => (bool) $note, 'bill' => $bill?->getAttributes()],
            $this->originEvent('finance.debit_note.deleted', $payload, $ledger),
        );
    }

    private function validateJournalEntryPersisted(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $journalId = $this->stringOrNull($payload['journal_entry_id'] ?? $payload['journal_id'] ?? $payload['id'] ?? null);
        $journal = $journalId ? DB::table(DC::TABLE_JOURNAL_ENTRIES)->find($journalId) : null;
        $totals = $this->journalItemTotals($journalId);
        $errors = [];

        if (!$journal) {
            $errors['journal_entry'] = 'Journal entry row was not persisted.';
        }
        $this->validateJournalBalance($totals, $errors);

        return $this->result(
            $errors,
            DC::TABLE_JOURNAL_ENTRIES,
            JournalEntry::class,
            $journalId,
            ['payload' => $payload, 'journal_entry' => $this->rowSnapshot($journal), 'item_totals' => $totals],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    private function validateJournalEntryDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $journalId = $this->stringOrNull($payload['journal_entry_id'] ?? $payload['journal_id'] ?? $payload['id'] ?? null);
        $journal = $journalId ? DB::table(DC::TABLE_JOURNAL_ENTRIES)->find($journalId) : null;
        $items = $journalId ? DB::table(DC::TABLE_JRN_IT)->where('journal', $journalId)->whereNull('deleted_at')->count() : 0;
        $errors = [];

        if ($this->rowIsActive($journal)) {
            $errors['journal_entry_delete'] = 'Journal entry row still exists after the delete operation.';
        }
        if ($items > 0) {
            $errors['journal_item_delete'] = 'Journal entry still has items after the delete operation.';
        }

        return $this->result(
            $errors,
            DC::TABLE_JOURNAL_ENTRIES,
            JournalEntry::class,
            $journalId,
            ['payload' => $payload, 'journal_exists_after_delete' => $this->rowIsActive($journal), 'remaining_items' => $items],
            $this->originEvent('finance.journal_entry.deleted', $payload, $ledger),
        );
    }

    private function validateJournalItemDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $itemId = $this->stringOrNull($payload['journal_item_id'] ?? $payload['item_id'] ?? $payload['id'] ?? null);
        $journalId = $this->stringOrNull($payload['journal_entry_id'] ?? $payload['journal_id'] ?? null);
        $item = $itemId ? DB::table(DC::TABLE_JRN_IT)->find($itemId) : null;
        $totals = $this->journalItemTotals($journalId);
        $errors = [];

        if ($this->rowIsActive($item)) {
            $errors['journal_item_delete'] = 'Journal item row still exists after the delete operation.';
        }
        $this->validateJournalBalance($totals, $errors, allowEmpty: true);

        return $this->result(
            $errors,
            DC::TABLE_JRN_IT,
            JournalItem::class,
            $itemId,
            ['payload' => $payload, 'journal_item_exists_after_delete' => $this->rowIsActive($item), 'item_totals' => $totals],
            $this->originEvent('finance.journal_item.deleted', $payload, $ledger),
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

    private function validateTransferAccounts(mixed $fromAccountId, mixed $toAccountId, array &$errors): void
    {
        $from = $this->stringOrNull($fromAccountId);
        $to = $this->stringOrNull($toAccountId);

        if (!$from) {
            $errors['from_account'] = 'Bank transfer from_account must be populated.';
        } elseif (!BankAccount::query()->whereKey($from)->exists()) {
            $errors['from_account'] = 'Bank transfer from_account does not reference a bank account.';
        }

        if (!$to) {
            $errors['to_account'] = 'Bank transfer to_account must be populated.';
        } elseif (!BankAccount::query()->whereKey($to)->exists()) {
            $errors['to_account'] = 'Bank transfer to_account does not reference a bank account.';
        }

        if ($from && $to && $from === $to) {
            $errors['bank_transfer_accounts'] = 'Bank transfer source and destination accounts must be different.';
        }
    }

    private function validateTransactionPresent(?string $paymentId, string $paymentType, string $userType, array $payload, array &$errors): void
    {
        if (!$paymentId) {
            $errors['transaction_link'] = 'Transaction source id is missing.';
            return;
        }

        $transaction = Transaction::query()
            ->where(BLC::COL_PAY_ID, $paymentId)
            ->where(BLC::COL_PAY_TP, $paymentType)
            ->where('user_type', $userType)
            ->first();

        if (!$transaction) {
            $errors['transaction_link'] = 'Transaction mirror row was not persisted.';
            return;
        }

        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount > 0.0 && abs((float) $transaction->amount - $amount) > 0.01) {
            $errors['transaction_amount'] = 'Transaction mirror amount does not match the source operation.';
        }
    }

    private function validateTransactionRemoved(?string $paymentId, string $paymentType, string $userType, array &$errors): void
    {
        if (!$paymentId) {
            return;
        }

        if (Transaction::query()
            ->where(BLC::COL_PAY_ID, $paymentId)
            ->where(BLC::COL_PAY_TP, $paymentType)
            ->where('user_type', $userType)
            ->exists()) {
            $errors['transaction_delete'] = 'Transaction mirror row still exists after the delete operation.';
        }
    }

    /**
     * @return array{debit: float, credit: float, count: int}
     */
    private function journalItemTotals(?string $journalId): array
    {
        if (!$journalId) {
            return ['debit' => 0.0, 'credit' => 0.0, 'count' => 0];
        }

        $row = DB::table(DC::TABLE_JRN_IT)
            ->where('journal', $journalId)
            ->whereNull('deleted_at')
            ->selectRaw('coalesce(sum(debit), 0) as debit, coalesce(sum(credit), 0) as credit, count(*) as item_count')
            ->first();

        return [
            'debit' => (float) ($row->debit ?? 0),
            'credit' => (float) ($row->credit ?? 0),
            'count' => (int) ($row->item_count ?? 0),
        ];
    }

    /**
     * @param array{debit: float, credit: float, count: int} $totals
     */
    private function validateJournalBalance(array $totals, array &$errors, bool $allowEmpty = false): void
    {
        if (!$allowEmpty && $totals['count'] < 2) {
            $errors['journal_item_count'] = 'Journal entry must have at least two persisted item rows.';
        }

        if ($totals['count'] > 0 && abs($totals['debit'] - $totals['credit']) > 0.01) {
            $errors['journal_balance'] = 'Journal item debit and credit totals are not balanced.';
        }

        if (!$allowEmpty && $totals['debit'] <= 0.0 && $totals['credit'] <= 0.0) {
            $errors['journal_amount'] = 'Journal entry total must be greater than zero.';
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
        if (
            array_key_exists('expense', $errors)
            || array_key_exists('expense_delete', $errors)
            || array_key_exists('expense_lines', $errors)
            || array_key_exists('expense_lines_delete', $errors)
            || array_key_exists('expense_line_delete', $errors)
        ) {
            $criteria[] = 'C4';
        }
        if (array_key_exists('transaction_link', $errors) || array_key_exists('transaction_delete', $errors)) {
            $criteria[] = 'C3';
        }
        if (
            array_key_exists('from_account', $errors)
            || array_key_exists('to_account', $errors)
            || array_key_exists('bank_transfer_accounts', $errors)
            || array_key_exists('journal_balance', $errors)
        ) {
            $criteria[] = 'C7';
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

    /**
     * @return array<string, mixed>|null
     */
    private function rowSnapshot(mixed $row): ?array
    {
        return is_object($row) ? (array) $row : null;
    }

    private function rowIsActive(mixed $row): bool
    {
        if (!is_object($row)) {
            return false;
        }

        return !property_exists($row, 'deleted_at') || $row->deleted_at === null || $row->deleted_at === '';
    }
}
