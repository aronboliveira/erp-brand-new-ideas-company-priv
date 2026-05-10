<?php

namespace App\Services\Reliability;

final class ReliabilityPolicy
{
    public const CRITICALITY_TRIVIAL = 'trivial';
    public const CRITICALITY_LOW = 'low';
    public const CRITICALITY_MEDIUM = 'medium';
    public const CRITICALITY_HIGH = 'high';
    public const CRITICALITY_CRITICAL = 'critical';

    public const STORAGE_MEMORY = 'memory';
    public const STORAGE_DATABASE = 'database';

    public const STATUS_STARTED = 'started';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_POSTED_TO_LEDGER = 'posted_to_ledger';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPENSATING = 'compensating';
    public const STATUS_COMPENSATED = 'compensated';
    public const STATUS_CLOSED = 'closed';

    public const STEP_PENDING = 'pending';
    public const STEP_RUNNING = 'running';
    public const STEP_SUCCEEDED = 'succeeded';
    public const STEP_FAILED = 'failed';
    public const STEP_SKIPPED = 'skipped';
    public const STEP_COMPENSATED = 'compensated';

    public const OUTBOX_PENDING = 'pending';
    public const OUTBOX_READY = 'ready';
    public const OUTBOX_DISPATCHED = 'dispatched';
    public const OUTBOX_FAILED = 'failed';
    public const OUTBOX_DEAD_LETTER = 'dead_letter';
    public const OUTBOX_CANCELLED = 'cancelled';

    public const QUARANTINE_PENDING_REVIEW = 'pending_review';
    public const QUARANTINE_MANUAL_REVIEW = 'manual_review';
    public const QUARANTINE_RECOVERED = 'recovered';
    public const QUARANTINE_ROLLED_BACK = 'rolled_back';
    public const QUARANTINE_DISMISSED = 'dismissed';

    public const QUARANTINE_DECISION_AUTO_RECOVER = 'auto_recover';
    public const QUARANTINE_DECISION_MANUAL_REVIEW = 'manual_review';
    public const QUARANTINE_DECISION_ROLLBACK = 'rollback';

    public const QUARANTINE_ACTION_QUARANTINED = 'quarantined';
    public const QUARANTINE_ACTION_JUDGE_DECISION = 'judge_decision';
    public const QUARANTINE_ACTION_RECOVERED = 'recovered';
    public const QUARANTINE_ACTION_ROLLED_BACK = 'rolled_back';
    public const QUARANTINE_ACTION_MANUAL_REVIEW_REQUESTED = 'manual_review_requested';

    public const CIRCUIT_CLOSED = 'closed';
    public const CIRCUIT_OPEN = 'open';
    public const CIRCUIT_HALF_OPEN = 'half_open';
    public const CIRCUIT_DISABLED = 'disabled';

    public const CIRCUIT_CALL_PERMITTED = 'permitted';
    public const CIRCUIT_CALL_REJECTED = 'rejected';
    public const CIRCUIT_CALL_SUCCEEDED = 'succeeded';
    public const CIRCUIT_CALL_FAILED = 'failed';

    private const CRITICALITY_ORDER = [
        self::CRITICALITY_TRIVIAL => 0,
        self::CRITICALITY_LOW => 1,
        self::CRITICALITY_MEDIUM => 2,
        self::CRITICALITY_HIGH => 3,
        self::CRITICALITY_CRITICAL => 4,
    ];

    private const RETRY_BASE_DELAY_SECONDS = 30;

    private const RETRY_MAX_DELAY_SECONDS = 300;

    public static function normalizeCriticality(?string $criticality): string
    {
        $normalized = strtolower(trim((string) ($criticality ?: self::CRITICALITY_MEDIUM)));

        return array_key_exists($normalized, self::CRITICALITY_ORDER)
            ? $normalized
            : self::CRITICALITY_MEDIUM;
    }

    public static function shouldPersist(string $criticality): bool
    {
        return self::CRITICALITY_ORDER[self::normalizeCriticality($criticality)]
            >= self::CRITICALITY_ORDER[self::CRITICALITY_MEDIUM];
    }

    public static function ledgerRequired(string $criticality): bool
    {
        return self::shouldPersist($criticality);
    }

    public static function storageMode(string $criticality, ?string $forced = null): string
    {
        if ($forced === self::STORAGE_MEMORY || $forced === self::STORAGE_DATABASE) {
            return $forced;
        }

        return self::shouldPersist($criticality)
            ? self::STORAGE_DATABASE
            : self::STORAGE_MEMORY;
    }

    public static function defaultIsolationLevel(?string $criticality): ?string
    {
        return match (self::normalizeCriticality($criticality)) {
            self::CRITICALITY_CRITICAL => 'SERIALIZABLE',
            self::CRITICALITY_HIGH => 'REPEATABLE READ',
            self::CRITICALITY_MEDIUM => 'READ COMMITTED',
            default => null,
        };
    }

    public static function retentionDays(string $kind, string $criticality): int
    {
        $criticality = self::normalizeCriticality($criticality);

        return match ($kind) {
            'operation_ledger' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 2555,
                self::CRITICALITY_HIGH => 1825,
                default => 365,
            },
            'operation_step' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 730,
                self::CRITICALITY_HIGH => 365,
                default => 180,
            },
            'outbox_message', 'inbox_message' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 365,
                self::CRITICALITY_HIGH => 180,
                self::CRITICALITY_MEDIUM => 90,
                default => 30,
            },
            'operational_event', 'circuit_breaker_call' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 365,
                self::CRITICALITY_HIGH => 180,
                self::CRITICALITY_MEDIUM => 30,
                default => 7,
            },
            'operation_quarantine' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 2555,
                self::CRITICALITY_HIGH => 1825,
                default => 365,
            },
            'circuit_breaker_state' => match ($criticality) {
                self::CRITICALITY_CRITICAL, self::CRITICALITY_HIGH => 365,
                self::CRITICALITY_MEDIUM => 180,
                default => 30,
            },
            default => 30,
        };
    }

    public static function retryDelaySeconds(int $attempt): int
    {
        $attempt = max(1, $attempt);
        $exponent = min(10, $attempt - 1);

        return (int) min(self::RETRY_MAX_DELAY_SECONDS, self::RETRY_BASE_DELAY_SECONDS * (2 ** $exponent));
    }

    public static function circuitBreakerRequired(string $criticality): bool
    {
        return self::CRITICALITY_ORDER[self::normalizeCriticality($criticality)]
            >= self::CRITICALITY_ORDER[self::CRITICALITY_MEDIUM];
    }

    public static function defaultHalfOpenSuccessThreshold(?string $criticality): float
    {
        return match (self::normalizeCriticality($criticality)) {
            self::CRITICALITY_CRITICAL, self::CRITICALITY_HIGH => 100.0,
            self::CRITICALITY_MEDIUM => 75.0,
            self::CRITICALITY_LOW => 25.0,
            default => 25.0,
        };
    }

    public static function highestCriticality(iterable $criticalities): string
    {
        $highest = self::CRITICALITY_TRIVIAL;

        foreach ($criticalities as $criticality) {
            $normalized = self::normalizeCriticality((string) $criticality);
            if (self::CRITICALITY_ORDER[$normalized] > self::CRITICALITY_ORDER[$highest]) {
                $highest = $normalized;
            }
        }

        return $highest;
    }
}
