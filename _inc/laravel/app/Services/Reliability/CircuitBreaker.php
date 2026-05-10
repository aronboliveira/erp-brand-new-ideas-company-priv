<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\CircuitBreakerCall;
use App\Models\CircuitBreakerState;
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
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
    ) {
        parent::__construct($criticality, $channel, $operationLedger, $outboxMessage, $events);
        $this->slidingWindowSize = max(1, $slidingWindowSize);
        $this->slidingWindowSeconds = max(1, $slidingWindowSeconds);
        $this->failureRateThreshold = max(1.0, min(100.0, $failureRateThreshold));
        $this->minimumCalls = max(1, $minimumCalls);
        $this->openStateSeconds = max(1, $openStateSeconds);
        $this->halfOpenAllowedCalls = max(1, $halfOpenAllowedCalls);
        $this->halfOpenSuccessThreshold = max(25.0, min(100.0, $halfOpenSuccessThreshold));
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

        if (!$this->allowsCall($state)) {
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
            $call = $this->recordCall($state, ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED, $stateBefore, $durationMs, null, $context);
            $this->evaluateAfterCall($state, $call, null, $context);

            return $result;
        } catch (Throwable $throwable) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $call = $this->recordCall($state, ReliabilityPolicy::CIRCUIT_CALL_FAILED, $stateBefore, $durationMs, $throwable, $context);
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
            'config' => [
                'enabled' => $this->enabled,
                'source' => static::class,
            ],
        ])->save();

        return $state;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function prepareState(CircuitBreakerState $state, array $context): CircuitBreakerState
    {
        if ($state->state === ReliabilityPolicy::CIRCUIT_OPEN && $state->next_attempt_at && $state->next_attempt_at->lte(now())) {
            return $this->transition($state, ReliabilityPolicy::CIRCUIT_HALF_OPEN, 'Open duration elapsed; trying half-open probes.', $context);
        }

        return $state;
    }

    private function allowsCall(CircuitBreakerState $state): bool
    {
        if ($state->state === ReliabilityPolicy::CIRCUIT_CLOSED || $state->state === ReliabilityPolicy::CIRCUIT_DISABLED) {
            return true;
        }

        if ($state->state === ReliabilityPolicy::CIRCUIT_OPEN) {
            return false;
        }

        $completedHalfOpen = $state->calls()
            ->where('occurred_at', '>=', $state->half_opened_at ?? now()->subSecond())
            ->whereIn('status', [ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED, ReliabilityPolicy::CIRCUIT_CALL_FAILED])
            ->count();

        return $completedHalfOpen < $this->halfOpenAllowedCalls;
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
            if ($metrics['total'] >= $this->minimumCalls && $metrics['failure_rate'] >= $this->failureRateThreshold) {
                $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, $throwable?->getMessage() ?? 'Failure rate threshold exceeded.', array_merge($context, $metrics));
            }
        } elseif ($state->state === ReliabilityPolicy::CIRCUIT_HALF_OPEN) {
            $metrics = $this->halfOpenMetrics($state);
            if ($this->halfOpenConservative && $metrics['failed'] > 0) {
                $state = $this->transition($state, ReliabilityPolicy::CIRCUIT_OPEN, $throwable?->getMessage() ?? 'Half-open probe failed.', array_merge($context, $metrics));
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
     * @return array{total: int, failed: int, succeeded: int, failure_rate: float}
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

        return [
            'total' => $total,
            'failed' => $failed,
            'succeeded' => $calls->where('status', ReliabilityPolicy::CIRCUIT_CALL_SUCCEEDED)->count(),
            'failure_rate' => $total > 0 ? ($failed / $total) * 100 : 0.0,
        ];
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
            $payload['next_attempt_at'] = now()->addSeconds($this->openStateSeconds);
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
