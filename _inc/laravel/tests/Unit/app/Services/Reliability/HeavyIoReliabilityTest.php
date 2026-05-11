<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationQuarantine, OperationalEvent, OutboxMessage};
use App\Services\Reliability\{
    HeavyIoCompensationService,
    HeavyIoOperationService,
    HeavyIoOutboxDispatcher,
    HeavyIoPostWriteValidator,
    HeavyIoReliabilityAssessment,
    HeavyIoReliabilityPolicy,
    QuarantineRemediationJudge,
    QuarantineService,
    ReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

#[CoversClass(HeavyIoCompensationService::class)]
#[CoversClass(HeavyIoOperationService::class)]
#[CoversClass(HeavyIoOutboxDispatcher::class)]
#[CoversClass(HeavyIoPostWriteValidator::class)]
#[CoversClass(HeavyIoReliabilityAssessment::class)]
#[CoversClass(HeavyIoReliabilityPolicy::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class HeavyIoReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    #[Test]
    public function python_import_records_ledger_outbox_and_dispatches_completion_signals(): void
    {
        $operationKey = 'heavy-io-import-' . Str::uuid();
        $result = (new HeavyIoOperationService())->runPythonImport(
            'CustomerImport',
            [
                'rows' => [
                    ['name' => 'Acme', 'email' => 'acme@example.test'],
                    ['name' => 'Beta', 'email' => 'beta@example.test'],
                ],
                'fields' => ['name', 'email'],
            ],
            fn(): array => [
                'status' => 'success',
                'imported' => 2,
                'skipped' => 0,
                'errors' => [],
                'rows' => [],
            ],
            ['operation_key' => $operationKey],
        );

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $outbox = $ledger->outboxMessages()->where('stream', 'heavy_io.operations')->first();

        $this->assertSame('success', $result['status']);
        $this->assertSame('heavy_io', $ledger->domain);
        $this->assertSame(ReliabilityPolicy::STATUS_COMMITTED, $ledger->status);
        $this->assertInstanceOf(OutboxMessage::class, $outbox);
        $this->assertSame('heavy_io.python_import.completed', $outbox?->event_type);
        $this->assertSame('python_import', data_get($outbox?->metadata, 'heavy_io_reliability.cluster'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'heavy_io.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);

        $dispatch = (new HeavyIoOutboxDispatcher())->dispatchMessage($outbox);

        $this->assertSame('dispatched', $dispatch['status']);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->fresh()?->status);
        $this->assertTrue(OperationalEvent::where('event_type', 'heavy_io.signal.accepted')->exists());
    }

    #[Test]
    public function python_export_empty_output_is_retried_before_success(): void
    {
        $attempts = 0;
        $operationKey = 'heavy-io-export-' . Str::uuid();

        $result = (new HeavyIoOperationService())->runPythonExport(
            'InvoiceExport',
            ['rows' => [['invoice' => 'INV-1']]],
            function () use (&$attempts): string {
                $attempts++;

                return $attempts === 1 ? '' : 'generated invoice report';
            },
            [
                'operation_key' => $operationKey,
                'max_attempts' => 2,
            ],
        );

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();

        $this->assertSame('generated invoice report', $result);
        $this->assertSame(2, $attempts);
        $this->assertSame(ReliabilityPolicy::STATUS_COMMITTED, $ledger->status);
        $this->assertTrue(OperationalEvent::where('event_type', 'reliability.retry.retrying')->where('channel', 'heavy_io')->exists());
    }

    #[Test]
    public function webhook_delivery_failure_preserves_false_return_and_marks_failed_ledger(): void
    {
        $attempts = 0;
        $operationKey = 'heavy-io-webhook-' . Str::uuid();

        $result = (new HeavyIoOperationService())->runWebhook(
            'https://subscriber.example.test/hook',
            ['payload' => 'value'],
            'POST',
            function () use (&$attempts): bool {
                $attempts++;

                return false;
            },
            [
                'operation_key' => $operationKey,
                'max_attempts' => 2,
            ],
        );

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();

        $this->assertFalse($result);
        $this->assertSame(2, $attempts);
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertSame(0, $ledger->outboxMessages()->count());
    }

    #[Test]
    public function persistent_high_impact_import_failures_route_to_heavy_io_quarantine(): void
    {
        $operationKey = 'heavy-io-quarantine-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'heavy-io-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'heavy_io.python_import',
                'domain' => 'heavy_io',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'summary' => 'Prior failed customer import',
                'started_at' => now()->subMinutes(15),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        $result = (new HeavyIoOperationService())->runPythonImport(
            'CustomerImport',
            [
                'rows' => [
                    ['name' => 'Corrupt', 'email' => 'bad@example.test'],
                ],
                'fields' => ['name', 'email'],
            ],
            fn(): array => [
                'status' => 'error',
                'errors' => ['Imported customer payload could not be reconciled after write.'],
                'rows' => [],
            ],
            [
                'operation_key' => $operationKey,
                'max_attempts' => 1,
            ],
        );

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();

        $this->assertSame('error', $result['status']);
        $this->assertSame(ReliabilityPolicy::STATUS_FAILED, $ledger->status);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_OPERATION_LEDGERS,
            'source_record_id' => (string) $ledger->id,
            'domain' => 'heavy_io',
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
        ]);
        $this->assertTrue(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }
}

