<?php

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OperationStep, OutboxMessage};
use App\Services\Reliability\{CompensationExecutorService, ReliabilityPolicy};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, Cache};
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Test};
use Tests\TestCase;

#[CoversClass(CompensationExecutorService::class)]
class CompensationExecutorServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function executor_completes_finance_compensation_required_workflow(): void
    {
        [$ledger, $message] = $this->compensatingLedger('finance', 'finance.ledger', 'finance.invoice.payment_deleted', [
            'payment_id' => 'missing-payment-' . Str::uuid(),
            'amount' => 6400,
        ]);

        $report = (new CompensationExecutorService())->executeLedger($ledger);

        $ledger->refresh();

        $this->assertSame('compensated', $report['status']);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATED, $ledger->status);
        $this->assertContains('ledger_reversal_review_registered', $report['result']['actions']);
        $this->assertContains('cash_reconciliation_registered', $report['result']['actions']);
        $this->assertTrue(Cache::has($report['result']['cache_key']));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'status' => ReliabilityPolicy::STEP_COMPENSATED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.execute:' . $message->id,
            'status' => ReliabilityPolicy::STEP_COMPENSATED,
        ]);
        $this->assertTrue(OperationalEvent::where('operation_ledger_id', $ledger->id)
            ->where('outbox_message_id', $message->id)
            ->where('event_type', 'finance.compensation.completed')
            ->exists());
    }

    #[Test]
    public function executor_leaves_ledger_compensating_when_dead_letter_state_is_missing(): void
    {
        [$ledger, $message] = $this->compensatingLedger('warehouse', 'warehouse.operations', 'warehouse.stock.adjusted');
        $message->forceFill(['status' => ReliabilityPolicy::OUTBOX_FAILED])->save();

        $report = (new CompensationExecutorService())->executeLedger($ledger);

        $ledger->refresh();

        $this->assertSame('failed', $report['status']);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATING, $ledger->status);
        $this->assertSame('Compensation requires a dead-letter outbox message.', $report['error']);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.execute:' . $message->id,
            'status' => ReliabilityPolicy::STEP_FAILED,
        ]);
        $this->assertTrue(OperationalEvent::where('operation_ledger_id', $ledger->id)
            ->where('outbox_message_id', $message->id)
            ->where('event_type', 'warehouse.compensation.failed')
            ->exists());
    }

    #[Test]
    public function executor_scans_pending_ledgers_by_domain(): void
    {
        $this->compensatingLedger('finance', 'finance.ledger', 'finance.revenue.created');
        $this->compensatingLedger('crm', 'crm.operations', 'crm.deal.status_changed');

        $report = (new CompensationExecutorService())->executePending(10, 'crm');

        $this->assertSame(1, $report['processed']);
        $this->assertSame(1, $report['compensated']);
        $this->assertSame('crm', $report['reports'][0]['domain']);
        $this->assertTrue(OperationalEvent::where('event_type', 'crm.compensation.completed')->exists());
        $this->assertFalse(OperationalEvent::where('event_type', 'finance.compensation.completed')->exists());
    }

    #[Test]
    public function compensation_command_is_registered(): void
    {
        $this->assertSame(0, Artisan::call('list', ['--raw' => true]));
        $this->assertStringContainsString('reliability:execute-compensation', Artisan::output());
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: OperationLedger, 1: OutboxMessage, 2: OperationStep}
     */
    private function compensatingLedger(string $domain, string $stream, string $eventType, array $payload = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => $domain . '-compensation-test-' . Str::uuid(),
            'operation_type' => $eventType,
            'domain' => $domain,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_COMPENSATING,
            'started_at' => now()->subMinutes(5),
            'committed_at' => now()->subMinutes(4),
            'failed_at' => now()->subMinute(),
            'error_message' => 'dead-letter test failure',
            'expires_at' => now()->addDay(),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);

        $message = OutboxMessage::create([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => $stream,
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_DEAD_LETTER,
            'aggregate_type' => $domain . '.test',
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => $payload,
            'retry_count' => 3,
            'max_attempts' => 3,
            'available_at' => now()->subMinutes(2),
            'failed_at' => now()->subMinute(),
            'last_error' => 'dead-letter test failure',
            'expires_at' => now()->addDay(),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);

        $step = OperationStep::create([
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'step_name' => ucfirst($domain) . ' compensation required',
            'step_type' => 'cleanup',
            'sequence' => 900,
            'status' => ReliabilityPolicy::STEP_PENDING,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'payload' => [
                'outbox_message_id' => $message->id,
                'event_type' => $message->event_type,
                'message_key' => $message->message_key,
            ],
            'error_message' => 'dead-letter test failure',
            'started_at' => now()->subMinute(),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);

        return [$ledger, $message, $step];
    }
}
