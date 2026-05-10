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

## Finance dispatcher slice

The first functional outbox dispatch slice is finance-only and monolith-local.
There is no broker requirement yet.

- `FinanceOperationService` wraps finance mutations and derives the outbox
  message key/payload after the DB callback has created the payment row.
- `FinanceOutboxDispatcher` processes `finance.ledger` outbox rows after
  commit and records accepted internal signals for journal control, banking
  API shells, communication API shells, ledger reversal review, and webhook
  shells.
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

## Rollback and compensation

Two rollback surfaces are now defined:

- Failures inside `CriticalOperationService::run()` happen before the DB commit
  completes, so Laravel rolls back the mutation and the operation ledger is
  marked `failed`.
- Failures after commit, during finance outbox dispatch, cannot undo the
  original SQL commit. The dispatcher applies simple retry scheduling first;
  once attempts are exhausted it marks the outbox `dead_letter`, records a
  `compensation.required:*` step, emits `finance.compensation.required`, and
  moves the operation ledger to `compensating`.

Actual domain reversal remains a later, domain-specific implementation. The
important current guarantee is that post-commit signal failure becomes durable,
visible, and queryable instead of disappearing into normal Laravel logs.

## Retention

Every durable row should have an `expires_at`. `ReliabilityRetentionService`
currently prunes expired finished rows and can compress verbose operational
events for a single operation. Do not introduce unbounded outbox/ledger writes
without a retention plan.

## Tests

Use focused PHPUnit tests under `tests/Unit/app/Services/Reliability/`.
Current baseline:

```bash
php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage
```

Latest local check after the finance dispatcher slice:

```text
tests/Unit/app/Services/Reliability --no-coverage:
11 tests, 73 assertions, 0 errors, 0 failures.

tests/Unit --no-coverage:
10,590 tests, 20,457 assertions, 0 errors, 0 failures.
```

Do not run `php artisan test`; this project uses `vendor/bin/phpunit` directly.
