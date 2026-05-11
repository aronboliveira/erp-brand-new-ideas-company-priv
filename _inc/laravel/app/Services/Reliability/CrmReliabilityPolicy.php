<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OutboxMessage};
use Carbon\CarbonInterface;

class CrmReliabilityPolicy
{
    public const CLUSTER_LEAD_LIFECYCLE = 'lead_lifecycle';
    public const CLUSTER_LEAD_CONVERSION = 'lead_conversion';
    public const CLUSTER_DEAL_LIFECYCLE = 'deal_lifecycle';
    public const CLUSTER_DEAL_STATUS = 'deal_status';
    public const CLUSTER_STAGE_PIPELINE = 'stage_pipeline_movement';
    public const CLUSTER_ACCESS_ASSIGNMENT = 'crm_access_assignment';
    public const CLUSTER_CATALOG_CONTEXT = 'catalog_context';
    public const CLUSTER_CONFIGURATION = 'configuration';
    public const CLUSTER_TRANSIENT_ACTIVITY = 'transient_activity';

    private const DEAL_VALUE_ELEVATED = 25000.0;
    private const DEAL_VALUE_HIGH = 100000.0;
    private const DEAL_VALUE_CRITICAL = 250000.0;
    private const BULK_ITEMS_ELEVATED = 25;
    private const BULK_ITEMS_HIGH = 100;
    private const LONG_PROCESSING_SECONDS = 600;
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
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): CrmReliabilityAssessment
    {
        $cluster = $this->cluster($eventType, $payload, $options);
        $signals = $this->signals($eventType, $payload, $ledger, $options);
        $criticality = ReliabilityPolicy::normalizeCriticality(
            $options['criticality'] ?? $this->criticality($cluster, $eventType, $payload, $signals)
        );
        $retryEligible = $this->retryEligible($cluster, $criticality, $options);
        $circuitBreakerEligible = $this->circuitBreakerEligible($cluster, $criticality, $options);
        $maxAttempts = $this->maxAttempts($cluster, $criticality, $signals, $options, $retryEligible);
        $postWriteValidation = $this->postWriteValidationRequired($cluster, $eventType, $payload, $options);
        $quarantineCandidate = (bool) $signals['persistent_failure_detected']
            && $this->clusterCanReachQuarantine($cluster, $criticality, $signals);

        return new CrmReliabilityAssessment(
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
        CrmReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        return !$validation->passed
            && $assessment->quarantineCandidate
            && $this->hasCoreCrmCorruption($validation)
            && $this->severePersistence($assessment->signals);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
            'deal_value_elevated' => self::DEAL_VALUE_ELEVATED,
            'deal_value_high' => self::DEAL_VALUE_HIGH,
            'deal_value_critical' => self::DEAL_VALUE_CRITICAL,
            'bulk_items_elevated' => self::BULK_ITEMS_ELEVATED,
            'bulk_items_high' => self::BULK_ITEMS_HIGH,
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

        if (preg_match('/convert|conversion|lead\\.converted/', $needle) === 1) {
            return self::CLUSTER_LEAD_CONVERSION;
        }
        if (preg_match('/status|won|loss|lost|close|final/', $needle) === 1) {
            return self::CLUSTER_DEAL_STATUS;
        }
        if (preg_match('/permission|client_link|client_unlink|user_link|user_unlink|assign|owner|member|access/', $needle) === 1) {
            return self::CLUSTER_ACCESS_ASSIGNMENT;
        }
        if (preg_match('/stage|pipeline|order|move|kanban/', $needle) === 1) {
            return self::CLUSTER_STAGE_PIPELINE;
        }
        if (preg_match('/deal/', $needle) === 1) {
            return self::CLUSTER_DEAL_LIFECYCLE;
        }
        if (preg_match('/lead/', $needle) === 1) {
            return self::CLUSTER_LEAD_LIFECYCLE;
        }
        if (preg_match('/product|source|label|catalog|context/', $needle) === 1) {
            return self::CLUSTER_CATALOG_CONTEXT;
        }
        if (preg_match('/pipeline_config|stage_config|label_config|source_config|setting|configuration/', $needle) === 1) {
            return self::CLUSTER_CONFIGURATION;
        }
        if (preg_match('/note|file|call|email|discussion|comment|dashboard|report/', $needle) === 1) {
            return self::CLUSTER_TRANSIENT_ACTIVITY;
        }

        return self::CLUSTER_TRANSIENT_ACTIVITY;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     */
    private function criticality(string $cluster, string $eventType, array $payload, array $signals): string
    {
        unset($payload);

        if ((bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            (float) $signals['deal_value'] >= self::DEAL_VALUE_CRITICAL
            || (int) $signals['bulk_items'] >= self::BULK_ITEMS_HIGH
            || (bool) $signals['final_state']
            || (bool) $signals['permission_sensitive']
        ) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (in_array($cluster, [self::CLUSTER_LEAD_CONVERSION, self::CLUSTER_DEAL_STATUS], true)) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (
            $cluster === self::CLUSTER_DEAL_LIFECYCLE
            && (str_contains($eventType, 'deleted') || str_contains($eventType, 'destroy'))
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (
            in_array($cluster, [self::CLUSTER_ACCESS_ASSIGNMENT, self::CLUSTER_STAGE_PIPELINE], true)
            || (float) $signals['deal_value'] >= self::DEAL_VALUE_ELEVATED
            || (int) $signals['bulk_items'] >= self::BULK_ITEMS_ELEVATED
            || (int) $signals['metadata_risk_score'] > 0
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (in_array($cluster, [self::CLUSTER_LEAD_LIFECYCLE, self::CLUSTER_DEAL_LIFECYCLE, self::CLUSTER_CATALOG_CONTEXT], true)) {
            return ReliabilityPolicy::CRITICALITY_MEDIUM;
        }

        return $cluster === self::CLUSTER_CONFIGURATION
            ? ReliabilityPolicy::CRITICALITY_MEDIUM
            : ReliabilityPolicy::CRITICALITY_LOW;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function retryEligible(string $cluster, string $criticality, array $options): bool
    {
        if (array_key_exists('retry_eligible', $options)) {
            return (bool) $options['retry_eligible'];
        }

        return !in_array($cluster, [self::CLUSTER_TRANSIENT_ACTIVITY, self::CLUSTER_CONFIGURATION], true)
            || ReliabilityPolicy::shouldPersist($criticality);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function circuitBreakerEligible(string $cluster, string $criticality, array $options): bool
    {
        if (array_key_exists('circuit_breaker_eligible', $options)) {
            return (bool) $options['circuit_breaker_eligible'];
        }

        return ReliabilityPolicy::circuitBreakerRequired($criticality)
            && !in_array($cluster, [self::CLUSTER_TRANSIENT_ACTIVITY, self::CLUSTER_CONFIGURATION], true);
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
            self::CLUSTER_LEAD_CONVERSION => 5,
            self::CLUSTER_DEAL_STATUS, self::CLUSTER_ACCESS_ASSIGNMENT => 4,
            self::CLUSTER_DEAL_LIFECYCLE, self::CLUSTER_STAGE_PIPELINE => 3,
            self::CLUSTER_LEAD_LIFECYCLE, self::CLUSTER_CATALOG_CONTEXT => 2,
            default => 1,
        };

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            $attempts++;
        }
        if ((float) $signals['deal_value'] >= self::DEAL_VALUE_HIGH) {
            $attempts++;
        }
        if ((bool) $signals['persistent_failure_detected']) {
            $attempts += 2;
        }
        if ((int) $signals['metadata_risk_score'] > 0) {
            $attempts += min(2, (int) $signals['metadata_risk_score']);
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

        if (in_array($cluster, [
            self::CLUSTER_LEAD_CONVERSION,
            self::CLUSTER_DEAL_STATUS,
            self::CLUSTER_ACCESS_ASSIGNMENT,
            self::CLUSTER_STAGE_PIPELINE,
        ], true)) {
            return true;
        }

        if ($cluster === self::CLUSTER_DEAL_LIFECYCLE) {
            return str_contains($eventType, 'deleted')
                || str_contains($eventType, 'destroy')
                || (float) $this->dealValue($payload) >= self::DEAL_VALUE_ELEVATED;
        }

        return $cluster === self::CLUSTER_LEAD_LIFECYCLE
            && ((bool) ($payload['is_critical'] ?? false) || str_contains($eventType, 'deleted'));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function signals(string $eventType, array $payload, ?OperationLedger $ledger, array $options): array
    {
        $dealValue = $this->dealValue($payload);
        $bulkItems = $this->bulkItems($payload, $options);
        $priorFailures = $this->recentFailedOperations($eventType, $payload, $ledger);
        $retryFailures = $this->recentRetryFailures($eventType, $payload, $ledger);
        $deadLetters = $this->recentDeadLetters($eventType, $payload, $ledger);
        $circuitEvents = $this->recentCircuitInstability($eventType);
        $elapsedSeconds = $this->elapsedSeconds($payload, $ledger, $options);
        $metadataRiskScore = $this->metadataRiskScore($eventType, $payload, $options);

        $signals = [
            'deal_value' => $dealValue,
            'bulk_items' => $bulkItems,
            'final_state' => $this->finalState($eventType, $payload),
            'permission_sensitive' => $this->permissionSensitive($eventType, $payload),
            'external_signal' => (bool) ($payload['webhook'] ?? $payload['external_origin'] ?? $options['external_origin'] ?? false),
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
        if (!in_array($cluster, [
            self::CLUSTER_LEAD_CONVERSION,
            self::CLUSTER_DEAL_STATUS,
            self::CLUSTER_DEAL_LIFECYCLE,
            self::CLUSTER_ACCESS_ASSIGNMENT,
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

    private function hasCoreCrmCorruption(PostWriteValidationResult $validation): bool
    {
        $coreKeys = [
            'lead',
            'lead_delete',
            'lead_conversion',
            'lead_stage',
            'lead_pipeline',
            'lead_user_link',
            'deal',
            'deal_delete',
            'deal_price',
            'deal_status',
            'deal_stage',
            'deal_pipeline',
            'deal_client_link',
            'deal_user_link',
            'deal_permission',
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
                    (float) $signals['deal_value'] >= self::DEAL_VALUE_HIGH
                    || (bool) $signals['final_state']
                    || (bool) $signals['permission_sensitive']
                )
            );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentFailedOperations(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'crm')
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
            ->where('channel', 'like', 'crm%')
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
            ->where('stream', 'crm.operations')
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
            ->where('channel', 'like', 'crm%')
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
            $payload['creates_client'] ?? null,
            $options['high_risk'] ?? null,
            $options['external_origin'] ?? null,
            $options['privileged_actor'] ?? null,
            $options['requires_approval'] ?? null,
        ] as $flag) {
            if ((bool) $flag) {
                $score++;
            }
        }

        if (preg_match('/convert|permission|client|status|won|loss|delete|destroy/', strtolower($eventType)) === 1) {
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
        $value = $payload['deal_id']
            ?? $payload['lead_id']
            ?? $payload['client_id']
            ?? $payload['permission_id']
            ?? $payload['subject_id']
            ?? $payload['id']
            ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function dealValue(array $payload): float
    {
        $value = $payload['deal_value']
            ?? $payload['price']
            ?? $payload['amount']
            ?? $payload['total']
            ?? 0;

        return is_numeric($value) ? max(0.0, (float) $value) : 0.0;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function bulkItems(array $payload, array $options): int
    {
        $value = $payload['bulk_items']
            ?? $payload['items_count']
            ?? $payload['row_count']
            ?? $options['bulk_items']
            ?? 0;

        return is_numeric($value) ? max(0, (int) $value) : 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function finalState(string $eventType, array $payload): bool
    {
        $status = strtolower((string) ($payload['status'] ?? $payload['expected_status'] ?? ''));

        return preg_match('/won|loss|lost|closed|final/', strtolower($eventType)) === 1
            || in_array($status, ['won', 'loss', 'lost', 'closed', 'final'], true)
            || (bool) ($payload['final_state'] ?? false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function permissionSensitive(string $eventType, array $payload): bool
    {
        return preg_match('/permission|client_link|client_unlink|access/', strtolower($eventType)) === 1
            || (bool) ($payload['permission_sensitive'] ?? false);
    }

    private function operationTypeFromEvent(string $eventType): string
    {
        return str_replace(
            ['.created', '.updated', '.deleted', '.changed', '.converted', '_created', '_updated', '_deleted', '_changed', '_converted'],
            ['.create', '.update', '.delete', '.change', '.convert', '.create', '.update', '.delete', '.change', '.convert'],
            $eventType,
        );
    }

    private function breakerSegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }
}
