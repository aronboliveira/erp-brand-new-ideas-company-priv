<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Exceptions\Reliability\QuarantineRollbackRequiredException;
use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Models\OutboxMessage;
use App\Services\Reliability\FinanceOperationService;
use App\Services\Reliability\FinanceOutboxDispatcher;
use App\Services\Reliability\FinancePostWriteValidator;
use App\Services\Reliability\PostWriteValidationResult;
use App\Services\Reliability\QuarantineDecision;
use App\Services\Reliability\QuarantineRemediationJudge;
use App\Services\Reliability\QuarantineService;
use App\Services\Reliability\ReliabilityPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

#[CoversClass(FinanceOperationService::class)]
#[CoversClass(FinanceOutboxDispatcher::class)]
#[CoversClass(FinancePostWriteValidator::class)]
#[CoversClass(PostWriteValidationResult::class)]
#[CoversClass(QuarantineDecision::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class FinanceQuarantineTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function finance_post_write_failure_rolls_back_and_records_quarantine_overlay(): void
    {
        $operationKey = 'finance-quarantine-' . Str::uuid();
        $paymentId = 'missing-payment-' . Str::uuid();

        try {
            (new FinanceOperationService())->run(
                'finance.invoice.payment.create',
                fn(): array => [
                    'invoice_id' => 'missing-invoice-' . Str::uuid(),
                    'payment_id' => $paymentId,
                    'account_id' => 'missing-account-' . Str::uuid(),
                    'amount' => 150.25,
                    'direction' => 'client_receipt',
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Create invoice payment with invalid persisted state',
                    'event_type' => 'finance.invoice.payment_created',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ],
            );

            $this->fail('Invalid finance post-write state should trigger quarantine rollback.');
        } catch (QuarantineRollbackRequiredException $exception) {
            $this->assertNotNull($exception->quarantine());
            $this->assertSame($paymentId, $exception->quarantine()?->source_record_id);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertFalse(OutboxMessage::where('operation_ledger_id', $ledger->id)->exists());

        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_INV_PAY,
            'source_record_id' => $paymentId,
            'domain' => 'finance',
            'severity' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::QUARANTINE_ROLLED_BACK,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_ROLLBACK,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_ledger_id' => $ledger->id,
            'source_record_id' => $paymentId,
            'domain' => 'finance',
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_QUARANTINED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINE_AUDITS, [
            'operation_ledger_id' => $ledger->id,
            'source_record_id' => $paymentId,
            'domain' => 'finance',
            'action' => ReliabilityPolicy::QUARANTINE_ACTION_ROLLED_BACK,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'event_type' => 'finance.quarantine.rolled_back',
            'severity' => 'critical',
        ]);
    }

    #[Test]
    public function finance_outbox_dispatcher_blocks_quarantined_operation_ledgers(): void
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'finance-quarantined-dispatch-' . Str::uuid(),
            'operation_type' => 'finance.invoice.payment.create',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create([
            'message_key' => 'finance.invoice.payment_created:' . Str::uuid(),
            'stream' => 'finance.ledger',
            'event_type' => 'finance.invoice.payment_created',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => 'invoice',
            'aggregate_id' => 'invoice-id',
            'operation_ledger_id' => $ledger->id,
            'payload' => ['amount' => 100],
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        OperationQuarantine::create([
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_INV_PAY,
            'source_type' => 'invoice-payment',
            'source_record_id' => 'payment-id',
            'domain' => 'finance',
            'severity' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::QUARANTINE_ROLLED_BACK,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_ROLLBACK,
            'failed_criteria' => ['C1'],
            'validation_errors' => ['amount' => 'invalid'],
            'snapshot_payload' => ['amount' => 100],
            'quarantined_at' => now(),
            'resolved_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $report = (new FinanceOutboxDispatcher())->dispatchMessage($message);
        $message->refresh();

        $this->assertSame('skipped', $report['status']);
        $this->assertSame('quarantined_operation', $report['reason']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_PENDING, $message->status);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'finance.outbox.quarantine_blocked',
            'severity' => 'critical',
        ]);
    }
}
