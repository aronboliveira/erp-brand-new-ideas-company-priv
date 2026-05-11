<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Exceptions\Reliability\{FinancePostWriteValidationFailedException, QuarantineRollbackRequiredException};
use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Models\OutboxMessage;
use App\Services\Reliability\FinanceOperationService;
use App\Services\Reliability\FinanceOutboxDispatcher;
use App\Services\Reliability\FinancePostWriteValidator;
use App\Services\Reliability\FinanceReliabilityAssessment;
use App\Services\Reliability\FinanceReliabilityPolicy;
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
#[CoversClass(FinanceReliabilityAssessment::class)]
#[CoversClass(FinanceReliabilityPolicy::class)]
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
    public function finance_low_amount_still_gets_retry_but_skips_post_write_validation(): void
    {
        $operationKey = 'finance-low-retry-' . Str::uuid();
        $paymentId = 'missing-payment-' . Str::uuid();

        $result = (new FinanceOperationService())->run(
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
                'summary' => 'Create low amount invoice payment',
                'subject_type' => 'invoice',
                'subject_id' => 'invoice-low-' . Str::uuid(),
                'event_type' => 'finance.invoice.payment_created',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $ledger = $result->ledger();
        $outbox = $result->outboxMessage();

        $this->assertNotNull($ledger);
        $this->assertNotNull($outbox);
        $this->assertSame(2, $outbox?->max_attempts);
        $this->assertSame(2, data_get($outbox?->metadata, 'retry.max_attempts'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger?->id,
            'step_key' => 'finance.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SKIPPED,
        ]);
        $this->assertFalse(OperationQuarantine::where('operation_ledger_id', $ledger?->id)->exists());
    }

    #[Test]
    public function finance_post_write_failure_rolls_back_without_quarantine_when_not_persistent(): void
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
                    'amount' => FinanceReliabilityPolicy::AMOUNT_POST_WRITE_VALIDATION,
                    'direction' => 'client_receipt',
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Create invoice payment with invalid persisted state but no persistence signal',
                    'event_type' => 'finance.invoice.payment_created',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ],
            );

            $this->fail('Invalid finance post-write state should roll back the operation.');
        } catch (FinancePostWriteValidationFailedException $exception) {
            $this->assertSame($paymentId, $exception->validation()->sourceRecordId);
            $this->assertFalse($exception->assessment()->quarantineCandidate);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertFalse(OutboxMessage::where('operation_ledger_id', $ledger->id)->exists());
        $this->assertFalse(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }

    #[Test]
    public function finance_post_write_failure_records_quarantine_only_after_persistent_instability(): void
    {
        $operationKey = 'finance-quarantine-' . Str::uuid();
        $paymentId = 'missing-payment-' . Str::uuid();
        $subjectId = 'invoice-persistent-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'finance-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'finance.invoice.payment.create',
                'domain' => 'finance',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'subject_type' => 'invoice',
                'subject_id' => $subjectId,
                'summary' => 'Prior failed invoice payment attempt',
                'started_at' => now()->subMinutes(20),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        try {
            (new FinanceOperationService())->run(
                'finance.invoice.payment.create',
                fn(): array => [
                    'invoice_id' => $subjectId,
                    'payment_id' => $paymentId,
                    'account_id' => 'missing-account-' . Str::uuid(),
                    'amount' => FinanceReliabilityPolicy::AMOUNT_EXTREME,
                    'direction' => 'client_receipt',
                ],
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Create invoice payment with persistent invalid state',
                    'subject_type' => 'invoice',
                    'subject_id' => $subjectId,
                    'event_type' => 'finance.invoice.payment_created',
                    'post_write_validation' => true,
                    'payload' => fn(array $result): array => $result,
                ],
            );

            $this->fail('Persistent invalid finance state should trigger quarantine rollback.');
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
    public function finance_retry_attempts_scale_with_transaction_amount(): void
    {
        $operationKey = 'finance-retry-scale-' . Str::uuid();

        $result = (new FinanceOperationService())->run(
            'finance.invoice.payment.create',
            fn(): array => [
                'invoice_id' => 'invoice-retry-' . Str::uuid(),
                'payment_id' => 'payment-retry-' . Str::uuid(),
                'account_id' => 'account-retry-' . Str::uuid(),
                'amount' => FinanceReliabilityPolicy::AMOUNT_EXTREME,
                'direction' => 'client_receipt',
            ],
            [
                'operation_key' => $operationKey,
                'summary' => 'Create high amount invoice payment',
                'subject_type' => 'invoice',
                'subject_id' => 'invoice-retry-' . Str::uuid(),
                'event_type' => 'finance.invoice.payment_created',
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $outbox = $result->outboxMessage();

        $this->assertNotNull($outbox);
        $this->assertSame(6, $outbox?->max_attempts);
        $this->assertSame(6, data_get($outbox?->metadata, 'retry.max_attempts'));
        $this->assertSame('extreme', data_get($outbox?->metadata, 'finance_reliability.amount_tier'));
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
