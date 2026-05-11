<?php

namespace App\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\{OperationLedger, OperationalEvent, OutboxMessage};
use Carbon\CarbonInterface;

class FinanceReliabilityPolicy
{
    public const AMOUNT_POST_WRITE_VALIDATION = 3200.0;
    public const AMOUNT_ELEVATED = 25000.0;
    public const AMOUNT_HIGH = 100000.0;
    public const AMOUNT_EXTREME = 250000.0;
    public const AMOUNT_CATASTROPHIC = 1000000.0;

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
    public function assess(string $eventType, array $payload, ?OperationLedger $ledger = null, array $options = []): FinanceReliabilityAssessment
    {
        $amount = $this->amount($payload, $options);
        $amountTier = $this->amountTier($amount);
        $signals = $this->signals($eventType, $payload, $ledger, $options, $amount);
        $persistentFailure = $this->persistentFailureDetected($signals, $amount);
        $postWriteValidation = $this->postWriteValidationRequired($amount, $eventType, $payload, $options);
        $criticality = $this->criticality($amount, $eventType, $payload, $signals);
        $maxAttempts = $this->maxAttempts($amount, $eventType, $payload, $signals, $options);
        $quarantineCandidate = $persistentFailure && $this->amountCanReachQuarantine($amount, $signals);

        return new FinanceReliabilityAssessment(
            $amount,
            $amountTier,
            $criticality,
            $maxAttempts,
            $postWriteValidation,
            $persistentFailure,
            $quarantineCandidate,
            $signals,
            $this->thresholds(),
        );
    }

    public function shouldQuarantine(
        PostWriteValidationResult $validation,
        FinanceReliabilityAssessment $assessment,
        ?OperationLedger $ledger = null,
    ): bool {
        unset($ledger);

        if ($validation->passed || !$assessment->quarantineCandidate) {
            return false;
        }

        $signals = $assessment->signals;
        $amount = $assessment->amount;
        $coreCorruption = $this->hasCoreFinancialCorruption($validation);

        if ($amount >= self::AMOUNT_EXTREME) {
            return true;
        }

        if ($amount >= self::AMOUNT_HIGH && $coreCorruption) {
            return true;
        }

        if ($amount >= self::AMOUNT_ELEVATED && $coreCorruption && $this->severePersistence($signals)) {
            return true;
        }

        return $amount >= self::AMOUNT_POST_WRITE_VALIDATION
            && $coreCorruption
            && $this->catastrophicPersistence($signals);
    }

