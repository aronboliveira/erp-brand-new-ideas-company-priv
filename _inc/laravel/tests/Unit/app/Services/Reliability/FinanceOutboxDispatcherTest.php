<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\CircuitBreakerCall;
use App\Models\CircuitBreakerState;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use App\Services\Reliability\CriticalOperationService;
use App\Services\Reliability\FinanceCompensationService;
use App\Services\Reliability\FinanceOperationService;
use App\Services\Reliability\FinanceOutboxDispatcher;
use App\Services\Reliability\ReliabilityPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(FinanceCompensationService::class)]
#[CoversClass(FinanceOperationService::class)]
#[CoversClass(FinanceOutboxDispatcher::class)]
#[Group('services')]
#[Group('reliability')]
class FinanceOutboxDispatcherTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function finance_operation_service_derives_outbox_payload_after_commit(): void
    {
        $paymentId = 'payment-' . Str::uuid();

        $result = (new FinanceOperationService())->run(
            'finance.invoice.payment.create',
            fn(): array => ['payment_id' => $paymentId, 'amount' => 125.50],
            [
                'summary' => 'Create invoice payment',
                'subject_type' => 'invoice',
                'subject_id' => 'invoice-1',
                'event_type' => 'finance.invoice.payment_created',
                'message_key' => fn(array $payload): string => 'finance.invoice.payment_created:' . $payload['payment_id'],
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $ledger = $result->ledger();
        $message = $result->outboxMessage();

        $this->assertInstanceOf(OperationLedger::class, $ledger);
        $this->assertInstanceOf(OutboxMessage::class, $message);
        $this->assertSame(ReliabilityPolicy::STATUS_COMMITTED, $ledger->status);
        $this->assertSame('finance.invoice.payment_created:' . $paymentId, $message->message_key);
        $this->assertSame($paymentId, $message->payload['payment_id']);
    }

    #[Test]
    public function dispatcher_closes_ledger_after_finance_signals_are_accepted(): void
    {
        [$ledger, $message] = $this->financeOutbox('finance.invoice.payment_created');

        $report = (new FinanceOutboxDispatcher())->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dispatched', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DISPATCHED, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->status);
        $this->assertNotNull($ledger->closed_at);
        $this->assertGreaterThanOrEqual(4, count($report['signals']));
        $this->assertTrue(CircuitBreakerState::where('breaker_key', 'finance.outbox.finance.invoice.payment_created')->exists());
        $this->assertTrue(CircuitBreakerCall::where('breaker_key', 'finance.outbox.finance.invoice.payment_created')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)
            ->exists());
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'outbox.dispatch:' . $message->id,
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'finance.outbox.dispatched',
        ]);
    }

    #[Test]
    public function dispatcher_schedules_simple_retry_before_dead_lettering(): void
    {
        [$ledger, $message] = $this->financeOutbox('finance.bill.payment_created', ['max_attempts' => 3]);

        $dispatcher = new FinanceOutboxDispatcher(handlers: [
            'finance.bill.payment_created' => fn(): array => throw new RuntimeException('banking shell unavailable'),
        ]);

        $report = $dispatcher->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('failed', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_FAILED, $message->status);
        $this->assertSame(1, $message->retry_count);
        $this->assertNotNull($message->next_retry_at);
        $this->assertSame(ReliabilityPolicy::STATUS_COMMITTED, $ledger->status);
        $this->assertSame(2, CircuitBreakerCall::where('breaker_key', 'finance.outbox.finance.bill.payment_created')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_FAILED)
            ->count());
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'reliability.retry.retrying',
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'reliability.retry.failed',
        ]);
        $this->assertDatabaseMissing(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_name' => 'Finance compensation required',
        ]);
    }

    #[Test]
    public function dispatcher_dead_letters_and_marks_compensation_required_when_attempts_are_exhausted(): void
    {
        [$ledger, $message] = $this->financeOutbox('finance.invoice.payment_deleted', ['max_attempts' => 1]);

        $dispatcher = new FinanceOutboxDispatcher(handlers: [
            'finance.invoice.payment_deleted' => fn(): array => throw new RuntimeException('journal reversal callback unavailable'),
        ]);

        $report = $dispatcher->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dead_letter', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DEAD_LETTER, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATING, $ledger->status);
        $this->assertSame('journal reversal callback unavailable', $ledger->error_message);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'status' => ReliabilityPolicy::STEP_PENDING,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'finance.compensation.required',
            'severity' => 'error',
        ]);
    }

    /**
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function financeOutbox(string $eventType, array $messageOverrides = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'finance-test-' . Str::uuid(),
            'operation_type' => 'finance.test.operation',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create(array_merge([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => 'finance.ledger',
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => 'finance-test',
            'aggregate_id' => 'finance-test-id',
            'operation_ledger_id' => $ledger->id,
            'payload' => ['amount' => 100],
            'max_attempts' => 3,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ], $messageOverrides));

        return [$ledger, $message];
    }
}
