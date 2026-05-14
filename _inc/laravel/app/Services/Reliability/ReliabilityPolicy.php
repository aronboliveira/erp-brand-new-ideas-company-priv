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
    public const QUARANTINE_ACTION_DISMISSED = 'dismissed';

    public const CIRCUIT_CLOSED = 'closed';
    public const CIRCUIT_OPEN = 'open';
    public const CIRCUIT_HALF_OPEN = 'half_open';
    public const CIRCUIT_DISABLED = 'disabled';

    public const CIRCUIT_CALL_PERMITTED = 'permitted';
    public const CIRCUIT_CALL_REJECTED = 'rejected';
    public const CIRCUIT_CALL_SUCCEEDED = 'succeeded';
    public const CIRCUIT_CALL_FAILED = 'failed';

    /**
     * Default allowlist of "transient" exceptions for Retry when no explicit retryOn() is configured.
     * Adding to this list is a deliberate policy change — only exceptions that are typically
     * recoverable by re-trying belong here. Validation / authorization / not-found should NOT.
     *
     * @var list<class-string<\Throwable>>
     */
    public const TRANSIENT_EXCEPTIONS = [
        \PDOException::class,
        \Illuminate\Database\QueryException::class,
        \Illuminate\Http\Client\ConnectionException::class,
        \Illuminate\Http\Client\RequestException::class,
        \GuzzleHttp\Exception\ConnectException::class,
        \GuzzleHttp\Exception\ServerException::class,
    ];

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
        $base = (int) min(self::RETRY_MAX_DELAY_SECONDS, self::RETRY_BASE_DELAY_SECONDS * (2 ** $exponent));

        return self::jitter($base);
    }

    /**
     * ±15% jitter around a base interval, floored at 1.
     * Decorrelates timed events across concurrent workers under correlated-failure scenarios.
     */
    public static function jitter(int $baseSeconds, float $factor = 0.15): int
    {
        $base = max(1, $baseSeconds);
        $delta = (int) floor($base * max(0.0, $factor));
        if ($delta === 0) {
            return $base;
        }

        return max(1, $base + random_int(-$delta, $delta));
    }

    public static function circuitBreakerRequired(string $criticality): bool
    {
        return self::CRITICALITY_ORDER[self::normalizeCriticality($criticality)]
            >= self::CRITICALITY_ORDER[self::CRITICALITY_MEDIUM];
    }

    /**
     * How many times CriticalOperationService::run() should let Laravel retry the commit transaction
     * on a concurrency error (SQLSTATE 40001 deadlock, lock-wait-timeout, "Deadlock found", etc).
     * Laravel's DB::transaction($callback, $attempts) only retries on concurrency errors — business
     * exceptions and constraint violations still abort on the first throw.
     *
     * Note: MySQL's `SET TRANSACTION ISOLATION LEVEL X` (non-SESSION) applies only to the next
     * transaction, so retries beyond attempt 1 fall back to the session-default isolation. Callers
     * that need strict SERIALIZABLE across all attempts should set session isolation explicitly.
     */
    public static function commitRetryAttempts(string $criticality): int
    {
        return match (self::normalizeCriticality($criticality)) {
            self::CRITICALITY_CRITICAL => 5,
            self::CRITICALITY_HIGH => 4,
            self::CRITICALITY_MEDIUM => 3,
            default => 1,
        };
    }

    /**
     * Age threshold beyond which a ledger row stuck in 'started' should be considered orphaned
     * and swept to 'failed' by the GC sweeper. Null = never sweep (low/trivial don't write ledgers).
     *
     * Tier rationale: critical operations should commit fast (≤2min), high within ~5min, medium ~10min.
     * Anything still in 'started' past these windows is almost certainly a crashed PHP process between
     * `recordStep(RUNNING)` and `DB::transaction()` entry — the small unreplicable window F1+F2 leave open.
     */
    public static function orphanedLedgerTtlSeconds(string $criticality): ?int
    {
        return match (self::normalizeCriticality($criticality)) {
            self::CRITICALITY_CRITICAL => 120,
            self::CRITICALITY_HIGH => 300,
            self::CRITICALITY_MEDIUM => 600,
            default => null,
        };
    }

    /**
     * Q3: single source of truth for the domain → (decision, details) mapping previously
     * hardcoded inside QuarantineRemediationJudge::decide(). Returns a (decision, details) tuple
     * keyed by `decision`/`details` so the judge can wrap it in a QuarantineDecision DTO.
     *
     * Only finance routes to rollback today; everything else routes to manual_review because
     * employee/inventory/CRM/planning/heavy-IO state typically needs a human decision before
     * automatic reversal. Add new domains here, not in the judge class.
     *
     * @return array{decision: string, details: string}
     */
    public static function quarantineDecisionFor(string $domain): array
    {
        return match (strtolower(trim($domain))) {
            'finance' => [
                'decision' => self::QUARANTINE_DECISION_ROLLBACK,
                'details' => 'Finance post-write validation failed after persistent instability signals; the domain write was rolled back before outbox dispatch.',
            ],
            'hrm' => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'HRM post-write validation failed after persistent retry/circuit instability; keep the source signal in manual review before further employee-impacting actions.',
            ],
            'warehouse' => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'Warehouse post-write validation failed after persistent retry/circuit instability; keep the stock or product signal in manual review before further inventory-impacting actions.',
            ],
            'crm' => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'CRM post-write validation failed after persistent retry/circuit instability; keep the lead, deal, or access signal in manual review before further customer-impacting actions.',
            ],
            'planning' => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'Planning post-write validation failed after persistent retry/circuit instability; keep the final project, milestone, or task signal in manual review before further project-impacting actions.',
            ],
            'heavy_io' => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'Heavy I/O integration validation failed after persistent retry/circuit instability; keep the import, export, webhook, or callback signal in manual review before further downstream processing.',
            ],
            default => [
                'decision' => self::QUARANTINE_DECISION_MANUAL_REVIEW,
                'details' => 'The quarantine decision is ambiguous outside the mature domain slices and requires manual review.',
            ],
        };
    }

    public static function defaultHalfOpenSuccessThreshold(?string $criticality): float
    {
        return match (self::normalizeCriticality($criticality)) {
            self::CRITICALITY_CRITICAL, self::CRITICALITY_HIGH => 100.0,
            self::CRITICALITY_MEDIUM => 75.0,
            self::CRITICALITY_LOW => 25.0,
            self::CRITICALITY_TRIVIAL => 10.0,
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
