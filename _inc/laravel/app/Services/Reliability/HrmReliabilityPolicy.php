<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OutboxMessage};
use Carbon\CarbonInterface;

class HrmReliabilityPolicy
{
    public const CLUSTER_PAYROLL = 'payroll';
    public const CLUSTER_LIFECYCLE = 'employee_lifecycle';
    public const CLUSTER_IDENTITY_ACCESS = 'identity_access';
    public const CLUSTER_HR_DECISION = 'hr_decision';
    public const CLUSTER_ROUTINE_CONTROL = 'routine_control';
    public const CLUSTER_CONFIGURATION = 'configuration';

    private const LONG_PROCESSING_SECONDS = 900;
    private const EXTREME_PROCESSING_SECONDS = 1800;
    private const RECENT_FAILURE_WINDOW_MINUTES = 60;
    private const CIRCUIT_INSTABILITY_WINDOW_MINUTES = 60;
    private const PRIOR_FAILURES_FOR_QUARANTINE = 4;
    private const RETRY_FAILURES_FOR_QUARANTINE = 5;
    private const CIRCUIT_EVENTS_FOR_QUARANTINE = 3;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): HrmReliabilityAssessment
    {
        $cluster = $this->cluster($eventType, $payload, $options);
        $signals = $this->signals($eventType, $payload, $ledger, $options);
        $persistentFailure = $this->persistentFailureDetected($signals);
        $criticality = ReliabilityPolicy::normalizeCriticality(
            $options['criticality'] ?? $this->criticality($cluster, $eventType, $payload, $signals)
        );
        $retryEligible = $this->retryEligible($cluster, $criticality, $eventType, $payload, $options);
        $circuitBreakerEligible = ReliabilityPolicy::circuitBreakerRequired($criticality)
            && $cluster !== self::CLUSTER_CONFIGURATION;
        $maxAttempts = $this->maxAttempts($cluster, $criticality, $signals, $options, $retryEligible);
        $postWriteValidation = $this->postWriteValidationRequired($cluster, $eventType, $payload, $options);
        $quarantineCandidate = $persistentFailure && $this->clusterCanReachQuarantine($cluster, $criticality, $signals);

        return new HrmReliabilityAssessment(
            $cluster,
            $criticality,
            $maxAttempts,
            $retryEligible,
            $circuitBreakerEligible,
            $postWriteValidation,
            $persistentFailure,
            $quarantineCandidate,
            $signals,
            $this->thresholds(),
        );
    }

    public function shouldQuarantine(
        PostWriteValidationResult $validation,
        HrmReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        return !$validation->passed
            && $assessment->quarantineCandidate
            && $this->hasCoreHrmCorruption($validation);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
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

        if (preg_match('/salary|payroll|payslip|allowance|commission|loan|deduction|overtime/', $needle)) {
            return self::CLUSTER_PAYROLL;
        }

        if (preg_match('/termination|resignation|employee\\.destroy|employee\\.delete|employee_deleted|final/', $needle)) {
            return self::CLUSTER_LIFECYCLE;
        }

        if (preg_match('/employee|user|role|rbac|access/', $needle)) {
            return self::CLUSTER_IDENTITY_ACCESS;
        }

        if (preg_match('/leave|promotion|transfer|warning|complaint|appraisal/', $needle)) {
            return self::CLUSTER_HR_DECISION;
        }

        if (preg_match('/department|designation|branch|leave_type|payslip_type|option|setting/', $needle)) {
            return self::CLUSTER_CONFIGURATION;
        }

        return self::CLUSTER_ROUTINE_CONTROL;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     */
    private function criticality(string $cluster, string $eventType, array $payload, array $signals): string
    {
        if ((bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if ($cluster === self::CLUSTER_PAYROLL || $cluster === self::CLUSTER_LIFECYCLE) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if ($cluster === self::CLUSTER_IDENTITY_ACCESS) {
            return str_contains($eventType, 'destroy') || str_contains($eventType, 'delete')
                ? ReliabilityPolicy::CRITICALITY_CRITICAL
                : ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if ($cluster === self::CLUSTER_HR_DECISION) {
            $status = strtolower((string) ($payload['status'] ?? $payload['requested_status'] ?? ''));

            return in_array($status, ['approved', 'approval', 'reject', 'rejected', 'terminated'], true)
                ? ReliabilityPolicy::CRITICALITY_HIGH
                : ReliabilityPolicy::CRITICALITY_MEDIUM;
        }

        return $cluster === self::CLUSTER_CONFIGURATION
            ? ReliabilityPolicy::CRITICALITY_MEDIUM
            : ReliabilityPolicy::CRITICALITY_MEDIUM;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function retryEligible(string $cluster, string $criticality, string $eventType, array $payload, array $options): bool
    {
        unset($eventType, $payload);

        if (array_key_exists('retry_eligible', $options)) {
            return (bool) $options['retry_eligible'];
        }

        if ($cluster === self::CLUSTER_CONFIGURATION) {
            return false;
        }

        return ReliabilityPolicy::shouldPersist($criticality);
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
            self::CLUSTER_PAYROLL, self::CLUSTER_LIFECYCLE => 5,
            self::CLUSTER_IDENTITY_ACCESS => 4,
            self::CLUSTER_HR_DECISION => 3,
            default => 2,
        };

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            $attempts++;
        }
        if ((bool) $signals['persistent_failure_detected']) {
            $attempts += 2;
        }
        if ((int) $signals['metadata_risk_score'] > 0) {
            $attempts += min(2, (int) $signals['metadata_risk_score']);
        }

        return max(2, min(10, $attempts));
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

        if (in_array($cluster, [self::CLUSTER_PAYROLL, self::CLUSTER_LIFECYCLE, self::CLUSTER_IDENTITY_ACCESS], true)) {
            return true;
        }

        if ($cluster === self::CLUSTER_HR_DECISION) {
            $status = strtolower((string) ($payload['status'] ?? $payload['requested_status'] ?? $eventType));

            return preg_match('/approved|approval|reject|rejected|warning|complaint|transfer|promotion/', $status) === 1
                || str_contains($eventType, 'status_changed');
        }

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function signals(string $eventType, array $payload, ?OperationLedger $ledger, array $options): array
    {
        $priorFailures = $this->recentFailedOperations($eventType, $payload, $ledger);
        $retryFailures = $this->recentRetryFailures($eventType, $payload, $ledger);
        $deadLetters = $this->recentDeadLetters($eventType, $payload, $ledger);
        $circuitEvents = $this->recentCircuitInstability($eventType);
        $elapsedSeconds = $this->elapsedSeconds($payload, $ledger, $options);
        $metadataRiskScore = $this->metadataRiskScore($eventType, $payload, $options);

        $signals = [
            'prior_failed_operations' => $priorFailures,
            'retry_failures' => $retryFailures,
            'dead_letters' => $deadLetters,
            'circuit_instability_events' => $circuitEvents,
            'elapsed_seconds' => $elapsedSeconds,
            'metadata_risk_score' => $metadataRiskScore,
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
        if (!in_array($cluster, [self::CLUSTER_PAYROLL, self::CLUSTER_LIFECYCLE, self::CLUSTER_IDENTITY_ACCESS], true)) {
            return false;
        }

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            return true;
        }

        return (int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) $signals['dead_letters'] > 0;
    }

    private function hasCoreHrmCorruption(PostWriteValidationResult $validation): bool
    {
        $coreKeys = [
            'employee',
            'employee_user_link',
            'employee_user_type',
            'employee_user_role',
            'employee_user_creator',
            'employee_user_email',
            'salary',
            'salary_type',
            'leave',
            'leave_employee',
            'leave_dates',
            'termination',
            'termination_employee',
            'termination_dates',
            'termination_delete',
        ];

        return array_intersect($coreKeys, array_keys($validation->validationErrors)) !== [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentFailedOperations(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'hrm')
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
            ->where('channel', 'like', 'hrm%')
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
            ->where('stream', 'hrm.operations')
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
            ->where('channel', 'like', 'hrm%')
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
            $payload['bulk'] ?? null,
            $payload['high_risk'] ?? null,
            $payload['external_origin'] ?? null,
            $payload['privileged_actor'] ?? null,
            $payload['requires_approval'] ?? null,
            $options['bulk'] ?? null,
            $options['high_risk'] ?? null,
            $options['external_origin'] ?? null,
            $options['privileged_actor'] ?? null,
            $options['requires_approval'] ?? null,
        ] as $flag) {
            if ((bool) $flag) {
                $score++;
            }
        }

        if (preg_match('/terminate|termination|salary|payroll|rbac|role|delete|destroy/', strtolower($eventType)) === 1) {
            $score++;
        }

        $riskScore = $payload['risk_score'] ?? $payload['user_risk_score'] ?? $options['risk_score'] ?? null;
        if (is_numeric($riskScore) && (float) $riskScore >= 70.0) {
            $score++;
        }

        return min(3, $score);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function subjectId(array $payload): ?string
    {
        $value = $payload['employee_id']
            ?? $payload['termination_id']
            ?? $payload['leave_id']
            ?? $payload['subject_id']
            ?? $payload['id']
            ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function operationTypeFromEvent(string $eventType): string
    {
        return str_replace(['_created', '_updated', '_deleted', '_changed'], ['.create', '.update', '.delete', '.change'], $eventType);
    }

    private function breakerSegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }
}
