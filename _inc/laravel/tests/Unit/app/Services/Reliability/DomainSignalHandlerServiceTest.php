<?php

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{InboxMessage, OperationLedger, OperationalEvent, OutboxMessage};
use App\Services\Reliability\{DomainSignalHandlerService, FinanceOutboxDispatcher, ReliabilityPolicy};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DomainSignalHandlerServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function finance_dispatcher_consumes_default_signals_through_inbox_handlers(): void
    {
        [$ledger, $message] = $this->outbox('finance.invoice.payment_created', 'finance.ledger', 'finance');

        $report = (new FinanceOutboxDispatcher())->dispatchMessage($message);

        $this->assertSame('dispatched', $report['status']);
        $this->assertNotEmpty($report['signals']);
        $this->assertSame('handled', $report['signals'][0]['status']);
        $this->assertSame('local_inbox_handler', $report['signals'][0]['mode']);
        $this->assertArrayHasKey('handler', $report['signals'][0]);
        $this->assertFalse(str_ends_with((string) $report['signals'][0]['target'], '_shell'));

        $this->assertGreaterThanOrEqual(count($report['signals']), InboxMessage::where('operation_ledger_id', $ledger->id)->count());
        $this->assertTrue(InboxMessage::where('operation_ledger_id', $ledger->id)->where('status', 'processed')->exists());
        $this->assertTrue(OperationalEvent::where('operation_ledger_id', $ledger->id)->where('event_type', 'finance.signal.handled')->exists());
    }

    #[Test]
    public function domain_signal_handler_is_idempotent_by_inbox_key(): void
    {
        [$ledger, $message] = $this->outbox('finance.reporting.refresh', 'finance.ledger', 'finance');
        $signal = [
            'name' => 'finance-reporting',
            'channel' => 'finance.reporting',
            'target' => 'finance_reporting_projection',
            'mode' => 'monolith_callback',
            'status' => 'accepted',
            'summary' => 'Finance reporting projections can refresh after commit.',
        ];
        $handler = new DomainSignalHandlerService();

        $first = $handler->handle($message, $ledger, $signal, 'finance');
        $second = $handler->handle($message, $ledger, $signal, 'finance');

        $this->assertSame('handled', $first['status']);
        $this->assertSame('already_handled', $second['status']);
        $this->assertSame('skipped', $second['handler']['status']);
        $this->assertSame(1, InboxMessage::where('message_key', 'like', $message->message_key . ':signal:%')->count());
    }

    #[Test]
    public function projection_signals_write_cache_projection_metadata(): void
    {
        [$ledger, $message] = $this->outbox('planning.project.status_changed', 'planning.operations', 'planning', [
            'project_id' => (string) Str::uuid(),
        ]);
        $signal = [
            'name' => 'planning-projection',
            'channel' => 'planning.projection',
            'target' => 'project_planning_projection_shell',
            'mode' => 'monolith_callback',
            'status' => 'accepted',
            'summary' => 'Planning projections can refresh after commit.',
        ];

        $handled = (new DomainSignalHandlerService())->handle($message, $ledger, $signal, 'planning');

        $cacheKey = (string) data_get($handled, 'handler.side_effects.projection_cache_key');
        $this->assertNotSame('', $cacheKey);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertSame('project_planning_projection_handler', $handled['target']);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function outbox(string $eventType, string $stream, string $domain, array $payload = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => $domain . '-signal-test-' . Str::uuid(),
            'operation_type' => $eventType,
            'domain' => $domain,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => $stream,
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_READY,
            'aggregate_type' => $domain . '.test',
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => $payload,
            'max_attempts' => 2,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);

        return [$ledger, $message];
    }
}
