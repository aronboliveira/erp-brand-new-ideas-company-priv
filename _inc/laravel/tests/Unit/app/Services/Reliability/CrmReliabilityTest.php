<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Exceptions\Reliability\QuarantineRollbackRequiredException;
use App\Models\{
    CircuitBreakerCall,
    CircuitBreakerState,
    ClientDeal,
    Deal,
    Lead,
    LeadStage,
    OperationLedger,
    OperationQuarantine,
    OutboxMessage,
    Pipeline,
    Stage,
    User
};
use App\Services\Reliability\{
    CrmCompensationService,
    CrmOperationService,
    CrmOutboxDispatcher,
    CrmPostWriteValidator,
    CrmReliabilityAssessment,
    CrmReliabilityPolicy,
    PostWriteValidationResult,
    QuarantineRemediationJudge,
    QuarantineService,
    ReliabilityPolicy
};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;

#[CoversClass(CrmCompensationService::class)]
#[CoversClass(CrmOperationService::class)]
#[CoversClass(CrmOutboxDispatcher::class)]
#[CoversClass(CrmPostWriteValidator::class)]
#[CoversClass(CrmReliabilityAssessment::class)]
#[CoversClass(CrmReliabilityPolicy::class)]
#[CoversClass(PostWriteValidationResult::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class CrmReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    #[Test]
    public function deal_operation_creates_crm_ledger_outbox_and_validation_step(): void
    {
        $pipeline = $this->pipeline();
        $stage = $this->stage((string) $pipeline->id);
        $client = $this->user('client');
        $dealId = (string) Str::uuid();

        $result = (new CrmOperationService())->run(
            'crm.deal.create',
            function () use ($dealId, $pipeline, $stage, $client): array {
                $deal = $this->deal([
                    'id' => $dealId,
                    PJC::COL_PPL_ID => $pipeline->id,
                    PJC::COL_STG_ID => $stage->id,
                    'price' => 125000,
                ]);
                ClientDeal::create([
                    PJC::COL_DL_ID => $deal->id,
                    PJC::COL_CLIENT_ID => $client->id,
                ]);

                return [
                    'deal_id' => (string) $deal->id,
                    'client_id' => (string) $client->id,
                    'expected_client_id' => (string) $client->id,
                    'expected_stage_id' => (string) $stage->id,
                    'price' => 125000,
                ];
            },
            [
                'summary' => 'Create CRM deal',
                'subject_type' => Deal::class,
                'subject_id' => $dealId,
                'event_type' => 'crm.deal.created',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $ledger = $result->ledger();
        $outbox = $result->outboxMessage();

        $this->assertInstanceOf(OperationLedger::class, $ledger);
        $this->assertInstanceOf(OutboxMessage::class, $outbox);
        $this->assertSame('crm', $ledger?->domain);
        $this->assertSame('crm.operations', $outbox?->stream);
        $this->assertSame('deal_lifecycle', data_get($outbox?->metadata, 'crm_reliability.cluster'));
        $this->assertGreaterThanOrEqual(4, (int) data_get($outbox?->metadata, 'retry.max_attempts'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger?->id,
            'step_key' => 'crm.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function converted_lead_validator_accepts_deal_and_client_link(): void
    {
        $pipeline = $this->pipeline();
        $leadStage = $this->leadStage((string) $pipeline->id);
        $dealStage = $this->stage((string) $pipeline->id);
        $lead = $this->lead([
            PJC::COL_PPL_ID => $pipeline->id,
            PJC::COL_STG_ID => $leadStage->id,
            PJC::COL_CNV => true,
        ]);
        $deal = $this->deal([
            PJC::COL_PPL_ID => $pipeline->id,
            PJC::COL_STG_ID => $dealStage->id,
            'price' => 42000,
        ]);
        $client = $this->user('client');
        ClientDeal::create([
            PJC::COL_DL_ID => $deal->id,
            PJC::COL_CLIENT_ID => $client->id,
        ]);

        $validation = (new CrmPostWriteValidator())->validate('crm.lead.converted', [
            'lead_id' => (string) $lead->id,
            'deal_id' => (string) $deal->id,
            'client_id' => (string) $client->id,
            'expected_client_id' => (string) $client->id,
            'price' => 42000,
        ]);

        $this->assertTrue($validation->passed);
        $this->assertSame('crm', $validation->domain);
    }

    #[Test]
    public function persistent_conversion_corruption_routes_to_crm_quarantine_manual_review(): void
    {
        $pipeline = $this->pipeline();
        $leadStage = $this->leadStage((string) $pipeline->id);
        $leadId = (string) Str::uuid();
        $dealId = (string) Str::uuid();
        $operationKey = 'crm-quarantine-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'crm-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'crm.lead.convert',
                'domain' => 'crm',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'subject_type' => Lead::class,
                'subject_id' => $leadId,
                'summary' => 'Prior failed CRM conversion reconciliation attempt',
                'started_at' => now()->subMinutes(20),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        try {
            (new CrmOperationService())->run(
                'crm.lead.convert',
                function () use ($leadId, $pipeline, $leadStage, $dealId): array {
                    $this->lead([
                        'id' => $leadId,
                        PJC::COL_PPL_ID => $pipeline->id,
                        PJC::COL_STG_ID => $leadStage->id,
                        PJC::COL_CNV => false,
                    ]);

                    return [
                        'lead_id' => $leadId,
                        'deal_id' => $dealId,
                        'price' => 275000,
                        'creates_client' => true,
                    ];
                },
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Convert lead with persistent corruption',
                    'subject_type' => Lead::class,
                    'subject_id' => $leadId,
                    'event_type' => 'crm.lead.converted',
                    'post_write_validation' => true,
                    'payload' => fn(array $payload): array => $payload,
                ],
            );

            $this->fail('Persistent CRM conversion corruption should route to quarantine.');
        } catch (QuarantineRollbackRequiredException $exception) {
            $this->assertNotNull($exception->quarantine());
            $this->assertSame('crm', $exception->quarantine()?->domain);
            $this->assertSame(ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW, $exception->quarantine()?->status);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_DEALS,
            'source_record_id' => $dealId,
            'domain' => 'crm',
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
        ]);
        $this->assertTrue(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }

    #[Test]
    public function crm_outbox_dispatcher_accepts_projection_and_webhook_signals(): void
    {
        [$ledger, $message] = $this->crmOutbox('crm.lead.converted');

        $report = (new CrmOutboxDispatcher())->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dispatched', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DISPATCHED, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->status);
        $this->assertGreaterThanOrEqual(5, count($report['signals']));
        $this->assertTrue(CircuitBreakerState::where('breaker_key', 'crm.outbox.crm.lead.converted')->exists());
        $this->assertTrue(CircuitBreakerCall::where('breaker_key', 'crm.outbox.crm.lead.converted')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)
            ->exists());
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'crm.outbox.dispatched',
        ]);
    }

    #[Test]
    public function crm_outbox_dispatcher_dead_letters_and_marks_compensation_required(): void
    {
        [$ledger, $message] = $this->crmOutbox('crm.deal.status_changed', ['max_attempts' => 1]);

        $report = (new CrmOutboxDispatcher(handlers: [
            'crm.deal.status_changed' => fn(): array => throw new RuntimeException('pipeline projection unavailable'),
        ]))->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dead_letter', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DEAD_LETTER, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_COMPENSATING, $ledger->status);
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger->id,
            'step_key' => 'compensation.required:' . $message->id,
            'status' => ReliabilityPolicy::STEP_PENDING,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'crm.compensation.required',
            'severity' => 'error',
        ]);
    }

    private function pipeline(): Pipeline
    {
        return Pipeline::create([
            PJC::COL_PPL_NM => 'CRM Pipeline ' . Str::uuid(),
            AC::COL_OD => 1,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    private function stage(string $pipelineId): Stage
    {
        return Stage::create([
            PJC::COL_STG_NM => 'CRM Stage ' . Str::uuid(),
            PJC::COL_PPL_ID => $pipelineId,
            AC::COL_OD => 1,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    private function leadStage(string $pipelineId): LeadStage
    {
        return LeadStage::create([
            PJC::COL_STG_NM => 'CRM Lead Stage ' . Str::uuid(),
            PJC::COL_PPL_ID => $pipelineId,
            AC::COL_OD => 1,
            PJC::COL_EST_CC => 60,
            PJC::COL_CRT => false,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function lead(array $overrides = []): Lead
    {
        return Lead::forceCreate(array_merge([
            'name' => 'CRM Lead ' . Str::uuid(),
            'email' => 'crm-lead-' . Str::uuid() . '@example.test',
            'phone' => (string) random_int(10000000000, 99999999999),
            'subject' => 'CRM lead subject',
            PJC::COL_CNV => false,
            PJC::COL_CRT => false,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function deal(array $overrides = []): Deal
    {
        return Deal::forceCreate(array_merge([
            'name' => 'CRM Deal ' . Str::uuid(),
            'phone' => (string) random_int(10000000000, 99999999999),
            'price' => 1000,
            PJC::COL_GRP_ID => 1,
            'status' => 'Active',
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    private function user(string $type = 'company'): User
    {
        return User::create([
            UC::COL_NM => 'CRM User ' . Str::uuid(),
            UC::COL_EM => 'crm-user-' . Str::uuid() . '@example.test',
            UC::COL_PW => 'secret',
            UC::COL_TP => $type,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ]);
    }

    /**
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function crmOutbox(string $eventType, array $messageOverrides = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'crm-test-' . Str::uuid(),
            'operation_type' => 'crm.test.operation',
            'domain' => 'crm',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create(array_merge([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => 'crm.operations',
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => Deal::class,
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => ['deal_id' => (string) Str::uuid()],
            'metadata' => [
                'retry' => ['eligible' => true, 'max_attempts' => 3],
                'circuit_breaker' => ['eligible' => true],
            ],
            'max_attempts' => 3,
            'available_at' => now(),
            'expires_at' => now()->addDay(),
        ], $messageOverrides));

        return [$ledger, $message];
    }
}
