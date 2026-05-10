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

Do not run `php artisan test`; this project uses `vendor/bin/phpunit` directly.
