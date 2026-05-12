<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OutboxMessage};
use Carbon\CarbonInterface;

class PlanningReliabilityPolicy
{
    public const CLUSTER_PROJECT_FINAL_STATUS = 'project_final_status';
    public const CLUSTER_PROJECT_DELETION = 'project_deletion';
    public const CLUSTER_MILESTONE_FINAL_STATE = 'milestone_final_state';
    public const CLUSTER_TASK_FINAL_STATE = 'task_final_state';
    public const CLUSTER_APPROVAL_FINALIZATION = 'approval_finalization';
    public const CLUSTER_TIMESHEET_APPROVAL = 'timesheet_approval_finalization';
    public const CLUSTER_ROUTINE_PLANNING = 'routine_planning';

    private const PROJECT_BUDGET_ELEVATED = 10000.0;
    private const PROJECT_BUDGET_HIGH = 50000.0;
    private const PROJECT_BUDGET_CRITICAL = 150000.0;
    private const MILESTONE_COST_ELEVATED = 5000.0;
    private const MILESTONE_COST_HIGH = 25000.0;
    private const LONG_PROCESSING_SECONDS = 900;
    private const EXTREME_PROCESSING_SECONDS = 2400;
    private const RECENT_FAILURE_WINDOW_MINUTES = 60;
    private const CIRCUIT_INSTABILITY_WINDOW_MINUTES = 60;
    private const PRIOR_FAILURES_FOR_QUARANTINE = 4;
    private const RETRY_FAILURES_FOR_QUARANTINE = 5;
    private const CIRCUIT_EVENTS_FOR_QUARANTINE = 3;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): PlanningReliabilityAssessment
    {
        $cluster = $this->cluster($eventType, $payload, $options);
        $signals = $this->signals($eventType, $payload, $ledger, $options);
        $criticality = ReliabilityPolicy::normalizeCriticality(
            $options['criticality'] ?? $this->criticality($cluster, $payload, $signals)
        );
        $retryEligible = $this->retryEligible($cluster, $criticality, $options);
        $circuitBreakerEligible = $this->circuitBreakerEligible($cluster, $criticality, $options);
        $maxAttempts = $this->maxAttempts($cluster, $criticality, $signals, $options, $retryEligible);
        $postWriteValidation = $this->postWriteValidationRequired($cluster, $eventType, $payload, $options);
        $quarantineCandidate = (bool) $signals['persistent_failure_detected']
            && $this->clusterCanReachQuarantine($cluster, $criticality, $signals);

        return new PlanningReliabilityAssessment(
            $cluster,
            $criticality,
            $maxAttempts,
            $retryEligible,
            $circuitBreakerEligible,
            $postWriteValidation,
            (bool) $signals['persistent_failure_detected'],
            $quarantineCandidate,
            $signals,
            $this->thresholds(),
        );
    }

    public function shouldQuarantine(
        PostWriteValidationResult $validation,
        PlanningReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        return !$validation->passed
            && $assessment->quarantineCandidate
            && $this->hasCorePlanningCorruption($validation)
            && $this->severePersistence($assessment->signals);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
            'project_budget_elevated' => self::PROJECT_BUDGET_ELEVATED,
            'project_budget_high' => self::PROJECT_BUDGET_HIGH,
            'project_budget_critical' => self::PROJECT_BUDGET_CRITICAL,
            'milestone_cost_elevated' => self::MILESTONE_COST_ELEVATED,
            'milestone_cost_high' => self::MILESTONE_COST_HIGH,
            'long_processing_seconds' => self::LONG_PROCESSING_SECONDS,
            'extreme_processing_seconds' => self::EXTREME_PROCESSING_SECONDS,
            'prior_failures_for_quarantine' => self::PRIOR_FAILURES_FOR_QUARANTINE,
            'retry_failures_for_quarantine' => self::RETRY_FAILURES_FOR_QUARANTINE,
            'circuit_events_for_quarantine' => self::CIRCUIT_EVENTS_FOR_QUARANTINE,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function cluster(string $eventType, array $payload, array $options): string
    {
        $explicit = $options['cluster'] ?? $payload['cluster'] ?? null;
        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        $needle = strtolower($eventType . ' ' . (string) ($payload['operation_type'] ?? ''));

        if (preg_match('/project.*(delete|deleted|destroy|destroyed)/', $needle) === 1) {
            return self::CLUSTER_PROJECT_DELETION;
        }
        if (preg_match('/project.*(complete|completed|cancel|cancelled|canceled|close|closed|final|status)/', $needle) === 1) {
            return self::CLUSTER_PROJECT_FINAL_STATUS;
        }
        if (preg_match('/milestone.*(delete|deleted|destroy|destroyed|complete|completed|cancel|cancelled|canceled|close|closed|final|progress|status)/', $needle) === 1) {
            return self::CLUSTER_MILESTONE_FINAL_STATE;
        }
        if (preg_match('/task.*(delete|deleted|destroy|destroyed|complete|completed|progress|final|status)/', $needle) === 1) {
            return self::CLUSTER_TASK_FINAL_STATE;
        }
        if (str_contains($needle, 'timesheet') && (
            preg_match('/approve|approved|reject|rejected|submit|submitted|delete|deleted|destroy|destroyed|final|status/', $needle) === 1
            || (bool) ($payload['approval_action'] ?? $payload['submitted_for_approval'] ?? $payload['payroll_handoff'] ?? $payload['finance_handoff'] ?? false)
            || in_array(strtolower((string) ($payload['status'] ?? $payload['expected_status'] ?? '')), ['pending', 'accept', 'accepted', 'approved', 'decline', 'declined', 'rejected'], true)
        )) {
            return self::CLUSTER_TIMESHEET_APPROVAL;
        }
        if (preg_match('/approval|approve|approved|reject|rejected|submit|submitted|finalize|finalized/', $needle) === 1) {
            return self::CLUSTER_APPROVAL_FINALIZATION;
        }

        return self::CLUSTER_ROUTINE_PLANNING;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     */
    private function criticality(string $cluster, array $payload, array $signals): string
    {
        unset($payload);

        if ((bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            $cluster === self::CLUSTER_PROJECT_DELETION
            || (float) $signals['project_budget'] >= self::PROJECT_BUDGET_CRITICAL
            || ((bool) $signals['irreversible_delete'] && (bool) $signals['final_state'])
        ) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            $cluster === self::CLUSTER_PROJECT_FINAL_STATUS
            || (float) $signals['project_budget'] >= self::PROJECT_BUDGET_HIGH
            || (float) $signals['milestone_cost'] >= self::MILESTONE_COST_HIGH
            || ((bool) $signals['final_state'] && (bool) $signals['high_priority_task'])
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (in_array($cluster, [
            self::CLUSTER_MILESTONE_FINAL_STATE,
            self::CLUSTER_TASK_FINAL_STATE,
            self::CLUSTER_TIMESHEET_APPROVAL,
            self::CLUSTER_APPROVAL_FINALIZATION,
        ], true)) {
            if ($cluster === self::CLUSTER_TIMESHEET_APPROVAL && ((bool) $signals['payroll_handoff'] || (bool) $signals['finance_handoff'])) {
                return ReliabilityPolicy::CRITICALITY_HIGH;
            }

            return ReliabilityPolicy::CRITICALITY_MEDIUM;
        }

        return ReliabilityPolicy::CRITICALITY_LOW;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function retryEligible(string $cluster, string $criticality, array $options): bool
    {
        if (array_key_exists('retry_eligible', $options)) {
            return (bool) $options['retry_eligible'];
        }

        return $cluster !== self::CLUSTER_ROUTINE_PLANNING
            && ReliabilityPolicy::shouldPersist($criticality);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function circuitBreakerEligible(string $cluster, string $criticality, array $options): bool
    {
        if (array_key_exists('circuit_breaker_eligible', $options)) {
            return (bool) $options['circuit_breaker_eligible'];
        }

        return $cluster !== self::CLUSTER_ROUTINE_PLANNING
            && ReliabilityPolicy::circuitBreakerRequired($criticality);
    }

    /**
     * @param array<string, mixed> $signals
     * @param array<string, mixed> $options
     */
    private function maxAttempts(
        string $cluster,
        string $criticality,
        array $signals,
        array $options,
        bool $retryEligible,
    ): int {
        if (isset($options['max_attempts'])) {
            return max(1, min(10, (int) $options['max_attempts']));
        }

        if (!$retryEligible) {
            return 1;
        }

        $attempts = match ($cluster) {
            self::CLUSTER_PROJECT_DELETION, self::CLUSTER_PROJECT_FINAL_STATUS => 3,
            self::CLUSTER_MILESTONE_FINAL_STATE, self::CLUSTER_TASK_FINAL_STATE, self::CLUSTER_TIMESHEET_APPROVAL, self::CLUSTER_APPROVAL_FINALIZATION => 2,
            default => 1,
        };

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            $attempts++;
        }
        if ((float) $signals['project_budget'] >= self::PROJECT_BUDGET_HIGH || (float) $signals['milestone_cost'] >= self::MILESTONE_COST_HIGH) {
            $attempts++;
        }
        if ((bool) $signals['persistent_failure_detected']) {
            $attempts += 2;
        }

        return max(1, min(10, $attempts));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function postWriteValidationRequired(string $cluster, string $eventType, array $payload, array $options): bool
    {
        if ((bool) ($options['force_post_write_validation'] ?? false)) {
            return true;
        }

        if (in_array($cluster, [self::CLUSTER_PROJECT_DELETION, self::CLUSTER_PROJECT_FINAL_STATUS], true)) {
            return true;
        }

        if ($cluster === self::CLUSTER_MILESTONE_FINAL_STATE) {
            return str_contains($eventType, 'deleted')
                || $this->finalState($eventType, $payload)
                || (float) $this->milestoneCost($payload) >= self::MILESTONE_COST_ELEVATED;
        }

        if ($cluster === self::CLUSTER_TASK_FINAL_STATE) {
            return str_contains($eventType, 'deleted')
                || $this->finalState($eventType, $payload)
                || $this->completedTask($payload);
        }

        if ($cluster === self::CLUSTER_TIMESHEET_APPROVAL) {
            return str_contains($eventType, 'deleted')
                || (bool) ($payload['approval_action'] ?? false)
                || (bool) ($payload['payroll_handoff'] ?? false)
                || (bool) ($payload['finance_handoff'] ?? false);
        }

        return $cluster === self::CLUSTER_APPROVAL_FINALIZATION;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function signals(string $eventType, array $payload, ?OperationLedger $ledger, array $options): array
    {
        $signals = [
            'project_budget' => $this->projectBudget($payload),
            'milestone_cost' => $this->milestoneCost($payload),
            'final_state' => $this->finalState($eventType, $payload),
            'irreversible_delete' => preg_match('/delete|deleted|destroy|destroyed/', strtolower($eventType)) === 1,
            'completed_task' => $this->completedTask($payload),
            'high_priority_task' => $this->highPriorityTask($payload),
            'payroll_handoff' => (bool) ($payload['payroll_handoff'] ?? $options['payroll_handoff'] ?? false),
            'finance_handoff' => (bool) ($payload['finance_handoff'] ?? $options['finance_handoff'] ?? false),
            'external_signal' => (bool) ($payload['webhook'] ?? $payload['external_origin'] ?? $options['external_origin'] ?? false),
            'prior_failed_operations' => $this->recentFailedOperations($eventType, $payload, $ledger),
            'retry_failures' => $this->recentRetryFailures($eventType, $payload, $ledger),
            'dead_letters' => $this->recentDeadLetters($eventType, $payload, $ledger),
            'circuit_instability_events' => $this->recentCircuitInstability($eventType),
            'elapsed_seconds' => $this->elapsedSeconds($payload, $ledger, $options),
            'metadata_risk_score' => $this->metadataRiskScore($eventType, $payload, $options),
            'persistent_failure_detected' => false,
        ];
        $signals['persistent_failure_detected'] = $this->persistentFailureDetected($signals);

        return $signals;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function persistentFailureDetected(array $signals): bool
    {
        if ((int) $signals['prior_failed_operations'] >= self::PRIOR_FAILURES_FOR_QUARANTINE) {
            return true;
        }
        if ((int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE) {
            return true;
        }
        if ((int) $signals['dead_letters'] > 0) {
            return true;
        }
        if ((int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE) {
            return true;
        }

        return (int) $signals['elapsed_seconds'] >= self::EXTREME_PROCESSING_SECONDS;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function clusterCanReachQuarantine(string $cluster, string $criticality, array $signals): bool
    {
        if (!in_array($cluster, [
            self::CLUSTER_PROJECT_DELETION,
            self::CLUSTER_PROJECT_FINAL_STATUS,
            self::CLUSTER_MILESTONE_FINAL_STATE,
            self::CLUSTER_TASK_FINAL_STATE,
            self::CLUSTER_TIMESHEET_APPROVAL,
            self::CLUSTER_APPROVAL_FINALIZATION,
        ], true)) {
            return false;
        }

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            return true;
        }

        return (int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) $signals['dead_letters'] > 0;
    }

    private function hasCorePlanningCorruption(PostWriteValidationResult $validation): bool
    {
        $coreKeys = [
            'project',
            'project_delete',
            'project_status',
            'project_budget',
            'project_client',
            'milestone',
            'milestone_delete',
            'milestone_project',
            'milestone_status',
            'milestone_progress',
            'milestone_cost',
            'task',
            'task_delete',
            'task_project',
            'task_status',
            'task_progress',
            'task_completion',
            'timesheet',
            'timesheet_delete',
            'timesheet_project',
            'timesheet_task',
            'timesheet_status',
            'timesheet_time',
            'timesheet_approval',
        ];

        return array_intersect($coreKeys, array_keys($validation->validationErrors)) !== [];
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function severePersistence(array $signals): bool
    {
        return (int) $signals['dead_letters'] > 0
            || (int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) $signals['prior_failed_operations'] >= self::PRIOR_FAILURES_FOR_QUARANTINE
            || (
                (int) $signals['elapsed_seconds'] >= self::EXTREME_PROCESSING_SECONDS
                && (
                    (float) $signals['project_budget'] >= self::PROJECT_BUDGET_HIGH
                    || (float) $signals['milestone_cost'] >= self::MILESTONE_COST_HIGH
                    || (bool) $signals['final_state']
                    || (bool) $signals['irreversible_delete']
                )
            );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentFailedOperations(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'planning')
            ->where('status', ReliabilityPolicy::STATUS_FAILED)
            ->where('created_at', '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES));

        $operationType = $ledger?->operation_type ?: $this->operationTypeFromEvent($eventType);
        if ($operationType !== '') {
            $query->where('operation_type', $operationType);
        }

        $subjectId = $ledger?->subject_id ?? $this->subjectId($payload);
        if ($subjectId !== null) {
            $query->where('subject_id', $subjectId);
        } elseif ($ledger?->correlation_id) {
            $query->where('correlation_id', $ledger->correlation_id);
        } else {
            return 0;
        }

        return $query->count();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentRetryFailures(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationalEvent::query()
            ->where('event_type', 'reliability.retry.failed')
            ->where('channel', 'like', 'planning%')
            ->where('occurred_at', '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES));

        $subjectId = $ledger?->subject_id ?? $this->subjectId($payload);
        if ($subjectId !== null) {
            $query->where(function ($inner) use ($subjectId): void {
                $inner->where('subject_id', $subjectId)
                    ->orWhere('context->message_key', 'like', '%' . $subjectId . '%');
            });
        } else {
            $query->where('context->event_type', $eventType);
        }

        return $query->count();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentDeadLetters(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OutboxMessage::query()
            ->where('stream', 'planning.operations')
            ->where('status', ReliabilityPolicy::OUTBOX_DEAD_LETTER)
            ->where(DC::COL_FL_AT, '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES));

        $subjectId = $ledger?->subject_id ?? $this->subjectId($payload);
        if ($subjectId !== null) {
            $query->where(function ($inner) use ($subjectId): void {
                $inner->where('aggregate_id', $subjectId)
                    ->orWhere('message_key', 'like', '%' . $subjectId . '%');
            });
        } else {
            $query->where('event_type', $eventType);
        }

        return $query->count();
    }

    private function recentCircuitInstability(string $eventType): int
    {
        return OperationalEvent::query()
            ->whereIn('event_type', [
                'reliability.circuit.state_changed',
                'reliability.circuit.opened',
                'reliability.circuit.rejected',
            ])
            ->where('channel', 'like', 'planning%')
            ->where('occurred_at', '>=', now()->subMinutes(self::CIRCUIT_INSTABILITY_WINDOW_MINUTES))
            ->where(function ($query) use ($eventType): void {
                $query->where('context->event_type', $eventType)
                    ->orWhere('context->context->event_type', $eventType)
                    ->orWhere('context->breaker_key', 'like', '%' . $this->breakerSegment($eventType) . '%');
            })
            ->count();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function elapsedSeconds(array $payload, ?OperationLedger $ledger, array $options): int
    {
        $explicit = $options['elapsed_seconds']
            ?? $payload['elapsed_seconds']
            ?? $payload['processing_seconds']
            ?? null;

        if (is_numeric($explicit)) {
            return max(0, (int) $explicit);
        }

        $startedAt = $ledger?->started_at;
        if ($startedAt instanceof CarbonInterface) {
            return (int) max(0, $startedAt->diffInSeconds(now()));
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function metadataRiskScore(string $eventType, array $payload, array $options): int
    {
        $score = 0;
        foreach ([
            $payload['high_risk'] ?? null,
            $payload['external_origin'] ?? null,
            $payload['privileged_actor'] ?? null,
            $payload['requires_approval'] ?? null,
            $payload['webhook'] ?? null,
            $options['high_risk'] ?? null,
            $options['external_origin'] ?? null,
            $options['privileged_actor'] ?? null,
            $options['requires_approval'] ?? null,
        ] as $flag) {
            if ((bool) $flag) {
                $score++;
            }
        }

        if (preg_match('/delete|destroy|complete|cancel|close|final|approval|approved|rejected/', strtolower($eventType)) === 1) {
            $score++;
        }

        return min(3, $score);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function subjectId(array $payload): ?string
    {
        $value = $payload['project_id']
            ?? $payload['milestone_id']
            ?? $payload['task_id']
            ?? $payload['timesheet_id']
            ?? $payload['subject_id']
            ?? $payload['id']
            ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function projectBudget(array $payload): float
    {
        $value = $payload['project_budget']
            ?? $payload['budget']
            ?? $payload['amount']
            ?? $payload['total']
            ?? 0;

        return is_numeric($value) ? max(0.0, (float) $value) : 0.0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function milestoneCost(array $payload): float
    {
        $value = $payload['milestone_cost']
            ?? $payload['cost']
            ?? 0;

        return is_numeric($value) ? max(0.0, (float) $value) : 0.0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function finalState(string $eventType, array $payload): bool
    {
        $status = strtolower((string) ($payload['status'] ?? $payload['expected_status'] ?? ''));
        $normalized = str_replace([' ', '-'], '_', $status);

        return preg_match('/complete|completed|cancel|cancelled|canceled|closed|final/', strtolower($eventType)) === 1
            || in_array($normalized, ['complete', 'completed', 'cancelled', 'canceled', 'closed', 'final'], true)
            || (bool) ($payload['final_state'] ?? false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function completedTask(array $payload): bool
    {
        $progress = $payload['progress'] ?? $payload['expected_progress'] ?? null;
        $status = strtolower((string) ($payload['status'] ?? $payload['expected_status'] ?? ''));

        return (is_numeric($progress) && (float) $progress >= 100.0)
            || in_array(str_replace([' ', '-'], '_', $status), ['complete', 'completed'], true)
            || (bool) ($payload['is_complete'] ?? $payload['expected_is_complete'] ?? false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function highPriorityTask(array $payload): bool
    {
        $priority = strtolower((string) ($payload['priority'] ?? ''));

        return in_array($priority, ['critical', 'high'], true);
    }

    private function operationTypeFromEvent(string $eventType): string
    {
        return str_replace(
            ['.created', '.updated', '.deleted', '.changed', '.completed', '.finalized', '_created', '_updated', '_deleted', '_changed', '_completed', '_finalized'],
            ['.create', '.update', '.delete', '.change', '.complete', '.finalize', '.create', '.update', '.delete', '.change', '.complete', '.finalize'],
            $eventType,
        );
    }

    private function breakerSegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }
}
