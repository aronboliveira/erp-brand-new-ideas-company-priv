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

    private const CRITICALITY_ORDER = [
        self::CRITICALITY_TRIVIAL => 0,
        self::CRITICALITY_LOW => 1,
        self::CRITICALITY_MEDIUM => 2,
        self::CRITICALITY_HIGH => 3,
        self::CRITICALITY_CRITICAL => 4,
    ];

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
            'operational_event' => match ($criticality) {
                self::CRITICALITY_CRITICAL => 365,
                self::CRITICALITY_HIGH => 180,
                self::CRITICALITY_MEDIUM => 30,
                default => 7,
            },
            default => 30,
        };
    }

    public static function retryDelaySeconds(int $attempt): int
    {
        $attempt = max(1, $attempt);

        return min(300, $attempt * 30);
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