    /**
     * @return array<string, mixed>
     */
    public function thresholds(): array
    {
        return [
            'post_write_validation_amount' => self::AMOUNT_POST_WRITE_VALIDATION,
            'elevated_amount' => self::AMOUNT_ELEVATED,
            'high_amount' => self::AMOUNT_HIGH,
            'extreme_amount' => self::AMOUNT_EXTREME,
            'catastrophic_amount' => self::AMOUNT_CATASTROPHIC,
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
    private function amount(array $payload, array $options): float
    {
        $value = $options['amount']
            ?? $payload['amount']
            ?? $payload['total']
            ?? $payload['value']
            ?? 0;

        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        return max(0.0, (float) $value);
    }

    private function amountTier(float $amount): string
    {
        return match (true) {
            $amount >= self::AMOUNT_CATASTROPHIC => 'catastrophic',
            $amount >= self::AMOUNT_EXTREME => 'extreme',
            $amount >= self::AMOUNT_HIGH => 'high',
            $amount >= self::AMOUNT_ELEVATED => 'elevated',
            $amount >= self::AMOUNT_POST_WRITE_VALIDATION => 'validated',
            default => 'basic',
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function postWriteValidationRequired(float $amount, string $eventType, array $payload, array $options): bool
    {
        if ((bool) ($options['force_post_write_validation'] ?? false)) {
            return true;
        }

        if ($amount >= self::AMOUNT_POST_WRITE_VALIDATION) {
            return true;
        }

        return $this->isReversalLike($eventType, $payload) && (bool) ($options['validate_low_amount_reversals'] ?? false);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     */
    private function criticality(float $amount, string $eventType, array $payload, array $signals): string
    {
        unset($eventType, $payload);

        if ($amount >= self::AMOUNT_EXTREME || (bool) $signals['persistent_failure_detected']) {
            return ReliabilityPolicy::CRITICALITY_CRITICAL;
        }

        if ($amount >= self::AMOUNT_ELEVATED || (int) $signals['metadata_risk_score'] >= 2) {
            return ReliabilityPolicy::CRITICALITY_HIGH;
        }

        return ReliabilityPolicy::CRITICALITY_MEDIUM;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $signals
     * @param array<string, mixed> $options
     */
    private function maxAttempts(float $amount, string $eventType, array $payload, array $signals, array $options): int
    {
        if (isset($options['max_attempts'])) {
            return max(2, min(10, (int) $options['max_attempts']));
        }

        $attempts = match (true) {
            $amount >= self::AMOUNT_CATASTROPHIC => 8,
            $amount >= self::AMOUNT_EXTREME => 6,
            $amount >= self::AMOUNT_HIGH => 5,
            $amount >= self::AMOUNT_ELEVATED => 4,
            $amount >= self::AMOUNT_POST_WRITE_VALIDATION => 3,
            default => 2,
        };

        if ($this->isReversalLike($eventType, $payload)) {
            $attempts++;
        }
        if ((bool) $signals['persistent_failure_detected']) {
            $attempts++;
        }
        if ((int) $signals['metadata_risk_score'] > 0) {
            $attempts += min(2, (int) $signals['metadata_risk_score']);
        }

        return max(2, min(10, $attempts));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function signals(string $eventType, array $payload, ?OperationLedger $ledger, array $options, float $amount): array
    {
        $priorFailures = $this->recentFailedOperations($eventType, $payload, $ledger);
        $retryFailures = $this->recentRetryFailures($eventType, $payload, $ledger);
        $deadLetters = $this->recentDeadLetters($eventType, $payload, $ledger);
        $circuitEvents = $this->recentCircuitInstability($eventType);
        $elapsedSeconds = $this->elapsedSeconds($payload, $ledger, $options);
        $metadataRiskScore = $this->metadataRiskScore($eventType, $payload, $options);

        $signals = [
            'amount' => $amount,
            'prior_failed_operations' => $priorFailures,
            'retry_failures' => $retryFailures,
            'dead_letters' => $deadLetters,
            'circuit_instability_events' => $circuitEvents,
            'elapsed_seconds' => $elapsedSeconds,
            'metadata_risk_score' => $metadataRiskScore,
            'reversal_like' => $this->isReversalLike($eventType, $payload),
            'persistent_failure_detected' => false,
        ];
        $signals['persistent_failure_detected'] = $this->persistentFailureDetected($signals, $amount);

        return $signals;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function persistentFailureDetected(array $signals, float $amount): bool
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
            || ($amount >= self::AMOUNT_EXTREME && $elapsedSeconds >= self::LONG_PROCESSING_SECONDS);
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function severePersistence(array $signals): bool
    {
        return (int) $signals['prior_failed_operations'] >= self::PRIOR_FAILURES_FOR_QUARANTINE
            || (int) $signals['retry_failures'] >= self::RETRY_FAILURES_FOR_QUARANTINE
            || (int) $signals['dead_letters'] > 0
            || (int) $signals['circuit_instability_events'] >= self::CIRCUIT_EVENTS_FOR_QUARANTINE
            || (int) $signals['elapsed_seconds'] >= self::EXTREME_PROCESSING_SECONDS;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function catastrophicPersistence(array $signals): bool
    {
        return (int) $signals['prior_failed_operations'] >= 8
            || (int) $signals['retry_failures'] >= 10
            || (int) $signals['dead_letters'] >= 2
            || (int) $signals['circuit_instability_events'] >= 5
            || (int) $signals['elapsed_seconds'] >= 3600;
    }

    /**
     * @param array<string, mixed> $signals
     */
    private function amountCanReachQuarantine(float $amount, array $signals): bool
    {
        return $amount >= self::AMOUNT_EXTREME
            || ($amount >= self::AMOUNT_HIGH && $this->severePersistence($signals))
            || ($amount >= self::AMOUNT_ELEVATED && $this->catastrophicPersistence($signals));
    }

    private function hasCoreFinancialCorruption(PostWriteValidationResult $validation): bool
    {
        $coreKeys = [
            'amount',
            'account_id',
            'invoice_overpaid',
            'bill_overpaid',
            'invoice_payment_link',
            'bill_payment_link',
            'invoice_status',
            'bill_status',
            'payment',
            'payment_delete',
            'invoice',
            'bill',
        ];

        return array_intersect($coreKeys, array_keys($validation->validationErrors)) !== [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isReversalLike(string $eventType, array $payload): bool
    {
        $direction = strtolower((string) ($payload['direction'] ?? ''));

        return str_contains($eventType, 'delete')
            || str_contains($eventType, 'deleted')
            || str_contains($eventType, 'reversal')
            || str_contains($direction, 'reversal');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recentFailedOperations(string $eventType, array $payload, ?OperationLedger $ledger): int
    {
        $query = OperationLedger::query()
            ->where('domain', 'finance')
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
            ->where('channel', 'like', 'finance%')
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
            ->where('stream', 'finance.ledger')
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
            ->where('channel', 'like', 'finance%')
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
        $flags = [
            $payload['high_risk'] ?? null,
            $payload['external_origin'] ?? null,
            $payload['requires_approval'] ?? null,
            $payload['privileged_actor'] ?? null,
            $options['high_risk'] ?? null,
            $options['external_origin'] ?? null,
            $options['requires_approval'] ?? null,
            $options['privileged_actor'] ?? null,
        ];

        foreach ($flags as $flag) {
            if ((bool) $flag) {
                $score++;
            }
        }

        $transactionType = strtolower((string) ($payload['transaction_type'] ?? $payload['direction'] ?? $eventType));
        foreach (['refund', 'reversal', 'transfer', 'banking', 'gateway', 'payroll', 'tax'] as $needle) {
            if (str_contains($transactionType, $needle)) {
                $score++;
                break;
            }
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
        $value = $payload['invoice_id']
            ?? $payload['bill_id']
            ?? $payload['payment_id']
            ?? $payload['subject_id']
            ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function operationTypeFromEvent(string $eventType): string
    {
        return str_replace(['.payment_created', '.payment_deleted'], ['.payment.create', '.payment.delete'], $eventType);
    }

    private function breakerSegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }
}
