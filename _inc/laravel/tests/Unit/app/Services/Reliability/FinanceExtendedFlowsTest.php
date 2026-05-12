<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{BankAccount, BankTransfer, OperationLedger, OutboxMessage};
use App\Services\Reliability\{
    FinanceOutboxDispatcher,
    FinancePostWriteValidator,
    FinanceReliabilityPolicy,
    ReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

#[CoversClass(FinanceOutboxDispatcher::class)]
#[CoversClass(FinancePostWriteValidator::class)]
#[CoversClass(FinanceReliabilityPolicy::class)]
#[Group('services')]
#[Group('reliability')]
class FinanceExtendedFlowsTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function finance_policy_uses_extended_accounting_amount_keys(): void
    {
        $assessment = (new FinanceReliabilityPolicy())->assess('finance.journal_entry.created', [
            'total_debit' => 5000,
            'total_credit' => 5000,
            'direction' => 'journal_entry',
        ]);

        $this->assertSame(5000.0, $assessment->amount);
        $this->assertSame('validated', $assessment->amountTier);
        $this->assertTrue($assessment->postWriteValidationRequired);
        $this->assertSame(4, $assessment->maxAttempts);
        $this->assertSame(1, $assessment->signals['metadata_risk_score']);
    }

    #[Test]
    public function finance_validator_accepts_soft_deleted_bank_transfer_reversal(): void
    {
        $from = BankAccount::factory()->create();
        $to = BankAccount::factory()->create();

        $transfer = BankTransfer::create([
            'from_account' => $from->id,
            'to_account' => $to->id,
            'amount' => 5000,
            'payment_method' => 0,
        ]);

        $payload = [
            'bank_transfer_id' => (string) $transfer->id,
            'from_account' => (string) $from->id,
            'to_account' => (string) $to->id,
            'amount' => 5000,
            'direction' => 'bank_transfer_reversal',
        ];

        $transfer->delete();

        $result = (new FinancePostWriteValidator())->validate('finance.bank_transfer.deleted', $payload);

        $this->assertTrue($result->passed);
        $this->assertSame(DC::TABLE_BNK_TRF, $result->sourceTable);
        $this->assertSame((string) $transfer->id, $result->sourceRecordId);
    }

    #[Test]
    public function finance_dispatcher_emits_accounting_signals_for_journal_events(): void
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'finance-journal-' . Str::uuid(),
            'operation_type' => 'finance.journal_entry.create',
            'domain' => 'finance',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create([
            'message_key' => 'finance.journal_entry.created:' . Str::uuid(),
            'stream' => 'finance.ledger',
            'event_type' => 'finance.journal_entry.created',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => 'journal-entry',
            'aggregate_id' => 'journal-entry-id',
            'operation_ledger_id' => $ledger->id,
            'payload' => ['amount' => 5000],
            'max_attempts' => 2,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $report = (new FinanceOutboxDispatcher())->dispatchMessage($message);
        $signalNames = collect($report['signals'] ?? [])->pluck('name')->all();

        $this->assertSame('dispatched', $report['status']);
        $this->assertContains('journal-control', $signalNames);
        $this->assertContains('accounting-reconciliation', $signalNames);
        $this->assertContains('webhook', $signalNames);
    }
}
