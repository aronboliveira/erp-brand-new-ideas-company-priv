<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Exceptions\Reliability\QuarantineRollbackRequiredException;
use App\Models\{
    CircuitBreakerCall,
    CircuitBreakerState,
    Milestone,
    OperationLedger,
    OperationQuarantine,
    OutboxMessage,
    Project,
    ProjectTask
};
use App\Services\Reliability\{
    PlanningCompensationService,
    PlanningOperationService,
    PlanningOutboxDispatcher,
    PlanningPostWriteValidator,
    PlanningReliabilityAssessment,
    PlanningReliabilityPolicy,
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

#[CoversClass(PlanningCompensationService::class)]
#[CoversClass(PlanningOperationService::class)]
#[CoversClass(PlanningOutboxDispatcher::class)]
#[CoversClass(PlanningPostWriteValidator::class)]
#[CoversClass(PlanningReliabilityAssessment::class)]
#[CoversClass(PlanningReliabilityPolicy::class)]
#[CoversClass(PostWriteValidationResult::class)]
#[CoversClass(QuarantineRemediationJudge::class)]
#[CoversClass(QuarantineService::class)]
#[Group('services')]
#[Group('reliability')]
class PlanningReliabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    #[Test]
    public function project_final_status_creates_planning_ledger_outbox_and_validation_step(): void
    {
        $project = $this->project(['budget' => 75000, 'status' => 'in_progress']);

        $result = (new PlanningOperationService())->run(
            'planning.project.status_change',
            function () use ($project): array {
                $project->forceFill(['status' => 'complete'])->save();

                return [
                    'project_id' => (string) $project->id,
                    'expected_status' => 'complete',
                    'project_budget' => 75000,
                    'final_state' => true,
                ];
            },
            [
                'summary' => 'Finalize project',
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'event_type' => 'planning.project.status_changed',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $ledger = $result->ledger();
        $outbox = $result->outboxMessage();

        $this->assertInstanceOf(OperationLedger::class, $ledger);
        $this->assertInstanceOf(OutboxMessage::class, $outbox);
        $this->assertSame('planning', $ledger?->domain);
        $this->assertSame('planning.operations', $outbox?->stream);
        $this->assertSame('project_final_status', data_get($outbox?->metadata, 'planning_reliability.cluster'));
        $this->assertGreaterThanOrEqual(4, (int) data_get($outbox?->metadata, 'retry.max_attempts'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $ledger?->id,
            'step_key' => 'planning.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function milestone_validator_accepts_final_progress_and_status(): void
    {
        $project = $this->project();
        $milestone = $this->milestone((string) $project->id, [
            'status' => 'completed',
            'progress' => 100,
            'cost' => 6000,
        ]);

        $validation = (new PlanningPostWriteValidator())->validate('planning.milestone.finalized', [
            'milestone_id' => (string) $milestone->id,
            'project_id' => (string) $project->id,
            'expected_status' => 'completed',
            'expected_progress' => 100,
            'milestone_cost' => 6000,
        ]);

        $this->assertTrue($validation->passed);
        $this->assertSame('planning', $validation->domain);
    }

    #[Test]
    public function task_completion_operation_creates_planning_outbox(): void
    {
        $project = $this->project();
        $task = $this->task((string) $project->id, ['progress' => 40, 'status' => 'in_progress']);

        $result = (new PlanningOperationService())->run(
            'planning.task.complete',
            function () use ($task): array {
                $task->forceFill([
                    'progress' => 100,
                    'status' => 'completed',
                    PJC::COL_IS_CP => true,
                    PJC::COL_M_AT => now()->toDateString(),
                ])->save();
                $task->refresh();

                return [
                    'task_id' => (string) $task->id,
                    'project_id' => (string) $task->project_id,
                    'expected_progress' => 100,
                    'expected_status' => 'completed',
                    'expected_is_complete' => true,
                    'priority' => 'high',
                ];
            },
            [
                'summary' => 'Complete planning task',
                'subject_type' => ProjectTask::class,
                'subject_id' => (string) $task->id,
                'event_type' => 'planning.task.completed',
                'post_write_validation' => true,
                'payload' => fn(array $payload): array => $payload,
            ],
        );

        $this->assertSame('task_final_state', data_get($result->outboxMessage()?->metadata, 'planning_reliability.cluster'));
        $this->assertDatabaseHas(DC::TABLE_OPERATION_STEPS, [
            'operation_ledger_id' => $result->ledger()?->id,
            'step_key' => 'planning.post_write_validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
        ]);
    }

    #[Test]
    public function persistent_project_finalization_corruption_routes_to_planning_quarantine_manual_review(): void
    {
        $project = $this->project(['status' => 'in_progress', 'budget' => 175000]);
        $operationKey = 'planning-quarantine-' . Str::uuid();

        for ($i = 0; $i < 4; $i++) {
            OperationLedger::create([
                'operation_key' => 'planning-prior-failure-' . $i . '-' . Str::uuid(),
                'operation_type' => 'planning.project.status_change',
                'domain' => 'planning',
                'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'summary' => 'Prior failed project finalization reconciliation attempt',
                'started_at' => now()->subMinutes(20),
                'failed_at' => now()->subMinutes(10),
                'expires_at' => now()->addDay(),
            ]);
        }

        try {
            (new PlanningOperationService())->run(
                'planning.project.status_change',
                function () use ($project): array {
                    $project->forceFill(['status' => 'in_progress'])->save();

                    return [
                        'project_id' => (string) $project->id,
                        'expected_status' => 'complete',
                        'project_budget' => 175000,
                        'final_state' => true,
                    ];
                },
                [
                    'operation_key' => $operationKey,
                    'summary' => 'Finalize project with persistent corrupted state',
                    'subject_type' => Project::class,
                    'subject_id' => (string) $project->id,
                    'event_type' => 'planning.project.status_changed',
                    'post_write_validation' => true,
                    'payload' => fn(array $payload): array => $payload,
                ],
            );

            $this->fail('Persistent planning corruption should route to quarantine.');
        } catch (QuarantineRollbackRequiredException $exception) {
            $this->assertNotNull($exception->quarantine());
            $this->assertSame('planning', $exception->quarantine()?->domain);
            $this->assertSame(ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW, $exception->quarantine()?->status);
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->firstOrFail();
        $this->assertDatabaseHas(DC::TABLE_OPERATION_QUARANTINES, [
            'operation_ledger_id' => $ledger->id,
            'source_table' => DC::TABLE_PROJECTS,
            'source_record_id' => (string) $project->id,
            'domain' => 'planning',
            'status' => ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            'remediation_decision' => ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
        ]);
        $this->assertTrue(OperationQuarantine::where('operation_ledger_id', $ledger->id)->exists());
    }

    #[Test]
    public function planning_outbox_dispatcher_accepts_projection_and_webhook_signals(): void
    {
        [$ledger, $message] = $this->planningOutbox('planning.project.deleted');

        $report = (new PlanningOutboxDispatcher())->dispatchMessage($message);

        $message->refresh();
        $ledger->refresh();

        $this->assertSame('dispatched', $report['status']);
        $this->assertSame(ReliabilityPolicy::OUTBOX_DISPATCHED, $message->status);
        $this->assertSame(ReliabilityPolicy::STATUS_CLOSED, $ledger->status);
        $this->assertGreaterThanOrEqual(5, count($report['signals']));
        $this->assertTrue(CircuitBreakerState::where('breaker_key', 'planning.outbox.planning.project.deleted')->exists());
        $this->assertTrue(CircuitBreakerCall::where('breaker_key', 'planning.outbox.planning.project.deleted')
            ->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)
            ->exists());
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'operation_ledger_id' => $ledger->id,
            'outbox_message_id' => $message->id,
            'event_type' => 'planning.outbox.dispatched',
        ]);
    }

    #[Test]
    public function planning_outbox_dispatcher_dead_letters_and_marks_compensation_required(): void
    {
        [$ledger, $message] = $this->planningOutbox('planning.task.completed', ['max_attempts' => 1]);

        $report = (new PlanningOutboxDispatcher(handlers: [
            'planning.task.completed' => fn(): array => throw new RuntimeException('task projection unavailable'),
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
            'event_type' => 'planning.compensation.required',
            'severity' => 'error',
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function project(array $overrides = []): Project
    {
        return Project::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            PJC::COL_NM => 'Planning Project ' . Str::uuid(),
            PJC::COL_S_DT => now()->toDateString(),
            PJC::COL_E_DT => now()->addMonth()->toDateString(),
            PJC::COL_CLIENT_ID => (string) Str::uuid(),
            PJC::COL_BUDGET => 1000,
            PJC::COL_STATUS => 'in_progress',
            PJC::COL_E_HRS => 10,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function milestone(string $projectId, array $overrides = []): Milestone
    {
        return Milestone::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            PJC::COL_PJ_ID => $projectId,
            'title' => 'Planning Milestone ' . Str::uuid(),
            'status' => 'pending',
            'progress' => 0,
            'cost' => 1000,
            PJC::COL_S_DT => now()->toDateString(),
            PJC::COL_D_DATE => now()->addWeek()->toDateString(),
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function task(string $projectId, array $overrides = []): ProjectTask
    {
        return ProjectTask::forceCreate(array_merge([
            'id' => (string) Str::uuid(),
            PJC::COL_PJ_ID => $projectId,
            PJC::COL_NM => 'Planning Task ' . Str::uuid(),
            PJC::COL_E_HRS => 4,
            'priority' => 'medium',
            PJC::COL_PGR => 0,
            'status' => 'not_started',
            PJC::COL_IS_CP => false,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ], $overrides));
    }

    /**
     * @return array{0: OperationLedger, 1: OutboxMessage}
     */
    private function planningOutbox(string $eventType, array $messageOverrides = []): array
    {
        $ledger = OperationLedger::create([
            'operation_key' => 'planning-test-' . Str::uuid(),
            'operation_type' => 'planning.test.operation',
            'domain' => 'planning',
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'status' => ReliabilityPolicy::STATUS_COMMITTED,
            'started_at' => now(),
            'committed_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $message = OutboxMessage::create(array_merge([
            'message_key' => $eventType . ':' . Str::uuid(),
            'stream' => 'planning.operations',
            'event_type' => $eventType,
            'criticality' => ReliabilityPolicy::CRITICALITY_CRITICAL,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => Project::class,
            'aggregate_id' => (string) Str::uuid(),
            'operation_ledger_id' => $ledger->id,
            'payload' => ['project_id' => (string) Str::uuid()],
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
