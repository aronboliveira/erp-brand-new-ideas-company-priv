<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{InboxMessage, OperationLedger, OperationalEvent, OutboxMessage};
use App\Services\Reliability\{ExternalPaymentGatewayCallbackService, ReliabilityPolicy};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(ExternalPaymentGatewayCallbackService::class)]
#[Group('services')]
#[Group('reliability')]
class ExternalPaymentGatewayCallbackServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function callback_is_processed_once_and_duplicate_replays_are_not_mutated_again(): void
    {
        $service = new ExternalPaymentGatewayCallbackService();
        $request = Request::create('/cashfree/payments/success', 'GET', [
            'order_id' => 'cf_order_1',
            'invoice_id' => 'invoice-1',
            'amount' => '1200',
        ]);
        $calls = 0;
        $options = [
            'subject_type' => 'invoice',
            'subject_id' => 'invoice-1',
            'provider_reference' => 'cf_order_1',
            'amount' => 1200,
            'duplicate_response' => fn (): array => ['duplicate' => true],
        ];

        $first = $service->handle('cashfree', 'invoice_return', $request, function () use (&$calls): array {
            $calls++;

            return ['status' => 'ok'];
        }, $options);
        $second = $service->handle('cashfree', 'invoice_return', $request, function () use (&$calls): array {
            $calls++;

            return ['status' => 'should-not-run'];
        }, $options);

        $messageKey = 'external-gateway:cashfree:invoice_return:ref:cf_order_1:subject:invoice-1';

        $this->assertSame(['status' => 'ok'], $first);
        $this->assertSame(['duplicate' => true], $second);
        $this->assertSame(1, $calls);
        $this->assertDatabaseHas(DC::TABLE_INBOX_MESSAGES, [
            'message_key' => $messageKey,
            'status' => 'processed',
        ]);
        $this->assertSame(1, OperationLedger::where('operation_type', 'finance.gateway_callback.cashfree.invoice_return')->count());
        $this->assertSame(1, OutboxMessage::where('message_key', $messageKey . ':processed')->count());
        $this->assertTrue(OperationalEvent::where('event_type', 'finance.gateway_callback.duplicate')->exists());
    }

    #[Test]
    public function replay_payload_mismatch_fails_before_mutating_unprocessed_message(): void
    {
        $service = new ExternalPaymentGatewayCallbackService();
        $messageKey = 'external-gateway:cashfree:invoice_return:ref:cf_mismatch:subject:invoice-2';
        $inbox = InboxMessage::create([
            'message_key' => $messageKey,
            'source' => 'external_payment_gateway:cashfree',
            'event_type' => 'finance.gateway_callback.cashfree.invoice_return.received',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'status' => 'received',
            'payload_hash' => hash('sha256', json_encode(['different' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''),
            'payload' => ['different' => true],
            'metadata' => ['gateway_callback' => true],
            'retry_count' => 0,
            'received_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
        $request = Request::create('/cashfree/payments/success', 'GET', [
            'order_id' => 'cf_mismatch',
            'invoice_id' => 'invoice-2',
            'amount' => '700',
        ]);
        $calls = 0;

        try {
            $service->handle('cashfree', 'invoice_return', $request, function () use (&$calls): array {
                $calls++;

                return ['status' => 'should-not-run'];
            }, [
                'subject_type' => 'invoice',
                'subject_id' => 'invoice-2',
                'provider_reference' => 'cf_mismatch',
                'amount' => 700,
            ]);
            $this->fail('Replay mismatch should throw before callback mutation.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('payload hash mismatch', $exception->getMessage());
        }

        $this->assertSame(0, $calls);
        $this->assertSame('failed', $inbox->refresh()->status);
        $this->assertTrue(OperationalEvent::where('event_type', 'finance.gateway_callback.replay_mismatch')->exists());
    }

    #[Test]
    public function rejected_callback_records_failed_inbox_and_event_for_provider_retry(): void
    {
        $service = new ExternalPaymentGatewayCallbackService();
        $request = Request::create('/paymentIPN', 'POST', [
            'tran_ref' => 'pt-1',
            'cart_id' => 'cart-1',
            'cart_amount' => '42.50',
        ]);

        $inbox = $service->recordRejected('paytabs', 'ipn', $request, 'Payment gateway not configured: paytabs.server_key', [
            'subject_type' => 'paytabs_ipn',
            'subject_id' => 'cart-1',
            'provider_reference' => 'pt-1',
            'amount' => 42.50,
        ]);

        $this->assertSame('failed', $inbox->refresh()->status);
        $this->assertSame(1, (int) $inbox->retry_count);
        $this->assertSame('Payment gateway not configured: paytabs.server_key', $inbox->last_error);
        $this->assertTrue(OperationalEvent::where('event_type', 'finance.gateway_callback.rejected')->exists());
    }
}
