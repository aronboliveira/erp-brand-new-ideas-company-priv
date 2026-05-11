<?php

namespace App\Services\Reliability;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Models\{Milestone, OperationLedger, Project, ProjectTask, ProjectUser};
use Illuminate\Support\Facades\DB;

class PlanningPostWriteValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(string $eventType, array $payload, ?OperationLedger $ledger = null): PostWriteValidationResult
    {
        return match ($eventType) {
            'planning.project.updated',
            'planning.project.status_changed' => $this->validateProjectPresent($eventType, $payload, $ledger),
            'planning.project.deleted' => $this->validateProjectDeleted($payload, $ledger),
            'planning.milestone.updated',
            'planning.milestone.finalized' => $this->validateMilestonePresent($eventType, $payload, $ledger),
            'planning.milestone.deleted' => $this->validateMilestoneDeleted($payload, $ledger),
            'planning.task.completed',
            'planning.task.progress_finalized' => $this->validateTaskPresent($eventType, $payload, $ledger),
            'planning.task.deleted' => $this->validateTaskDeleted($payload, $ledger),
            default => PostWriteValidationResult::pass(
                'planning',
                (string) ($payload['source_table'] ?? 'planning'),
                null,
                isset($payload['id']) ? (string) $payload['id'] : null,
                $payload,
                $this->originEvent($eventType, $payload, $ledger),
            ),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateProjectPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $projectId = $this->stringOrNull($payload['project_id'] ?? $payload['id'] ?? null);
        $project = $projectId ? Project::query()->find($projectId) : null;
        $errors = [];

        if (!$project) {
            $errors['project'] = 'Project was not persisted.';
        } else {
            $this->validateProjectCore($project, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PROJECTS,
            Project::class,
            $projectId,
            [
                'payload' => $payload,
                'project' => $project?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateProjectDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $projectId = $this->stringOrNull($payload['project_id'] ?? $payload['id'] ?? null);
        $project = $projectId ? Project::query()->find($projectId) : null;
        $tasks = $projectId ? ProjectTask::query()->where(PJC::COL_PJ_ID, $projectId)->count() : 0;
        $milestones = $projectId ? Milestone::query()->where(PJC::COL_PJ_ID, $projectId)->count() : 0;
        $users = $projectId ? ProjectUser::query()->where(PJC::COL_PJ_ID, $projectId)->count() : 0;
        $errors = [];

        if ($project) {
            $errors['project_delete'] = 'Project still exists after delete operation.';
        }
        if ($tasks > 0) {
            $errors['task_project'] = 'Project tasks still reference the deleted project.';
        }
        if ($milestones > 0) {
            $errors['milestone_project'] = 'Milestones still reference the deleted project.';
        }
        if ($users > 0) {
            $errors['project_user_link'] = 'Project user rows still reference the deleted project.';
        }

        return $this->result(
            $errors,
            DC::TABLE_PROJECTS,
            Project::class,
            $projectId,
            [
                'payload' => $payload,
                'project_exists_after_delete' => (bool) $project,
                'tasks_after_delete' => $tasks,
                'milestones_after_delete' => $milestones,
                'project_users_after_delete' => $users,
            ],
            $this->originEvent('planning.project.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateMilestonePresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $milestoneId = $this->stringOrNull($payload['milestone_id'] ?? $payload['id'] ?? null);
        $milestone = $milestoneId ? Milestone::query()->find($milestoneId) : null;
        $errors = [];

        if (!$milestone) {
            $errors['milestone'] = 'Milestone was not persisted.';
        } else {
            $this->validateMilestoneCore($milestone, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_MSS,
            Milestone::class,
            $milestoneId,
            [
                'payload' => $payload,
                'milestone' => $milestone?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateMilestoneDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $milestoneId = $this->stringOrNull($payload['milestone_id'] ?? $payload['id'] ?? null);
        $milestone = $milestoneId ? Milestone::query()->find($milestoneId) : null;
        $tasks = $milestoneId ? ProjectTask::query()->where(PJC::COL_ML_ID, $milestoneId)->count() : 0;
        $errors = [];

        if ($milestone) {
            $errors['milestone_delete'] = 'Milestone still exists after delete operation.';
        }
        if ($tasks > 0) {
            $errors['task_project'] = 'Project tasks still reference the deleted milestone.';
        }

        return $this->result(
            $errors,
            DC::TABLE_MSS,
            Milestone::class,
            $milestoneId,
            [
                'payload' => $payload,
                'milestone_exists_after_delete' => (bool) $milestone,
                'tasks_referencing_milestone_after_delete' => $tasks,
            ],
            $this->originEvent('planning.milestone.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTaskPresent(string $eventType, array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $taskId = $this->stringOrNull($payload['task_id'] ?? $payload['id'] ?? null);
        $task = $taskId ? ProjectTask::query()->find($taskId) : null;
        $errors = [];

        if (!$task) {
            $errors['task'] = 'Project task was not persisted.';
        } else {
            $this->validateTaskCore($task, $payload, $errors);
        }

        return $this->result(
            $errors,
            DC::TABLE_PROJ_TSKS,
            ProjectTask::class,
            $taskId,
            [
                'payload' => $payload,
                'task' => $task?->getAttributes(),
            ],
            $this->originEvent($eventType, $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTaskDeleted(array $payload, ?OperationLedger $ledger): PostWriteValidationResult
    {
        $taskId = $this->stringOrNull($payload['task_id'] ?? $payload['id'] ?? null);
        $task = $taskId ? ProjectTask::query()->find($taskId) : null;
        $files = $taskId ? DB::table(DC::TABLE_TSK_FL)->where(AC::COL_TSK_ID, $taskId)->count() : 0;
        $comments = $taskId ? DB::table(DC::TABLE_TSK_CMT)->where(AC::COL_TSK_ID, $taskId)->count() : 0;
        $checklists = $taskId ? DB::table(DC::TABLE_TSK_CHKL)->where(AC::COL_TSK_ID, $taskId)->count() : 0;
        $timesheets = $taskId ? DB::table(DC::TABLE_TMS)->where(AC::COL_TSK_ID, $taskId)->count() : 0;
        $errors = [];

        if ($task) {
            $errors['task_delete'] = 'Project task still exists after delete operation.';
        }
        if ($files > 0 || $comments > 0 || $checklists > 0 || $timesheets > 0) {
            $errors['task_delete_children'] = 'Task child rows still reference the deleted task.';
        }

        return $this->result(
            $errors,
            DC::TABLE_PROJ_TSKS,
            ProjectTask::class,
            $taskId,
            [
                'payload' => $payload,
                'task_exists_after_delete' => (bool) $task,
                'task_files_after_delete' => $files,
                'task_comments_after_delete' => $comments,
                'task_checklists_after_delete' => $checklists,
                'timesheets_after_delete' => $timesheets,
            ],
            $this->originEvent('planning.task.deleted', $payload, $ledger),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateProjectCore(Project $project, array $payload, array &$errors): void
    {
        if (trim((string) $project->getAttribute(PJC::COL_NM)) === '') {
            $errors['project'] = 'Project name is empty after persistence.';
        }

        $expectedStatus = $this->stringOrNull($payload['expected_status'] ?? $payload['status'] ?? null);
        if ($expectedStatus !== null && !$this->sameStatus($project->getAttribute(PJC::COL_STATUS), $expectedStatus)) {
            $errors['project_status'] = 'Project status does not match the expected final state.';
        }

        $expectedBudget = $payload['expected_budget'] ?? $payload['project_budget'] ?? $payload['budget'] ?? null;
        if (is_numeric($expectedBudget) && abs((float) $project->getAttribute(PJC::COL_BUDGET) - (float) $expectedBudget) > 0.01) {
            $errors['project_budget'] = 'Project budget does not match the expected persisted value.';
        }

        $expectedClientId = $this->stringOrNull($payload['expected_client_id'] ?? null);
        if ($expectedClientId !== null && (string) $project->getAttribute(PJC::COL_CLIENT_ID) !== $expectedClientId) {
            $errors['project_client'] = 'Project client link does not match the expected value.';
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateMilestoneCore(Milestone $milestone, array $payload, array &$errors): void
    {
        if (trim((string) $milestone->getAttribute('title')) === '') {
            $errors['milestone'] = 'Milestone title is empty after persistence.';
        }
        if (!$milestone->getAttribute(PJC::COL_PJ_ID) || !Project::query()->whereKey($milestone->getAttribute(PJC::COL_PJ_ID))->exists()) {
            $errors['milestone_project'] = 'Milestone project link is missing or invalid.';
        }

        $expectedStatus = $this->stringOrNull($payload['expected_status'] ?? $payload['status'] ?? null);
        if ($expectedStatus !== null && !$this->sameStatus($milestone->getAttribute('status'), $expectedStatus)) {
            $errors['milestone_status'] = 'Milestone status does not match the expected state.';
        }

        $expectedProgress = $payload['expected_progress'] ?? $payload['progress'] ?? null;
        if (is_numeric($expectedProgress) && abs((float) $milestone->getAttribute('progress') - (float) $expectedProgress) > 0.01) {
            $errors['milestone_progress'] = 'Milestone progress does not match the expected value.';
        }

        if ((float) $milestone->getAttribute('progress') < 0.0 || (float) $milestone->getAttribute('progress') > 100.0) {
            $errors['milestone_progress'] = 'Milestone progress is outside 0-100 after persistence.';
        }
        if ((float) $milestone->getAttribute('cost') < 0.0) {
            $errors['milestone_cost'] = 'Milestone cost is negative after persistence.';
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $errors
     */
    private function validateTaskCore(ProjectTask $task, array $payload, array &$errors): void
    {
        if (trim((string) $task->getAttribute(PJC::COL_NM)) === '') {
            $errors['task'] = 'Project task name is empty after persistence.';
        }
        if (!$task->getAttribute(PJC::COL_PJ_ID) || !Project::query()->whereKey($task->getAttribute(PJC::COL_PJ_ID))->exists()) {
            $errors['task_project'] = 'Project task link is missing or invalid.';
        }

        $expectedProgress = $payload['expected_progress'] ?? $payload['progress'] ?? null;
        if (is_numeric($expectedProgress) && abs((float) $task->getAttribute(PJC::COL_PGR) - (float) $expectedProgress) > 0.01) {
            $errors['task_progress'] = 'Project task progress does not match the expected value.';
        }

        $expectsComplete = (bool) ($payload['expected_is_complete'] ?? $payload['is_complete'] ?? false);
        if ($expectsComplete && !$this->truthy($task->getAttribute(PJC::COL_IS_CP))) {
            $errors['task_completion'] = 'Project task was not marked complete.';
        }

        $expectedStatus = $this->stringOrNull($payload['expected_status'] ?? $payload['status'] ?? null);
        if ($expectedStatus !== null && !$this->sameStatus($task->getAttribute('status'), $expectedStatus)) {
            $errors['task_status'] = 'Project task status does not match the expected state.';
        }
    }

    /**
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $snapshot
     * @param array<string, mixed> $originEvent
     */
    private function result(
        array $errors,
        string $sourceTable,
        ?string $sourceType,
        ?string $sourceRecordId,
        array $snapshot,
        array $originEvent,
    ): PostWriteValidationResult {
        if ($errors === []) {
            return PostWriteValidationResult::pass(
                'planning',
                $sourceTable,
                $sourceType,
                $sourceRecordId,
                $snapshot,
                $originEvent,
                ReliabilityPolicy::CRITICALITY_HIGH,
            );
        }

        return PostWriteValidationResult::fail(
            'planning',
            $sourceTable,
            $sourceType,
            $sourceRecordId,
            array_keys($errors),
            $errors,
            $snapshot,
            $originEvent,
            ReliabilityPolicy::CRITICALITY_HIGH,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function originEvent(string $eventType, array $payload, ?OperationLedger $ledger): array
    {
        return [
            'event_type' => $eventType,
            'operation_key' => $ledger?->operation_key,
            'operation_type' => $ledger?->operation_type,
            'payload' => $payload,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function sameStatus(mixed $actual, mixed $expected): bool
    {
        $normalize = static fn(mixed $value): string => str_replace([' ', '-'], '_', strtolower(trim((string) $value)));

        $actualStatus = $normalize($actual);
        $expectedStatus = $normalize($expected);
        $aliases = [
            'complete' => 'completed',
            'canceled' => 'cancelled',
        ];

        return ($aliases[$actualStatus] ?? $actualStatus) === ($aliases[$expectedStatus] ?? $expectedStatus);
    }

    private function truthy(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'yes'], true);
    }
}
