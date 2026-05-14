<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\CircuitBreakerCall;
use App\Models\CircuitBreakerState;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;
use Throwable;

class CircuitBreaker extends AbstractReliabilityGuard
{
    public function __construct(
        private string $breakerKey,
        private string $name,
        private bool $enabled,
        private int $slidingWindowSize,
        private int $slidingWindowSeconds,
        private float $failureRateThreshold,
        private int $minimumCalls,
        private int $openStateSeconds,
        private int $halfOpenAllowedCalls,
        private float $halfOpenSuccessThreshold,
        private bool $halfOpenConservative,
        string $criticality,
        string $channel,
        ?OperationLedger $operationLedger = null,
        ?OutboxMessage $outboxMessage = null,
        ?OperationalEventService $events = null,
        private ?int $slowCallDurationMs = null,
        private ?float $slowCallRateThreshold = null,
    ) {
        parent::__construct($criticality, $channel, $operationLedger, $outboxMessage, $events);
        $this->slidingWindowSize = max(1, $slidingWindowSize);
        $this->slidingWindowSeconds = max(1, $slidingWindowSeconds);
        $this->failureRateThreshold = max(1.0, min(100.0, $failureRateThreshold));
        $this->minimumCalls = max(1, $minimumCalls);
        $this->openStateSeconds = max(1, $openStateSeconds);
        $this->halfOpenAllowedCalls = max(1, $halfOpenAllowedCalls);
        // Floor at 1.0 (sanity, not policy). Defaults are tier-driven via ReliabilityPolicy::defaultHalfOpenSuccessThreshold;
        // a tighter floor here would prevent 'trivial' tiers from running near-zero-threshold probes intentionally.
        $this->halfOpenSuccessThreshold = max(1.0, min(100.0, $halfOpenSuccessThreshold));
        if ($this->slowCallDurationMs !== null) {
            $this->slowCallDurationMs = max(1, $this->slowCallDurationMs);
        }
        if ($this->slowCallRateThreshold !== null) {
            $this->slowCallRateThreshold = max(1.0, min(100.0, $this->slowCallRateThreshold));
        }
    }

    public static function builder(string $breakerKey): CircuitBreakerBuilder
    {
        return new CircuitBreakerBuilder($breakerKey);
    }

