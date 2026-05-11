<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{CircuitBreakerCall, OperationLedger, OperationalEvent, OutboxMessage};

class HeavyIoReliabilityPolicy
{
    public const CLUSTER_PYTHON_IMPORT = 'python_import';
    public const CLUSTER_PYTHON_EXPORT = 'python_export';
    public const CLUSTER_WEBHOOK_DELIVERY = 'webhook_delivery';
    public const CLUSTER_EXTERNAL_CALLBACK = 'external_callback';
    public const CLUSTER_ASYNC_JOB = 'async_job';
    public const CLUSTER_ROUTINE_IO = 'routine_io';

    private const IMPORT_ROWS_ELEVATED = 100;
    private const IMPORT_ROWS_HIGH = 1000;
    private const IMPORT_ROWS_CRITICAL = 5000;
    private const PAYLOAD_BYTES_HIGH = 1048576;
    private const PAYLOAD_BYTES_CRITICAL = 8388608;
    private const LONG_PROCESSING_SECONDS = 300;
    private const EXTREME_PROCESSING_SECONDS = 1200;
    private const RECENT_FAILURE_WINDOW_MINUTES = 60;
    private const PRIOR_FAILURES_FOR_QUARANTINE = 4;
    private const RETRY_FAILURES_FOR_QUARANTINE = 5;
    private const CIRCUIT_EVENTS_FOR_QUARANTINE = 3;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): HeavyIoReliabilityAssessment
    {
        $cluster = $this->cluster($eventType, $payload, $options);
        $signals = $this->signals($eventType, $payload, $ledger, $options);
        $criticality = ReliabilityPolicy::normalizeCriticality(
            $options['criticality'] ?? $this->criticality($cluster, $signals)
        );
        $retryEligible = $this->retryEligible($cluster, $criticality, $options);
        $circuitBreakerEligible = $this->circuitBreakerEligible($cluster, $criticality, $options);
        $maxAttempts = $this->maxAttempts($cluster, $criticality, $signals, $options, $retryEligible);
        $postWriteValidation = $this->postWriteValidationRequired($cluster, $criticality, $options);
        $quarantineCandidate = (bool) $signals['persistent_failure_detected']
            && $this->clusterCanReachQuarantine($cluster, $criticality, $signals);

        return new HeavyIoReliabilityAssessment(
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
        HeavyIoReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        return !$validation->passed
            && $assessment->quarantineCandidate
            && $this->hasCoreIntegrationCorruption($validation)
            && $this->severePersistence($assessment->signals);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
            'import_rows_elevated' => self::IMPORT_ROWS_ELEVATED,
            'import_rows_high' => self::IMPORT_ROWS_HIGH,
            'import_rows_critical' => self::IMPORT_ROWS_CRITICAL,
            'payload_bytes_high' => self::PAYLOAD_BYTES_HIGH,
            'payload_bytes_critical' => self::PAYLOAD_BYTES_CRITICAL,
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

        $needle = strtolower($eventType . ' ' . (string) ($payload['operation_type'] ?? '') . ' ' . (string) ($payload['integration_name'] ?? ''));

        if (str_contains($needle, 'python_import') || str_contains($needle, 'import')) {
            return self::CLUSTER_PYTHON_IMPORT;
        }
        if (str_contains($needle, 'python_export') || str_contains($needle, 'export')) {
            return self::CLUSTER_PYTHON_EXPORT;
        }
        if (str_contains($needle, 'webhook')) {
            return self::CLUSTER_WEBHOOK_DELIVERY;
        }
        if (str_contains($needle, 'callback') || str_contains($needle, 'calendar') || str_contains($needle, 'api')) {
            return self::CLUSTER_EXTERNAL_CALLBACK;
        }
        if (str_contains($needle, 'job') || str_contains($needle, 'queue') || str_contains($needle, 'async')) {
            return self::CLUSTER_ASYNC_JOB;
        }

        return self::CLUSTER_ROUTINE_IO;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function criticality(string $cluster, array $signals): string
    {
        if ((bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            (int) $signals['rows_count'] >= self::IMPORT_ROWS_CRITICAL
            || (int) $signals['payload_bytes'] >= self::PAYLOAD_BYTES_CRITICAL
            || (float) $signals['processing_seconds'] >= self::EXTREME_PROCESSING_SECONDS
        ) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            $cluster === self::CLUSTER_PYTHON_IMPORT
            && ((bool) $signals['high_impact_name'] || (int) $signals['rows_count'] >= self::IMPORT_ROWS_HIGH)
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (
            $cluster === self::CLUSTER_PYTHON_EXPORT
            && ((bool) $signals['regulated_report_name'] || (int) $signals['payload_bytes'] >= self::PAYLOAD_BYTES_HIGH)
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if ($cluster === self::CLUSTER_WEBHOOK_DELIVERY && (int) $signals['payload_bytes'] >= self::PAYLOAD_BYTES_HIGH) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (in_array($cluster, [
            self::CLUSTER_PYTHON_IMPORT,
            self::CLUSTER_PYTHON_EXPORT,
            self::CLUSTER_WEBHOOK_DELIVERY,
            self::CLUSTER_EXTERNAL_CALLBACK,
            self::CLUSTER_ASYNC_JOB,
        ], true)) {
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

        return $cluster !== self::CLUSTER_ROUTINE_IO
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

        return $cluster !== self::CLUSTER_ROUTINE_IO
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
            self::CLUSTER_PYTHON_IMPORT => 3,
            self::CLUSTER_WEBHOOK_DELIVERY, self::CLUSTER_EXTERNAL_CALLBACK, self::CLUSTER_ASYNC_JOB => 3,
            self::CLUSTER_PYTHON_EXPORT => 2,
            default => 1,
        };

        if ($criticality === ReliabilityPolicy::CRITICALITY_HIGH) {
            $attempts++;
        }
        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            $attempts += 2;
        }
        if ((int) $signals['rows_count'] >= self::IMPORT_ROWS_HIGH || (int) $signals['payload_bytes'] >= self::PAYLOAD_BYTES_HIGH) {
            $attempts++;
        }
        if ((bool) $signals['persistent_failure_detected']) {
            $attempts += 2;
        }

        return max(1, min(10, $attempts));
    }

    /**
     * @param array<string, mixed> $options
     */
    private function postWriteValidationRequired(string $cluster, string $criticality, array $options): bool
    {
        if ((bool) ($options['force_post_write_validation'] ?? false)) {
            return true;
        }

        if (!ReliabilityPolicy::shouldPersist($criticality)) {
            return false;
        }

        return in_array($cluster, [
            self::CLUSTER_PYTHON_IMPORT,
            self::CLUSTER_PYTHON_EXPORT,
            self::CLUSTER_WEBHOOK_DELIVERY,
            self::CLUSTER_EXTERNAL_CALLBACK,
            self::CLUSTER_ASYNC_JOB,
        ], true);
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function clusterCanReachQuarantine(string $cluster, string $criticality, array $signals): bool
    {
        if (!in_array($criticality, [ReliabilityPolicy::CRITICALITY_HIGH, ReliabilityPolicy::CRITICALITY_CRITICAL], true)) {
            return false;
        }

        if ($cluster === self::CLUSTER_PYTHON_IMPORT) {
            return (bool) $signals['high_impact_name'] || (int) $signals['rows_count'] >= self::IMPORT_ROWS_HIGH;
        }

        if ($cluster === self::CLUSTER_PYTHON_EXPORT) {
            return (bool) $signals['regulated_report_name'];
        }

        return in_array($cluster, [
            self::CLUSTER_WEBHOOK_DELIVERY,
            self::CLUSTER_EXTERNAL_CALLBACK,
            self::CLUSTER_ASYNC_JOB,
        ], true);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function signals(string $eventType, array $payload, ?OperationLedger $ledger, array $options): array
    {
        $name = (string) ($payload['integration_name'] ?? $payload['importer'] ?? $payload['exporter'] ?? $options['integration_name'] ?? '');
        $operationType = (string) ($payload['operation_type'] ?? $eventType);
        $rowsCount = (int) ($payload['rows_count'] ?? $payload['row_count'] ?? $payload['records_count'] ?? 0);
        $payloadBytes = (int) ($payload['payload_bytes'] ?? $payload['data_size'] ?? $payload['output_bytes'] ?? 0);
        $processingSeconds = (float) ($payload['processing_seconds'] ?? $payload['elapsed_seconds'] ?? 0);
        if ($ledger && $ledger->started_at) {
            $processingSeconds = max($processingSeconds, (float) $ledger->started_at->diffInSeconds(now()));
        }

        $priorFailures = $this->recentFailureCount($operationType, $ledger);
        $retryFailures = $this->recentEventCount('reliability.retry.failed');
        $circuitEvents = $this->recentCircuitInstabilityCount();
        $deadLetters = $this->recentDeadLetterCount();
        $persistentFailure = $priorFailures >= self::PRIOR_FAILURES_FOR_QUARANTINE
            || $retryFailures >= self::RETRY_FAILURES_FOR_QUARANTINE
            || $circuitEvents >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || $deadLetters > 0
            || $processingSeconds >= self::EXTREME_PROCESSING_SECONDS;

        return [
            'operation_type' => $operationType,
            'integration_name' => $name,
            'rows_count' => $rowsCount,
            'payload_bytes' => $payloadBytes,
            'processing_seconds' => $processingSeconds,
            'high_impact_name' => $this->highImpactName($name),
            'regulated_report_name' => $this->regulatedReportName($name),
            'prior_failed_operations' => $priorFailures,
            'retry_failures' => $retryFailures,
            'circuit_instability_events' => $circuitEvents,
            'dead_letter_messages' => $deadLetters,
            'persistent_failure_detected' => $persistentFailure,
        ];
    }

    private function recentFailureCount(string $operationType, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'heavy_io')
            ->where('status', ReliabilityPolicy::STATUS_FAILED)
            ->where('started_at', '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES));

        if ($operationType !== '') {
            $query->where('operation_type', $operationType);
        }
        if ($ledger) {
            $query->where('id', '!=', $ledger->id);
        }

        return (int) $query->count();
    }

    private function recentEventCount(string $eventType): int
    {
        return (int) OperationalEvent::query()
            ->where('event_type', $eventType)
            ->where('channel', 'heavy_io')
            ->where('occurred_at', '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES))
            ->count();
    }

    private function recentCircuitInstabilityCount(): int
    {
        return (int) CircuitBreakerCall::query()
            ->where('breaker_key', 'like', 'heavy_io.%')
            ->whereIn('status', [ReliabilityPolicy::CIRCUIT_CALL_REJECTED, ReliabilityPolicy::CIRCUIT_CALL_FAILED])
            ->where('occurred_at', '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES))
            ->count();
    }

    private function recentDeadLetterCount(): int
    {
        return (int) OutboxMessage::query()
            ->where('stream', 'heavy_io.operations')
            ->where('status', ReliabilityPolicy::OUTBOX_DEAD_LETTER)
            ->where(DC::COL_FL_AT, '>=', now()->subMinutes(self::RECENT_FAILURE_WINDOW_MINUTES))
            ->count();
    }

    private function highImpactName(string $name): bool
    {
        return preg_match('/customer|vendor|employee|attendance|product|service|stock|purchase|payroll|invoice|bill|transaction|balance|ledger/i', $name) === 1;
    }

    private function regulatedReportName(string $name): bool
    {
        return preg_match('/payroll|payslip|invoice|bill|transaction|balance|trial|profit|loss|receivable|account|stock|sales/i', $name) === 1;
    }

    private function hasCoreIntegrationCorruption(PostWriteValidationResult $validation): bool
    {
        foreach ($validation->failedCriteria as $criterion) {
            if (in_array($criterion, [
                'python_import_result_invalid',
                'python_export_output_missing',
                'webhook_delivery_failed',
                'external_callback_invalid',
                'async_job_invalid_result',
            ], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function severePersistence(array $signals): bool
    {
        return (int) ($signals['prior_failed_operations'] ?? 0) >= self::PRIOR_FAILURES_FOR_QUARANTINE
            || (int) ($signals['retry_failures'] ?? 0) >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) ($signals['circuit_instability_events'] ?? 0) >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) ($signals['dead_letter_messages'] ?? 0) > 0
            || (float) ($signals['processing_seconds'] ?? 0) >= self::EXTREME_PROCESSING_SECONDS;
    }
}
