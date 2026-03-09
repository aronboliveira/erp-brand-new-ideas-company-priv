# Financial Processing Rules

## ACID / Rollback Principles

All financial operations must be wrapped in database transactions. If any step fails,
the entire operation rolls back — partial financial state is never acceptable.

```php
DB::transaction(function () {
    // debit, credit, ledger entries — all or nothing
});
```

Use `DB::transaction()` with closures for automatic rollback on exception, or
`DB::beginTransaction()` / `DB::commit()` / `DB::rollBack()` for manual control
when you need granular error handling between steps.

## Logging for Debugging & Monitoring

Financial operations require **clear, detailed logging** at every decision point:

- Log the **before** state (account balance, invoice status) before mutation.
- Log the **after** state immediately following a committed transaction.
- Log **all exceptions** with full context: amount, currency, account IDs, user, timestamp.
- Use structured context arrays, never string interpolation for financial data.

```php
Log::info('Payment processing started', [
    'invoice_id' => $invoice->id,
    'amount'     => $amount,
    'currency'   => $currency,
    'user_id'    => auth()->id(),
]);
```

## Review Final Classes & Methods

Before deploying financial logic:

1. Review all `final` classes — they cannot be extended, so ensure the base implementation is complete.
2. Review all public methods on financial service classes — each is a contract.
3. Verify that rounding uses `bcmath` or `round($val, 2, PHP_ROUND_HALF_UP)` consistently.
4. Confirm that currency precision matches the database column scale.