    /**
     * @template TResult
     *
     * @param callable(): TResult $callback
     * @param array<string, mixed> $context
     * @return TResult
     *
     * @throws Throwable
     */
    public function call(callable $callback, array $context = []): mixed
    {
        if (!$this->enabled) {
            return $callback();
        }

        $state = $this->state();
        $state = $this->prepareState($state, $context);

        [$permitted, $permit] = $this->tryAcquirePermit($state, $context);

        if (!$permitted) {
            $this->recordCall($state, ReliabilityPolicy::CIRCUIT_CALL_REJECTED, $state->state, null, null, $context);
            $this->emitReliabilityEvent('reliability.circuit.rejected', 'Circuit breaker rejected a guarded call', [
                'breaker_key' => $this->breakerKey,
                'name' => $this->name,
                'state' => $state->state,
                'next_attempt_at' => $state->next_attempt_at?->toIso8601String(),
                'context' => $context,
            ], 'warning');

            throw new CircuitBreakerOpenException("Circuit breaker [{$this->breakerKey}] is {$state->state}.");
        }

        $stateBefore = (string) $state->state;
        $started = microtime(true);

        try {
            $result = $callback();
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $call = $this->finalizeCall($state, $permit, ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED, $stateBefore, $durationMs, null, $context);
            $this->evaluateAfterCall($state, $call, null, $context);

            return $result;
        } catch (Throwable $throwable) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $call = $this->finalizeCall($state, $permit, ReliabilityPolicy::CIRCUIT_CALL_FAILED, $stateBefore, $durationMs, $throwable, $context);
            $this->evaluateAfterCall($state, $call, $throwable, $context);

            throw $throwable;
        }
    }

    private function state(): CircuitBreakerState
    {
        $state = CircuitBreakerState::firstOrCreate(
            ['breaker_key' => $this->breakerKey],
            [
                'name' => $this->name,
                'domain' => $this->channel,
                'criticality' => $this->criticality,
                'state' => ReliabilityPolicy::CIRCUIT_CLOSED,
                'closed_at' => now(),
                'expires_at' => now()->addDays(ReliabilityPolicy::retentionDays('circuit_breaker_state', $this->criticality)),
            ]
        );

        $state->forceFill([
            'name' => $this->name,
            'domain' => $this->channel,
            'criticality' => $this->criticality,
            'sliding_window_size' => $this->slidingWindowSize,
            'sliding_window_seconds' => $this->slidingWindowSeconds,
            'failure_rate_threshold' => $this->failureRateThreshold,
            'minimum_calls' => $this->minimumCalls,
            'open_state_seconds' => $this->openStateSeconds,
            'half_open_allowed_calls' => $this->halfOpenAllowedCalls,
            'half_open_success_threshold' => $this->halfOpenSuccessThreshold,
            'half_open_conservative' => $this->halfOpenConservative,
            'slow_call_duration_ms' => $this->slowCallDurationMs,
            'slow_call_rate_threshold' => $this->slowCallRateThreshold,
            'config' => [
                'enabled' => $this->enabled,
                'source' => static::class,
            ],
        ])->save();

        return $state;
    }

    /**
     * Transition open→half_open under a row lock, so concurrent workers don't both write half_opened_at.
     *
     * @param array<string, mixed> $context
     */
    private function prepareState(CircuitBreakerState $state, array $context): CircuitBreakerState
    {
        if (!($state->state === ReliabilityPolicy::CIRCUIT_OPEN && $state->next_attempt_at && $state->next_attempt_at->lte(now()))) {
            return $state;
        }

        return DB::transaction(function () use ($state, $context): CircuitBreakerState {
            $locked = CircuitBreakerState::query()->lockForUpdate()->find($state->id);

            if ($locked === null) {
                return $state;
            }
            if ($locked->state !== ReliabilityPolicy::CIRCUIT_OPEN) {
                return $locked;
            }
            if (!$locked->next_attempt_at || $locked->next_attempt_at->gt(now())) {
                return $locked;
            }

            return $this->transition($locked, ReliabilityPolicy::CIRCUIT_HALF_OPEN, 'Open duration elapsed; trying half-open probes.', $context);
        });
    }

    /**
     * Atomically attempt to acquire a half-open probe permit.
     *
     * Counting only completed calls (the previous behavior) admits unbounded concurrent probes:
     * N parallel requests all see 0 completed and all are admitted. Resilience4j uses an atomic
     * semaphore decremented at admission, not at completion. Here we approximate that by writing
     * a 'permitted' call row inside a transaction with lockForUpdate on the state row, so
     * admissions across workers are serialized through the row lock.
     *
     * @param  array<string, mixed>  $context
     * @return array{0: bool, 1: ?CircuitBreakerCall}  [permitted, permit row (null for closed/disabled/rejected)]
     */
    private function tryAcquirePermit(CircuitBreakerState $state, array $context): array
    {
        if ($state->state === ReliabilityPolicy::CIRCUIT_CLOSED || $state->state === ReliabilityPolicy::CIRCUIT_DISABLED) {
            return [true, null];
        }

        if ($state->state === ReliabilityPolicy::CIRCUIT_OPEN) {
            return [false, null];
        }

        return DB::transaction(function () use ($state, $context): array {
            $locked = CircuitBreakerState::query()->lockForUpdate()->find($state->id);

            if ($locked === null) {
                return [false, null];
            }

            if ($locked->state === ReliabilityPolicy::CIRCUIT_OPEN) {
                return [false, null];
            }

            if ($locked->state === ReliabilityPolicy::CIRCUIT_CLOSED || $locked->state === ReliabilityPolicy::CIRCUIT_DISABLED) {
                return [true, null];
            }

            $admitted = $locked->calls()
                ->where('occurred_at', '>=', $locked->half_opened_at ?? now()->subSecond())
                ->whereIn('status', [
                    ReliabilityPolicy::CIRCUIT_CALL_PERMITTED,
                    ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED,
                    ReliabilityPolicy::CIRCUIT_CALL_FAILED,
                ])
                ->count();

            if ($admitted >= $this->halfOpenAllowedCalls) {
                return [false, null];
            }

            $permit = CircuitBreakerCall::create([
                'circuit_breaker_state_id' => $locked->id,
                'breaker_key' => $this->breakerKey,
                'state_before' => $locked->state,
                'state_after' => $locked->state,
                'status' => ReliabilityPolicy::CIRCUIT_CALL_PERMITTED,
                'duration_ms' => null,
                'error_class' => null,
                'error_message' => null,
                'context' => $context ?: null,
                'occurred_at' => now(),
                'expires_at' => now()->addDays(ReliabilityPolicy::retentionDays('circuit_breaker_call', $this->criticality)),
            ]);

            return [true, $permit];
        });
    }

    /**
     * Mutate the half-open permit row to its final status, or insert a fresh completion row for closed/disabled state.
     *
     * @param array<string, mixed> $context
     */
    private function finalizeCall(
        CircuitBreakerState $state,
        ?CircuitBreakerCall $permit,
        string $status,
        string $stateBefore,
        ?int $durationMs,
        ?Throwable $throwable,
        array $context,
    ): CircuitBreakerCall {
        if ($permit !== null) {
            $permit->forceFill([
                'state_before' => $stateBefore,
                'state_after' => $state->state,
                'status' => $status,
                'duration_ms' => $durationMs,
                'error_class' => $throwable ? $throwable::class : null,
                'error_message' => $throwable?->getMessage(),
            ])->save();

            return $permit;
        }

        return $this->recordCall($state, $status, $stateBefore, $durationMs, $throwable, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function recordCall(
        CircuitBreakerState $state,
        string $status,
        string $stateBefore,
        ?int $durationMs,
        ?Throwable $throwable,
        array $context,
    ): CircuitBreakerCall {
        return CircuitBreakerCall::create([
            'circuit_breaker_state_id' => $state->id,
            'breaker_key' => $this->breakerKey,
            'state_before' => $stateBefore,
            'state_after' => $state->state,
            'status' => $status,
            'duration_ms' => $durationMs,
            'error_class' => $throwable ? $throwable::class : null,
            'error_message' => $throwable?->getMessage(),
            'context' => $context ?: null,
            'occurred_at' => now(),
            'expires_at' => now()->addDays(ReliabilityPolicy::retentionDays('circuit_breaker_call', $this->criticality)),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function evaluateAfterCall(CircuitBreakerState $state, CircuitBreakerCall $call, ?Throwable $throwable, array $context): void
    {
        $state->refresh();

        if ($state->state === ReliabilityPolicy::CIRCUIT_CLOSED) {
            $metrics = $this->closedWindowMetrics($state);
            if ($metrics['total'] >= $this->minimumCalls) {
                if ($metrics['failure_rate'] >= $this->failureRateThreshold) {
                    $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, $throwable?->getMessage() ?? 'Failure rate threshold exceeded.', array_merge($context, $metrics));
                } elseif ($this->slowCallRateThreshold !== null && $metrics['slow_rate'] >= $this->slowCallRateThreshold) {
                    $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, 'Slow-call rate threshold exceeded.', array_merge($context, $metrics));
                }
            }
        } elseif ($state->state === ReliabilityPolicy::CIRCUIT_HALF_OPEN) {
            $metrics = $this->halfOpenMetrics($state);
            if ($this->halfOpenConservative && $metrics['failed'] > 0) {
                $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, $throwable?->getMessage() ?? 'Half-open probe failed.', array_merge($context, $metrics));
            } elseif ($this->isThresholdUnreachable($metrics)) {
                // Even if remaining probes all succeed, the success rate cannot reach the threshold — re-open eagerly instead of wasting probes.
                $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, 'Half-open success threshold became unreachable.', array_merge($context, $metrics));
            } elseif ($metrics['total'] >= $this->halfOpenAllowedCalls) {
                $successRate = $metrics['total'] > 0 ? ($metrics['succeeded'] / $metrics['total']) * 100 : 0.0;
                $state = $successRate >= $this->halfOpenSuccessThreshold
                    ? $this->transition($state, ReliabilityPolicy::CIRCUIT_CLOSED, 'Half-open probes passed.', array_merge($context, $metrics, ['success_rate' => $successRate]))
                    : $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, 'Half-open probes did not meet success threshold.', array_merge($context, $metrics, ['success_rate' => $successRate]));
            }
        }

        $call->forceFill(['state_after' => $state->state])->save();
    }

    /**
     * @return array{total: int, failed: int, succeeded: int, failure_rate: float, slow: int, slow_rate: float}
     */
    private function closedWindowMetrics(CircuitBreakerState $state): array
    {
        $calls = $state->calls()
            ->where('occurred_at', '>=', now()->subSeconds($this->slidingWindowSeconds))
            ->whereIn('status', [ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED, ReliabilityPolicy::CIRCUIT_CALL_FAILED])
            ->latest('occurred_at')
            ->limit($this->slidingWindowSize)
            ->get();

        $total = $calls->count();
        $failed = $calls->where('status', ReliabilityPolicy::CIRCUIT_CALL_FAILED)->count();
        $slow = $this->slowCallDurationMs !== null
            ? $calls->filter(fn($c) => $c->duration_ms !== null && $c->duration_ms >= $this->slowCallDurationMs)->count()
            : 0;

        return [
            'total' => $total,
            'failed' => $failed,
            'succeeded' => $calls->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)->count(),
            'failure_rate' => $total > 0 ? ($failed / $total) * 100 : 0.0,
            'slow' => $slow,
            'slow_rate' => $total > 0 ? ($slow / $total) * 100 : 0.0,
        ];
    }

    /**
     * Returns true when the success threshold cannot be met even if every remaining probe succeeds.
     * Used to short-circuit a doomed half-open window rather than waste probes.
     *
     * @param array{total: int, failed: int, succeeded: int} $metrics
     */
    private function isThresholdUnreachable(array $metrics): bool
    {
        $remaining = max(0, $this->halfOpenAllowedCalls - $metrics['total']);
        $bestPossibleSuccess = $metrics['succeeded'] + $remaining;
        $bestPossibleRate = $this->halfOpenAllowedCalls > 0
            ? ($bestPossibleSuccess / $this->halfOpenAllowedCalls) * 100
            : 0.0;

        return $bestPossibleRate < $this->halfOpenSuccessThreshold;
    }

    /**
     * @return array{total: int, failed: int, succeeded: int}
     */
    private function halfOpenMetrics(CircuitBreakerState $state): array
    {
        $calls = $state->calls()
            ->where('occurred_at', '>=', $state->half_opened_at ?? now()->subSecond())
            ->whereIn('status', [ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED, ReliabilityPolicy::CIRCUIT_CALL_FAILED])
            ->get();

        return [
            'total' => $calls->count(),
            'failed' => $calls->where('status', ReliabilityPolicy::CIRCUIT_CALL_FAILED)->count(),
            'succeeded' => $calls->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)->count(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function transition(CircuitBreakerState $state, string $newState, string $reason, array $context): CircuitBreakerState
    {
        $oldState = (string) $state->state;
        if ($oldState === $newState) {
            return $state;
        }

        $payload = [
            'state' => $newState,
            'last_failure_message' => $reason,
        ];

        if ($newState === ReliabilityPolicy::CIRCUIT_OPEN) {
            $payload['opened_at'] = now();
            // ±15% jitter decorrelates re-attempts across workers/instances after a correlated failure.
            $payload['next_attempt_at'] = now()->addSeconds(ReliabilityPolicy::jitter($this->openStateSeconds));
        } elseif ($newState === ReliabilityPolicy::CIRCUIT_HALF_OPEN) {
            $payload['half_opened_at'] = now();
        } elseif ($newState === ReliabilityPolicy::CIRCUIT_CLOSED) {
            $payload['closed_at'] = now();
            $payload['opened_at'] = null;
            $payload['half_opened_at'] = null;
            $payload['next_attempt_at'] = null;
            $payload['last_failure_message'] = null;
        }

        $state->forceFill($payload)->save();

        $eventContext = [
            'breaker_key' => $this->breakerKey,
            'name' => $this->name,
            'from' => $oldState,
            'to' => $newState,
            'reason' => $reason,
            'context' => $context,
        ];

        $this->emitReliabilityEvent('reliability.circuit.state_changed', 'Circuit breaker changed state', $eventContext, $newState === ReliabilityPolicy::CIRCUIT_OPEN ? 'error' : 'notice');

        if ($oldState === ReliabilityPolicy::CIRCUIT_CLOSED && $newState === ReliabilityPolicy::CIRCUIT_OPEN) {
            $this->emitReliabilityEvent('reliability.circuit.opened', 'Circuit breaker opened from closed state', $eventContext, 'error');
        }

        return $state->fresh() ?? $state;
    }
}
