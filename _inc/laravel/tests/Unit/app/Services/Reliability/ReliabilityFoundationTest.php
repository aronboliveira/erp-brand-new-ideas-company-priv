<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\InboxMessage;
use App\Models\OperationLedger;
use App\Models\OperationalEvent;
use App\Models\OperationStep;
use App\Models\OutboxMessage;
use App\Services\Reliability\CriticalOperationService;
use App\Services\Reliability\InboxService;
use App\Services\Reliability\OperationalEventService;
use App\Services\Reliability\OutboxService;
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

        $deleted = (new ReliabilityRetentionService())->pruneExpired();

        $this->assertGreaterThanOrEqual(1, $deleted['operation_steps']);
        $this->assertGreaterThanOrEqual(1, $deleted['operation_ledgers']);
        $this->assertGreaterThanOrEqual(1, $deleted['outbox_messages']);
        $this->assertGreaterThanOrEqual(1, $deleted['inbox_messages']);
        $this->assertGreaterThanOrEqual(1, $deleted['operational_events']);
        $this->assertFalse(OperationLedger::whereKey($expiredLedger->id)->exists());
        $this->assertTrue(OperationLedger::whereKey($activeLedger->id)->exists());
        $this->assertFalse(OutboxMessage::whereKey($expiredOutbox->id)->exists());
        $this->assertFalse(InboxMessage::whereKey($expiredInbox->id)->exists());
        $this->assertFalse(OperationalEvent::whereKey($expiredEvent->id)->exists());
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
}
