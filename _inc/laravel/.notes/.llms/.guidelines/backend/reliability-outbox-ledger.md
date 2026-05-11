# Reliability Outbox and Operation Ledger

> Last updated: 2026-05-10. Applies to high-impact business operations across
> finance, HR, planning, products/warehouse, and heavy system workflows.

## Purpose

The project now has a generic reliability foundation for operations that need
more than normal Laravel logs:

- `operation_ledgers` — durable manager record for a high-impact operation.
- `operation_steps` — ordered state-machine/step log before and during commit.
- `outbox_messages` — durable publish-after-commit intent.
- `inbox_messages` — durable receive-side idempotency guard.
- `operational_events` — durable operational event timeline for medium+ work.
- `circuit_breaker_states` — durable state for guarded critical call paths.
- `circuit_breaker_calls` — sliding-window call history for circuit decisions.
- `operation_quarantines` — overlay records for extreme post-write validation
  failures that must block downstream use.
- `operation_quarantine_audits` — append-only quarantine decision timeline.

These tables are not finance-only. Finance postings are the first integration
because they are mission-critical, but employee status decisions, irreversible
project closure/deletion, warehouse commits, payroll, external imports, and
long-running I/O operations should use the same policy when their business
impact justifies it.

## Severity policy

Use `App\Services\Reliability\ReliabilityPolicy`:

| Criticality | Default storage | Operation ledger | Typical usage |
| --- | --- | --- | --- |
| `trivial` | memory | no | minor UI previews, harmless toggles |
| `low` | memory | no | lightweight customization, non-critical CRUD |
| `medium` | database | yes | business workflow with recoverable impact |
| `high` | database | yes | employee/project/warehouse decisions |
| `critical` | database | yes | finance posting, payroll, irreversible commits |

Criticality should rise with the SQL transaction isolation level when the
underlying procedure supports it:

- `critical` → `SERIALIZABLE`
- `high` → `REPEATABLE READ`
- `medium` → `READ COMMITTED`
- `low` / `trivial` → no forced isolation

## Usage pattern

Wrap critical business work in `CriticalOperationService::run()` and pass:

- `domain` such as `finance`, `hrm`, `planning`, `products`, or `system`
- `criticality`
- `subject_type` and `subject_id`
- `summary`
- optional `outbox` with `stream`, `event_type`, and `payload`

For low-impact work, prefer `OperationalEventService` or `OutboxService` with
`criticality => low`; this keeps the event in memory and avoids DB bloat.

## Retry and circuit breaker guards

Use builder APIs when a critical operation calls a fragile local integration,
shell adapter, webhook, or other expensive/externally influenced path:

```php
Retry::builder('finance.outbox.dispatch.payment.created')
    ->maxAttempts(3)
    ->retryOn(RuntimeException::class)
    ->abortOn(CircuitBreakerOpenException::class)
    ->intervalUsing(fn(int $attempt): int => ReliabilityPolicy::retryDelaySeconds($attempt))
    ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
    ->channel('finance.outbox')
    ->build()
    ->run(fn(): mixed => $guardedWork());
```

```php
CircuitBreaker::builder('finance.outbox.payment.created')
    ->slidingWindowSize(20)
    ->slidingWindowSeconds(300)
    ->failureRateThreshold(50.0)
    ->minimumCalls(5)
    ->openStateDurationSeconds(60)
    ->halfOpenAllowedCalls(3)
    ->halfOpenConservative(true)
    ->criticality(ReliabilityPolicy::CRITICALITY_CRITICAL)
    ->channel('finance.outbox')
    ->build()
    ->call(fn(): mixed => $guardedWork());
```

Retry emits `reliability.retry.success`, `reliability.retry.retrying`, and
`reliability.retry.failed`. Circuit breaker emits
`reliability.circuit.state_changed`, `reliability.circuit.opened`, and
`reliability.circuit.rejected`.

Retry intervals default to capped exponential backoff through
`ReliabilityPolicy::retryDelaySeconds()`: 30s, 60s, 120s, 240s, then 300s.
Use `intervalUsing()` only when a specific guarded call needs a different
cadence; `onInterval()` receives the computed delay so a caller can log,
schedule, or notify without duplicating the policy.

Circuit breakers are disabled by default for `trivial` and `low` criticality
so lightweight UI/customization work does not pay durable tracking overhead.
`medium` and above persist state/calls. High and critical builders default to
conservative half-open behavior; percentage half-open mode is available for
lower-severity cases, with the threshold clamped to at least 25%.

## Finance dispatcher slice

The first functional outbox dispatch slice is finance-only and monolith-local.
There is no broker requirement yet.

- `FinanceOperationService` wraps finance mutations and derives the outbox
  message key/payload after the DB callback has created the payment row.
- `FinanceOutboxDispatcher` processes `finance.ledger` outbox rows after
  commit and records accepted internal signals for journal control, banking
  API shells, communication API shells, ledger reversal review, and webhook
  shells.
