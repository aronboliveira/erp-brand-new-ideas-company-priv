<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OutboxMessage};
use Carbon\CarbonInterface;

class WarehouseReliabilityPolicy
{
    public const CLUSTER_STOCK_MUTATION = 'stock_mutation';
    public const CLUSTER_WAREHOUSE_TRANSFER = 'warehouse_transfer';
    public const CLUSTER_POS_COMMIT = 'pos_commit';
    public const CLUSTER_PURCHASE_COMMIT = 'purchase_commit';
    public const CLUSTER_BULK_IMPORT = 'bulk_import';
    public const CLUSTER_CATALOG_VALUE = 'catalog_value';
    public const CLUSTER_WAREHOUSE_LIFECYCLE = 'warehouse_lifecycle';
    public const CLUSTER_ROUTINE_METADATA = 'routine_metadata';

    private const QUANTITY_ELEVATED = 250;
    private const QUANTITY_HIGH = 1000;
    private const QUANTITY_EXTREME = 5000;
    private const BULK_ROWS_ELEVATED = 50;
    private const BULK_ROWS_HIGH = 250;
    private const VALUE_ELEVATED = 25000.0;
    private const VALUE_HIGH = 100000.0;
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
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): WarehouseReliabilityAssessment
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

        return new WarehouseReliabilityAssessment(
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
        WarehouseReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        return !$validation->passed
            && $assessment->quarantineCandidate
            && $this->hasCoreWarehouseCorruption($validation)
            && $this->severePersistence($assessment->signals);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
            'quantity_elevated' => self::QUANTITY_ELEVATED,
            'quantity_high' => self::QUANTITY_HIGH,
            'quantity_extreme' => self::QUANTITY_EXTREME,
            'bulk_rows_elevated' => self::BULK_ROWS_ELEVATED,
            'bulk_rows_high' => self::BULK_ROWS_HIGH,
            'value_elevated' => self::VALUE_ELEVATED,
            'value_high' => self::VALUE_HIGH,
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

        if (preg_match('/pos|point_of_sale|sale\\.commit|sale_created/', $needle)) {
            return self::CLUSTER_POS_COMMIT;
        }
        if (preg_match('/purchase/', $needle)) {
            return self::CLUSTER_PURCHASE_COMMIT;
        }
        if (preg_match('/transfer|dispatch/', $needle)) {
            return self::CLUSTER_WAREHOUSE_TRANSFER;
        }
        if (preg_match('/stock|quantity|inventory|reconcile|adjust/', $needle)) {
            return self::CLUSTER_STOCK_MUTATION;
        }
        if (preg_match('/import|bulk|batch/', $needle)) {
            return self::CLUSTER_BULK_IMPORT;
        }
        if (preg_match('/price|pricing|sku|catalog|product|service|tax|unit|category|account|valuation/', $needle)) {
            return self::CLUSTER_CATALOG_VALUE;
        }
        if (preg_match('/warehouse\\.delete|warehouse\\.destroy|warehouse\\.close|warehouse_lifecycle/', $needle)) {
            return self::CLUSTER_WAREHOUSE_LIFECYCLE;
        }

        return self::CLUSTER_ROUTINE_METADATA;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     */
    private function criticality(string $cluster, string $eventType, array $payload, array $signals): string
    {
        unset($eventType, $payload);

        if ((bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (
            (int) $signals['quantity'] >= self::QUANTITY_EXTREME
            || (float) $signals['value'] >= self::VALUE_HIGH
            || (int) $signals['bulk_rows'] >= self::BULK_ROWS_HIGH
            || (bool) $signals['closed_state_recovery']
        ) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if (in_array($cluster, [
            self::CLUSTER_STOCK_MUTATION,
            self::CLUSTER_WAREHOUSE_TRANSFER,
            self::CLUSTER_POS_COMMIT,
            self::CLUSTER_PURCHASE_COMMIT,
            self::CLUSTER_WAREHOUSE_LIFECYCLE,
        ], true)) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        if (
            $cluster === self::CLUSTER_BULK_IMPORT
            || $cluster === self::CLUSTER_CATALOG_VALUE
            || (int) $signals['quantity'] >= self::QUANTITY_ELEVATED
            || (float) $signals['value'] >= self::VALUE_ELEVATED
            || (int) $signals['bulk_rows'] >= self::BULK_ROWS_ELEVATED
            || (int) $signals['metadata_risk_score'] > 0
        ) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        return ReliabilityPolicy::CRITICALITY_MEDIUM;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function retryEligible(string $cluster, string $criticality, array $options): bool
    {
        if (array_key_exists('retry_eligible', $options)) {
            return (bool) $options['retry_eligible'];
        }

        return $cluster !== self::CLUSTER_ROUTINE_METADATA
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
            && $cluster !== self::CLUSTER_ROUTINE_METADATA;
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
            self::CLUSTER_WAREHOUSE_TRANSFER, self::CLUSTER_BULK_IMPORT => 5,
            self::CLUSTER_STOCK_MUTATION, self::CLUSTER_POS_COMMIT, self::CLUSTER_PURCHASE_COMMIT => 4,
            self::CLUSTER_WAREHOUSE_LIFECYCLE, self::CLUSTER_CATALOG_VALUE => 3,
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

        if (in_array($cluster, [
            self::CLUSTER_STOCK_MUTATION,
            self::CLUSTER_WAREHOUSE_TRANSFER,
            self::CLUSTER_POS_COMMIT,
            self::CLUSTER_PURCHASE_COMMIT,
            self::CLUSTER_BULK_IMPORT,
            self::CLUSTER_WAREHOUSE_LIFECYCLE,
        ], true)) {
            return true;
        }

        if ($cluster === self::CLUSTER_CATALOG_VALUE) {
            return $this->payloadTouchesDecisiveCatalogFields($payload)
                || preg_match('/price|pricing|sku|quantity|tax|unit|account|delete|destroy/', strtolower($eventType)) === 1;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function payloadTouchesDecisiveCatalogFields(array $payload): bool
    {
        foreach ([
            'sku',
            'sale_price',
            'purchase_price',
            'quantity',
            'tax_id',
            'category_id',
            'unit_id',
            'type',
            'sale_chart_account_id',
            'expense_chart_account_id',
            'is_active',
            'on_sale',
            'is_locked',
            'is_transferred',
            'changed_fields',
        ] as $key) {
            if (array_key_exists($key, $payload)) {
                return true;
            }
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
        $quantity = $this->quantity($payload, $options);
        $value = $this->value($payload, $options, $quantity);
        $bulkRows = $this->bulkRows($payload, $options);
        $priorFailures = $this->recentFailedOperations($eventType, $payload, $ledger);
        $retryFailures = $this->recentRetryFailures($eventType, $payload, $ledger);
        $deadLetters = $this->recentDeadLetters($eventType, $payload, $ledger);
        $circuitEvents = $this->recentCircuitInstability($eventType);
        $elapsedSeconds = $this->elapsedSeconds($payload, $ledger, $options);
        $metadataRiskScore = $this->metadataRiskScore($eventType, $payload, $options);

        $signals = [
            'quantity' => $quantity,
            'value' => $value,
            'bulk_rows' => $bulkRows,
            'prior_failed_operations' => $priorFailures,
            'retry_failures' => $retryFailures,
            'dead_letters' => $deadLetters,
            'circuit_instability_events' => $circuitEvents,
            'elapsed_seconds' => $elapsedSeconds,
            'metadata_risk_score' => $metadataRiskScore,
            'closed_state_recovery' => (bool) ($payload['closed_state_recovery'] ?? $options['closed_state_recovery'] ?? false),
            'eventual_consistency_sensitive' => (bool) ($payload['eventual_consistency_sensitive'] ?? $options['eventual_consistency_sensitive'] ?? false),
            'replica_sync_sensitive' => (bool) ($payload['replica_sync_sensitive'] ?? $options['replica_sync_sensitive'] ?? false),
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

        $elapsedSeconds = (int) $signals['elapsed_seconds'];

        return $elapsedSeconds >= self::EXTREME_PROCESSING_SECONDS
            || ((bool) $signals['closed_state_recovery'] && $elapsedSeconds >= self::LONG_PROCESSING_SECONDS)
            || ((int) $signals['quantity'] >= self::QUANTITY_EXTREME && $elapsedSeconds >= self::LONG_PROCESSING_SECONDS)
            || ((int) $signals['bulk_rows'] >= self::BULK_ROWS_HIGH && $elapsedSeconds >= self::LONG_PROCESSING_SECONDS);
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function clusterCanReachQuarantine(string $cluster, string $criticality, array $signals): bool
    {
        if (!in_array($cluster, [
            self::CLUSTER_STOCK_MUTATION,
            self::CLUSTER_WAREHOUSE_TRANSFER,
            self::CLUSTER_POS_COMMIT,
            self::CLUSTER_PURCHASE_COMMIT,
            self::CLUSTER_BULK_IMPORT,
            self::CLUSTER_WAREHOUSE_LIFECYCLE,
        ], true)) {
            return false;
        }

        if ($criticality === ReliabilityPolicy::CRITICALITY_CRITICAL) {
            return true;
        }

        return $this->severePersistence($signals);
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function severePersistence(array $signals): bool
    {
        return (int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) $signals['dead_letters'] > 0
            || (int) $signals['prior_failed_operations'] >= self::PRIOR_FAILURES_FOR_QUARANTINE
            || (int) $signals['elapsed_seconds'] >= self::EXTREME_PROCESSING_SECONDS;
    }

    private function hasCoreWarehouseCorruption(PostWriteValidationResult $validation): bool
    {
        $coreKeys = [
            'product',
            'product_delete',
            'product_quantity',
            'product_price',
            'product_type',
            'warehouse',
            'warehouse_delete',
            'warehouse_product',
            'warehouse_quantity',
            'warehouse_quantity_mismatch',
            'stock_report',
            'stock_report_quantity',
            'transfer',
            'transfer_delete',
            'transfer_quantity',
            'transfer_warehouse',
            'purchase',
            'purchase_stock',
            'pos',
            'pos_stock',
            'closed_state',
        ];

        return array_intersect($coreKeys, array_keys($validation->validationErrors)) !== [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentFailedOperations(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'warehouse')
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
            ->where('channel', 'like', 'warehouse%')
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
            ->where('stream', 'warehouse.operations')
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
            ->where('channel', 'like', 'warehouse%')
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
            $payload['eventual_consistency_sensitive'] ?? null,
            $payload['replica_sync_sensitive'] ?? null,
            $options['bulk'] ?? null,
            $options['high_risk'] ?? null,
            $options['external_origin'] ?? null,
            $options['privileged_actor'] ?? null,
            $options['requires_approval'] ?? null,
            $options['eventual_consistency_sensitive'] ?? null,
            $options['replica_sync_sensitive'] ?? null,
        ] as $flag) {
            if ((bool) $flag) {
                $score++;
            }
        }

        if (preg_match('/delete|destroy|transfer|dispatch|stock|quantity|price|import|pos|purchase|closed/', strtolower($eventType)) === 1) {
            $score++;
        }

        $riskScore = $payload['risk_score'] ?? $payload['user_risk_score'] ?? $options['risk_score'] ?? null;
        if (is_numeric($riskScore) && (float) $riskScore >= 70.0) {
            $score++;
        }

        return min(4, $score);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function quantity(array $payload, array $options): int
    {
        $value = $options['quantity']
            ?? $payload['quantity']
            ?? $payload['total_quantity']
            ?? $payload['quantity_delta']
            ?? 0;

        return max(0, (int) abs((float) $value));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function value(array $payload, array $options, int $quantity): float
    {
        $explicit = $options['value']
            ?? $options['total_value']
            ?? $payload['value']
            ?? $payload['total_value']
            ?? $payload['amount']
            ?? null;

        if (is_numeric($explicit)) {
            return max(0.0, (float) $explicit);
        }

        $price = $payload['sale_price']
            ?? $payload['purchase_price']
            ?? $payload['unit_price']
            ?? $payload['price']
            ?? $options['unit_price']
            ?? 0;

        return max(0.0, (float) $price * max(1, $quantity));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function bulkRows(array $payload, array $options): int
    {
        $value = $options['bulk_rows']
            ?? $options['row_count']
            ?? $payload['bulk_rows']
            ?? $payload['row_count']
            ?? $payload['items_count']
            ?? 0;

        return max(0, (int) $value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function subjectId(array $payload): ?string
    {
        $value = $payload['transfer_id']
            ?? $payload['warehouse_transfer_id']
            ?? $payload['warehouse_product_id']
            ?? $payload['stock_report_id']
            ?? $payload['product_id']
            ?? $payload['product_service_id']
            ?? $payload['warehouse_id']
            ?? $payload['purchase_id']
            ?? $payload['pos_id']
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
