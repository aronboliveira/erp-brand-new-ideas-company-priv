<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\CircuitBreakerCall;
use App\Models\CircuitBreakerState;
use App\Models\InboxMessage;
use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Models\OperationQuarantineAudit;
use App\Models\OperationalEvent;
use App\Models\OperationStep;
use App\Models\OutboxMessage;
use App\Services\Reliability\CriticalOperationService;
use App\Services\Reliability\InboxService;
use App\Services\Reliability\OperationalEventService;
use App\Services\Reliability\OutboxService;
use App\Services\Reliability\QuarantineRetentionSweepService;
use App\Services\Reliability\ReliabilityLedgerSweepService;
use App\Services\Reliability\ReliabilityPolicy;
use App\Services\Reliability\ReliabilityRetentionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(CriticalOperationService::class)]
#[CoversClass(InboxService::class)]
#[CoversClass(OperationalEventService::class)]
#[CoversClass(OutboxService::class)]
#[CoversClass(ReliabilityPolicy::class)]
#[CoversClass(ReliabilityRetentionService::class)]
#[Group('services')]
#[Group('reliability')]
class ReliabilityFoundationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        OperationalEventService::flushMemory();
        OutboxService::flushMemory();
    }

    #[Test]
    public function low_criticality_operations_remain_volatile_by_default(): void
    {
        $operationType = 'ui.preference.preview.' . Str::uuid();

        $result = (new CriticalOperationService())->run($operationType, fn() => 'ok', [
            'criticality' => ReliabilityPolicy::CRITICALITY_LOW,
            'domain' => 'ui',
            'summary' => 'Preview a harmless UI preference',
        ]);

        $message = (new OutboxService())->record('ui.preference.previewed', ['theme' => 'compact'], [
            'criticality' => ReliabilityPolicy::CRITICALITY_LOW,
        ]);

        $this->assertSame('ok', $result);
        $this->assertSame(ReliabilityPolicy::STORAGE_MEMORY, $message['storage_mode']);
        $this->assertCount(1, OutboxService::memoryMessages());
        $this->assertCount(1, OperationalEventService::memoryEvents());
        $this->assertFalse(OperationLedger::where('operation_type', $operationType)->exists());
    }

    #[Test]
    public function critical_operation_creates_durable_ledger_steps_outbox_and_events(): void
    {
        $operationKey = 'test-op-' . Str::uuid();
        $subjectId = (string) Str::uuid();

        $result = (new CriticalOperationService())->run(
            'finance.test.commit',
            function (?OperationLedger $ledger, CriticalOperationService $operations): array {
                $this->assertInstanceOf(OperationLedger::class, $ledger);
                $operations->recordStep($ledger, 'domain.validation', 'Validate domain invariants', [
                    'step_type' => 'validation',
                    'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                    'sequence' => 20,
                    'result' => ['validated' => true],
                    'started_at' => now(),
                    'finished_at' => now(),
                ]);

                return ['posted' => true];
            },
            [
                'operation_key' => $operationKey,
                'domain' => 'finance',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'summary' => 'Post a critical finance test operation',
                'subject_type' => 'test-finance-subject',
                'subject_id' => $subjectId,
                'final_status' => ReliabilityPolicy::STATUS_POSTED_TO_LEDGER,
                'outbox' => [
                    'stream' => 'finance.ledger',
                    'event_type' => 'finance.test.posted',
                    'payload' => ['subject_id' => $subjectId],
                ],
            ]
        );

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();

        $this->assertSame(['posted' => true], $result);
        $this->assertSame(ReliabilityPolicy::STATUS_POSTED_TO_LEDGER, $ledger->status);
        $this->assertSame('SERIALIZABLE', $ledger->isolation_level);
        $this->assertNotNull($ledger->committed_at);
        $this->assertNotNull($ledger->posted_at);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'operation.begin',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'domain.validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OUTBOX_MESSAGES, [
            'operation_ledger_id' => $ledger->id,
            'stream' => 'finance.ledger',
            'event_type' => 'finance.test.posted',
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'operation.started',
            'severity' => 'notice',
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'operation.committed',
        ]);
    }

    #[Test]
    public function critical_operation_records_failure_state_without_dispatching_outbox(): void
    {
        $operationKey = 'test-op-failed-' . Str::uuid();

        try {
            (new CriticalOperationService())->run(
                'finance.test.fail',
                fn() => throw new RuntimeException('simulated commit failure'),
                [
                    'operation_key' => $operationKey,
                    'domain' => 'finance',
                    'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                    'summary' => 'Fail a critical finance test operation',
                    'outbox' => [
                        'stream' => 'finance.ledger',
                        'event_type' => 'finance.test.failed_should_not_publish',
                        'payload' => ['failed' => true],
                    ],
                ]
            );
            $this->fail('The critical operation should rethrow the domain failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('simulated commit failure', $exception->getMessage());
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();

        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertSame('simulated commit failure', $ledger->error_message);
        $this->assertNotNull($ledger->failed_at);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'operation.db_transaction',
            'status' => ReliabilityPolicy::STEP_FAILED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'operation.failed',
            'severity' => 'error',
        ]);
        $this->assertDatabaseMissing(DC::TABLE_OUTBOX_MESSAGES, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'finance.test.failed_should_not_publish',
        ]);
    }

    #[Test]
    public function inbox_records_remote_messages_once_and_marks_processed(): void
    {
        $messageKey = 'remote-message-' . Str::uuid();
        $service = new InboxService();

        $first = $service->recordReceived($messageKey, 'bank.statement.imported', ['line' => 1], [
            'source' => 'bank-feed',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
        ]);
        $second = $service->recordReceived($messageKey, 'bank.statement.imported', ['line' => 2], [
            'source' => 'bank-feed',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
        ]);

        $service->markProcessed($first);

        $this->assertSame($first->id, $second->id);
        $this->assertTrue($service->isProcessed($messageKey));
        $this->assertDatabaseHas(DC::TABLE_INBOX_MESSAGES, [
            'message_key' => $messageKey,
            'status' => 'processed',
        ]);
    }

    #[Test]
    public function database_outbox_messages_are_idempotent_by_message_key(): void
    {
        $messageKey = 'durable-outbox-' . Str::uuid();
        $service = new OutboxService();

        $first = $service->record('finance.invoice.recorded', ['amount' => 100], [
            'message_key' => $messageKey,
            'stream' => 'finance.ledger',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
        ]);
        $second = $service->record('finance.invoice.recorded', ['amount' => 200], [
            'message_key' => $messageKey,
            'stream' => 'finance.ledger',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
        ]);

        $this->assertInstanceOf(OutboxMessage::class, $first);
        $this->assertInstanceOf(OutboxMessage::class, $second);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, OutboxMessage::where('message_key', $messageKey)->count());
    }

    #[Test]
    public function retention_prunes_only_expired_finished_records(): void
    {
        $expiredLedger = OperationLedger::create([
            'operation_key' => 'expired-op-' . Str::uuid(),
            'operation_type' => 'finance.expired',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now()->subDays(10),
            'committed_at' => now()->subDays(9),
            'expires_at' => now()->subDay(),
        ]);
        OperationStep::create([
            'operation_ledger_id' => $expiredLedger->id,
            'step_key' => 'expired.step',
            'step_name' => 'Expired step',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'finished_at' => now()->subDays(9),
        ]);
        $activeLedger = OperationLedger::create([
            'operation_key' => 'active-op-' . Str::uuid(),
            'operation_type' => 'finance.active',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'started_at' => now(),
            'expires_at' => now()->subDay(),
        ]);
        $expiredOutbox = OutboxMessage::create([
            'message_key' => 'expired-outbox-' . Str::uuid(),
            'stream' => 'finance.ledger',
            'event_type' => 'finance.expired',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_DISPATCHED,
            'dispatched_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);
        $expiredInbox = InboxMessage::create([
            'message_key' => 'expired-inbox-' . Str::uuid(),
            'event_type' => 'finance.remote',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => 'processed',
            'processed_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);
        $expiredEvent = OperationalEvent::create([
            'event_type' => 'finance.expired.event',
            'severity' => 'info',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => 'recorded',
            'occurred_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);
        $expiredCircuitState = CircuitBreakerState::create([
            'breaker_key' => 'expired-circuit-' . Str::uuid(),
            'name' => 'Expired circuit',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'state' => ReliabilityPolicy::CIRCUIT_CLOSED,
            'closed_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);
        $expiredCircuitCall = CircuitBreakerCall::create([
            'circuit_breaker_state_id' => $expiredCircuitState->id,
            'breaker_key' => $expiredCircuitState->breaker_key,
            'state_before' => ReliabilityPolicy::CIRCUIT_CLOSED,
            'state_after' => ReliabilityPolicy::CIRCUIT_CLOSED,
            'status' => ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED,
            'occurred_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);

        $deleted = (new ReliabilityRetentionService())->pruneExpired();

        $this->assertGreaterThanOrEqual(1, $deleted['operation_steps']);
        $this->assertGreaterThanOrEqual(1, $deleted['operation_ledgers']);
        $this->assertGreaterThanOrEqual(1, $deleted['outbox_messages']);
        $this->assertGreaterThanOrEqual(1, $deleted['inbox_messages']);
        $this->assertGreaterThanOrEqual(1, $deleted['operational_events']);
        $this->assertGreaterThanOrEqual(1, $deleted['circuit_breaker_calls']);
        $this->assertGreaterThanOrEqual(1, $deleted['circuit_breaker_states']);
        $this->assertFalse(OperationLedger::whereKey($expiredLedger->id)->exists());
        $this->assertTrue(OperationLedger::whereKey($activeLedger->id)->exists());
        $this->assertFalse(OutboxMessage::whereKey($expiredOutbox->id)->exists());
        $this->assertFalse(InboxMessage::whereKey($expiredInbox->id)->exists());
        $this->assertFalse(OperationalEvent::whereKey($expiredEvent->id)->exists());
        $this->assertFalse(CircuitBreakerCall::whereKey($expiredCircuitCall->id)->exists());
        $this->assertFalse(CircuitBreakerState::whereKey($expiredCircuitState->id)->exists());
    }

    #[Test]
    public function operational_event_compression_keeps_summary_and_marks_source_rows(): void
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'compress-op-' . Str::uuid(),
            'operation_type' => 'finance.compress',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
        OperationalEvent::create([
            'event_type' => 'operation.started',
            'severity' => 'notice',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'operation_ledger_id' => $ledger->id,
            'status' => 'recorded',
            'occurred_at' => now()->subMinute(),
        ]);
        OperationalEvent::create([
            'event_type' => 'operation.committed',
            'severity' => 'info',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'operation_ledger_id' => $ledger->id,
            'status' => 'recorded',
            'occurred_at' => now(),
        ]);

        $compressed = (new ReliabilityRetentionService())->compressOperationalEventsForOperation($ledger->id);

        $this->assertInstanceOf(OperationalEvent::class, $compressed);
        $this->assertSame('operation.events.compressed', $compressed->event_type);
        $this->assertSame(2, DB::table(DC::TABLE_OPERATIONAL_EVENTS)
            ->where('operation_ledger_id', $ledger->id)
            ->where('status', 'compressed')
            ->count());
    }

    #[Test]
    public function ledger_sweep_flips_orphaned_started_ledgers_to_failed(): void
    {
        // Orphan: critical ledger 5min old (TTL = 120s) → should be swept.
        $orphan = OperationLedger::create([
            'operation_key' => 'orphan-critical-' . Str::uuid(),
            'operation_type' => 'finance.test.orphaned',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'started_at' => now()->subMinutes(5),
            'expires_at' => now()->addDays(7),
        ]);
        OperationStep::create([
            'operation_ledger_id' => $orphan->id,
            'step_key' => 'operation.db_transaction',
            'step_name' => 'Database transaction',
            'step_type' => 'db_write',
            'sequence' => 10,
            'status' => ReliabilityPolicy::STEP_RUNNING,
            'criticality' => $orphan->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'started_at' => $orphan->started_at,
        ]);

        // Fresh: critical ledger 30s old → still inside TTL, should NOT be swept.
        $fresh = OperationLedger::create([
            'operation_key' => 'fresh-critical-' . Str::uuid(),
            'operation_type' => 'finance.test.fresh',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'started_at' => now()->subSeconds(30),
            'expires_at' => now()->addDays(7),
        ]);

        // Trivial-tier ledger past medium TTL — but trivial has null TTL, so untouched.
        $trivialOrphan = OperationLedger::create([
            'operation_key' => 'trivial-old-' . Str::uuid(),
            'operation_type' => 'misc.test.trivial',
            'domain' => 'system',
            'criticality' => ReliabilityPolicy::CRITICALITY_TRIVIAL,
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'started_at' => now()->subHour(),
            'expires_at' => now()->addDays(1),
        ]);

        $report = (new ReliabilityLedgerSweepService())->sweep(50);

        $this->assertGreaterThanOrEqual(1, $report['orphaned']);
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $orphan->fresh()->status);
        $this->assertNotNull($orphan->fresh()->failed_at);
        $this->assertSame(ReliabilityPolicy::STATUS_STARTED, $fresh->fresh()->status);
        $this->assertSame(ReliabilityPolicy::STATUS_STARTED, $trivialOrphan->fresh()->status);

        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $orphan->id,
            'step_key' => 'operation.db_transaction',
            'status' => ReliabilityPolicy::STEP_FAILED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $orphan->id,
            'event_type' => 'reliability.operation.orphaned',
        ]);
    }

    #[Test]
    public function quarantine_recover_flips_status_and_writes_audit(): void
    {
        $quarantine = OperationQuarantine::create([
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => 'hrm',
            'severity' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subMinutes(30),
            'expires_at' => now()->addDays(30),
        ]);

        $actorId = (string) Str::uuid();
        $returned = $quarantine->recover('Resolved after manual reconciliation in INC-9012.', $actorId);

        $this->assertSame($quarantine->id, $returned->id);
        $fresh = $quarantine->fresh();
        $this->assertSame(ReliabilityPolicy::QUARANTINE_RECOVERED, $fresh?->status);
        $this->assertSame('Resolved after manual reconciliation in INC-9012.', $fresh?->resolution_notes);
        $this->assertNotNull($fresh?->resolved_at);

        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_quarantine_id' => $quarantine->id,
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_RECOVERED,
            'actor_type' => 'human',
            'actor_id' => $actorId,
            'domain' => 'hrm',
        ]);
    }

    #[Test]
    public function quarantine_dismiss_flips_status_and_writes_audit_with_system_actor_when_none_provided(): void
    {
        $quarantine = OperationQuarantine::create([
            'source_table' => 'leads',
            'source_record_id' => (string) Str::uuid(),
            'domain' => 'crm',
            'severity' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subHour(),
            'expires_at' => now()->addDays(30),
        ]);

        $quarantine->dismiss('False positive — lead self-corrected via subsequent stage move.');

        $fresh = $quarantine->fresh();
        $this->assertSame(ReliabilityPolicy::QUARANTINE_DISMISSED, $fresh?->status);
        $this->assertNotNull($fresh?->resolved_at);

        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_quarantine_id' => $quarantine->id,
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_DISMISSED,
            'actor_type' => 'system',
            'domain' => 'crm',
        ]);
    }

    #[Test]
    public function quarantine_retention_sweep_dismisses_expired_overlays(): void
    {
        $expired = OperationQuarantine::create([
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => 'hrm',
            'severity' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subDays(60),
            'expires_at' => now()->subDays(2),
        ]);

        $fresh = OperationQuarantine::create([
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => 'hrm',
            'severity' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subHour(),
            'expires_at' => now()->addDays(30),
        ]);

        $alreadyResolved = OperationQuarantine::create([
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => 'hrm',
            'severity' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::QUARANTINE_RECOVERED,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subDays(40),
            'resolved_at' => now()->subDays(35),
            'expires_at' => now()->subDays(1),
        ]);

        $report = (new QuarantineRetentionSweepService())->sweep(50);

        $this->assertGreaterThanOrEqual(1, $report['dismissed']);
        $this->assertSame(ReliabilityPolicy::QUARANTINE_DISMISSED, $expired->fresh()?->status);
        $this->assertNotNull($expired->fresh()?->resolved_at);
        // Fresh row stays in manual_review (not past TTL).
        $this->assertSame(ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW, $fresh->fresh()?->status);
        // Already-resolved row is untouched (not in pending/manual_review filter).
        $this->assertSame(ReliabilityPolicy::QUARANTINE_RECOVERED, $alreadyResolved->fresh()?->status);

        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_quarantine_id' => $expired->id,
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_DISMISSED,
            'actor_type' => 'scheduler',
            'domain' => 'hrm',
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'event_type' => 'reliability.quarantine.expired',
            'subject_id' => $expired->source_record_id,
        ]);
    }

    #[Test]
    public function backfill_quarantine_domains_recovers_truncated_rows_from_ledger(): void
    {
        // Simulate a pre-Q1 truncated row: ledger has 'hrm', quarantine has ''.
        $hrmLedger = OperationLedger::create([
            'operation_key' => 'backfill-hrm-' . Str::uuid(),
            'operation_type' => 'hrm.employee.update',
            'domain' => 'hrm',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_FAILED,
            'started_at' => now()->subDays(3),
            'failed_at' => now()->subDays(3),
            'expires_at' => now()->addDays(30),
        ]);

        // Directly insert with raw query to simulate the truncated state (DB::table avoids Eloquent + lets us pass '').
        $truncatedId = (string) Str::uuid();
        DB::table(DC::TABLE_OPERATION_QUARANTINES)->insert([
            'id' => $truncatedId,
            'operation_ledger_id' => $hrmLedger->id,
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => '',
            'severity' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            'quarantined_at' => now()->subDays(3),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Unrecoverable case: no linked ledger.
        $orphanId = (string) Str::uuid();
        DB::table(DC::TABLE_OPERATION_QUARANTINES)->insert([
            'id' => $orphanId,
            'operation_ledger_id' => null,
            'source_table' => 'employees',
            'source_record_id' => (string) Str::uuid(),
            'domain' => '',
            'severity' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'quarantined_at' => now()->subDays(3),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('reliability:backfill-quarantine-domains', ['--limit' => 50])
            ->assertExitCode(0);

        $recovered = OperationQuarantine::find($truncatedId);
        $this->assertSame('hrm', $recovered?->domain);

        $orphan = OperationQuarantine::find($orphanId);
        $this->assertSame('', $orphan?->domain);

        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_quarantine_id' => $truncatedId,
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_JUDGE_DECISION,
            'domain' => 'hrm',
        ]);
    }
}