- Each finance outbox signal is guarded by `Retry` and `CircuitBreaker` before
  the durable outbox row is marked processed, failed, or dead-lettered.
- Every finance transaction is retry-eligible. Retry attempts scale through
  `FinanceReliabilityPolicy`: basic amounts start at 2 attempts, `>= 3,200`
  starts at 3, `>= 25,000` at 4, `>= 100,000` at 5, `>= 250,000` at 6, and
  `>= 1,000,000` at 8, with small increases for reversals and persistent
  instability signals. High-risk metadata such as gateway/external origin,
  approval requirement, privileged actor, high user risk score, or payroll/tax/
  transfer/reversal direction can also increase attempts. Do not copy this rule
  to non-finance CRUD; those modules may still skip retries when the business
  impact is low.
- `DispatchFinanceOutboxCommand` exposes the same flow through
  `php artisan reliability:dispatch-finance-outbox`.
- Invoice and bill payment create/delete controller actions now use the
  finance operation wrapper, dispatch their local finance outbox immediately
  after commit, and flash a `reliability_operation` payload for client-side
  progress feedback.
- `OperationStatusController` serves the status payload through the named route
  `reliability.operations.show`; the app route pluralizer renders the URI as
  `reliabilities/operations/{operation}`.

This is still a monolithic callback/signal flow. Future queue, Redis, database
queue, or stream/broker adoption should keep the same outbox table as the
commit boundary and only replace how pending rows are drained.

## Post-write quarantine

Quarantine is intentionally narrower than the general reliability layer. It is
for extreme critical procedures where the database can accept a schema-valid but
business-invalid row, and where allowing the row to continue would be more
dangerous than the overhead of extra validation/audit writes.

Current production scope is finance payments only:

- invoice payment create/delete
- bill payment create/delete

These paths opt into `FinanceOperationService` post-write validation through
`post_write_validation => true`, but that flag now delegates to
`FinanceReliabilityPolicy`; it does not mean "validate every payment." Post-write
validation starts at amount `3,200` or an explicit force flag. The validator
fetches the persisted payment and linked finance record after the write callback,
before outbox creation. It checks critical payment fields, bank account
references, payment ownership links, overpayment/imbalance, and payment-status
consistency.

If validation fails without persistence signals, the service throws
`FinancePostWriteValidationFailedException`, the domain transaction rolls back,
no outbox row is created, and no quarantine overlay is written. Quarantine is
reserved for repeated/stuck corrupted-data scenarios: multiple recent failed
operation ledgers, retry failures, dead letters, several finance circuit-breaker
state/rejection events, or a long-running unresolved operation. Only then can
`QuarantineRollbackRequiredException` route the failed validation to
`QuarantineService`, which writes the overlay quarantine record, audit entries,
and a critical operational event against the existing operation ledger. Source
finance rows are not marked directly; the overlay table is the canonical
quarantine signal for this slice.

Do not enable quarantine for routine CRUD, lightweight customization, ordinary
imports, or non-critical stage movement. Use operation ledgers, outbox, retry, or
normal validation first. Add quarantine only when a specific business invariant
justifies the DB reads, audit writes, and downstream blocking.

## Rollback and compensation

Two rollback surfaces are now defined:

- Failures inside `CriticalOperationService::run()` happen before the DB commit
  completes, so Laravel rolls back the mutation and the operation ledger is
  marked `failed`.
- Failures after commit, during finance outbox dispatch, cannot undo the
  original SQL commit. The dispatcher first runs in-process retry/circuit
  guards, then applies durable outbox retry scheduling; once attempts are
  exhausted it marks the outbox `dead_letter`, records a
  `compensation.required:*` step, emits `finance.compensation.required`, and
  moves the operation ledger to `compensating`.

Actual domain reversal remains a later, domain-specific implementation. The
important current guarantee is that post-commit signal failure becomes durable,
visible, and queryable instead of disappearing into normal Laravel logs.

## Retention

Every durable row should have an `expires_at`. `ReliabilityRetentionService`
currently prunes expired finished rows, circuit breaker calls, closed/disabled
circuit breaker states, resolved quarantine overlays, and can compress verbose
operational events for a single operation. Do not introduce unbounded
outbox/ledger/circuit/quarantine writes without a retention plan.

## Tests

Use focused PHPUnit tests under `tests/Unit/app/Services/Reliability/`.
Current baseline:

```bash
php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage
```

Latest local reliability check after the finance quarantine policy tightening:

```text
tests/Unit/app/Services/Reliability --no-coverage:
24 tests, 142 assertions, 0 errors, 0 failures.

tests/Unit --no-coverage:
10,607 tests, 20,530 assertions, 0 errors, 0 failures.
```

Do not run `php artisan test`; this project uses `vendor/bin/phpunit` directly.
