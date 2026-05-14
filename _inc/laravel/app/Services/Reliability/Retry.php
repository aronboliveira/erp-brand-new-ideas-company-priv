<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use ReflectionFunction;
use Throwable;

class Retry extends AbstractReliabilityGuard
{
    /**
     * @param array<int, class-string<Throwable>> $retryOn
     * @param array<int, class-string<Throwable>> $abortOn
     * @param array<class-string<Throwable>, callable(Throwable, int, array<string, mixed>): bool> $exceptionHandlers
     * @param callable(int, Throwable, array<string, mixed>): int|null $intervalResolver
     * @param callable(int, Throwable, int, array<string, mixed>): void|null $intervalCallback
     */
    public function __construct(
        private string $name,
        private int $maxAttempts,
        private array $retryOn,
        private array $abortOn,
        private array $exceptionHandlers,
        private mixed $intervalResolver,
        private mixed $intervalCallback,
        string $criticality,
        string $channel,
        ?OperationLedger $operationLedger = null,
        ?OutboxMessage $outboxMessage = null,
        ?OperationalEventService $events = null,
        private bool $sleepBetweenAttempts = true,
        private mixed $recover = null,
        private bool $intervalIsMillis = false,
    ) {
        parent::__construct($criticality, $channel, $operationLedger, $outboxMessage, $events);
        $this->maxAttempts = max(1, $maxAttempts);
    }

    public static function builder(string $name): RetryBuilder
    {
        return new RetryBuilder($name);
    }

    /**
     * @template TResult
     *
     * @param callable(int): TResult $callback
     * @param array<string, mixed> $context
     * @return TResult
     *
     * @throws Throwable
     */
    public function run(callable $callback, array $context = []): mixed
    {
        // Reflect once per run() — the callback identity is stable across attempts in a single retry session.
        $passAttempt = (new ReflectionFunction(\Closure::fromCallable($callback)))->getNumberOfParameters() >= 1;
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $result = $passAttempt ? $callback($attempt) : $callback();
                $this->emitReliabilityEvent('reliability.retry.success', 'Retry guarded call succeeded', [
                    'name' => $this->name,
                    'attempt' => $attempt,
                    'max_attempts' => $this->maxAttempts,
                    'context' => $context,
                ], 'info');

                return $result;
            } catch (Throwable $throwable) {
                $retryable = $attempt < $this->maxAttempts && $this->shouldRetry($throwable, $attempt, $context);

                if (!$retryable) {
                    $this->emitReliabilityEvent('reliability.retry.failed', 'Retry guarded call failed', [
                        'name' => $this->name,
                        'attempt' => $attempt,
                        'max_attempts' => $this->maxAttempts,
                        'exception' => $throwable::class,
                        'message' => $throwable->getMessage(),
                        'context' => $context,
                    ], 'error');

                    if (is_callable($this->recover)) {
                        $this->emitReliabilityEvent('reliability.retry.recovered', 'Retry exhausted; recover fallback handled the failure', [
                            'name' => $this->name,
                            'attempt' => $attempt,
                            'max_attempts' => $this->maxAttempts,
                            'exception' => $throwable::class,
                            'message' => $throwable->getMessage(),
                            'context' => $context,
                        ], 'warning');

                        return ($this->recover)($throwable, $attempt, $context);
                    }

                    throw $throwable;
                }

                $intervalMs = $this->intervalMs($attempt, $throwable, $context);
                $intervalSeconds = (int) ceil($intervalMs / 1000);
                $this->emitReliabilityEvent('reliability.retry.retrying', 'Retry guarded call will retry', [
                    'name' => $this->name,
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1,
                    'max_attempts' => $this->maxAttempts,
                    'interval_seconds' => $intervalSeconds,
                    'interval_ms' => $intervalMs,
                    'exception' => $throwable::class,
                    'message' => $throwable->getMessage(),
                    'context' => $context,
                ], 'warning');

                if (is_callable($this->intervalCallback)) {
                    // Legacy callback signature passes seconds; ms-mode callers can read interval_ms from the event payload.
                    ($this->intervalCallback)($attempt, $throwable, $intervalSeconds, $context);
                }

                // Without this, retry attempts fire back-to-back regardless of intervalUsing() — diverges from Spring Retry's BackOffPolicy semantics.
                // Outbox/queue dispatchers opt out via builder->withSleep(false) since their own scheduler enforces backoff externally.
                if ($this->sleepBetweenAttempts && $intervalMs > 0) {
                    usleep($intervalMs * 1000);
                }
            }
        }
    }

    /**
     * Exception-routing precedence in shouldRetry:
     *   1. abortOn — if the throwable is in the abortOn list, return false (never retry).
     *   2. exceptionHandlers — first matching class's handler decides (true=retry, false=abort).
     *   3. retryOn — if non-empty, retry only on listed classes; empty falls back to ReliabilityPolicy::TRANSIENT_EXCEPTIONS.
     * Explicit abort wins over handler; handler wins over default list. This lets callers express
     * "always abort on X, retry on Y unless its message matches Z" cleanly.
     *
     * @param array<string, mixed> $context
     */
    private function shouldRetry(Throwable $throwable, int $attempt, array $context): bool
    {
        foreach ($this->abortOn as $class) {
            if ($throwable instanceof $class) {
                return false;
            }
        }

        foreach ($this->exceptionHandlers as $class => $handler) {
            if ($throwable instanceof $class) {
                return (bool) $handler($throwable, $attempt, $context);
            }
        }

        // Empty retryOn => use the policy's transient-exception allowlist (DB/HTTP/network).
        // Callers that want to retry on Throwable must opt in explicitly via ->retryOnAny().
        $list = $this->retryOn === [] ? ReliabilityPolicy::TRANSIENT_EXCEPTIONS : $this->retryOn;

        foreach ($list as $class) {
            if ($throwable instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute the wait between attempts as a non-negative ms value.
     * The resolver's return unit is determined by the builder (seconds by default, ms via intervalUsingMillis()).
     *
     * @param array<string, mixed> $context
     */
    private function intervalMs(int $attempt, Throwable $throwable, array $context): int
    {
        if (!is_callable($this->intervalResolver)) {
            return max(0, ReliabilityPolicy::retryDelaySeconds($attempt) * 1000);
        }

        $raw = (int) ($this->intervalResolver)($attempt, $throwable, $context);
        $ms = $this->intervalIsMillis ? $raw : $raw * 1000;

        return max(0, $ms);
    }
}
