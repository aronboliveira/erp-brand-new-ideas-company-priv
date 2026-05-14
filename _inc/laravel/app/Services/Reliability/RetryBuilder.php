<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Throwable;

class RetryBuilder
{
    private int $maxAttempts = 3;

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $retryOn = [];

    /**
     * @var array<int, class-string<Throwable>>
     */
    private array $abortOn = [];

    /**
     * @var array<class-string<Throwable>, callable(Throwable, int, array<string, mixed>): bool>
     */
    private array $exceptionHandlers = [];

    private mixed $intervalResolver = null;

    private mixed $intervalCallback = null;

    private bool $intervalIsMillis = false;

    private string $criticality = ReliabilityPolicy::CRITICALITY_MEDIUM;

    private string $channel = 'system';

    private ?OperationLedger $operationLedger = null;

    private ?OutboxMessage $outboxMessage = null;

    private ?OperationalEventService $events = null;

    private bool $sleepBetweenAttempts = true;

    private mixed $recover = null;

    public function __construct(private string $name)
    {
    }

    public function maxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = max(1, $maxAttempts);

        return $this;
    }

    /**
     * @param class-string<Throwable>|array<int, class-string<Throwable>> $classes
     */
    public function retryOn(string|array $classes): self
    {
        $this->retryOn = array_values(array_unique(array_merge($this->retryOn, (array) $classes)));

        return $this;
    }

    /**
     * Retry on any Throwable (subject to abortOn / exceptionHandlers).
     * Equivalent to the pre-policy default — use sparingly. Prefer explicit retryOn() with a curated list
     * or rely on the default ReliabilityPolicy::TRANSIENT_EXCEPTIONS allowlist when no retryOn() is set.
     */
    public function retryOnAny(): self
    {
        return $this->retryOn(Throwable::class);
    }

    /**
     * @param class-string<Throwable>|array<int, class-string<Throwable>> $classes
     */
    public function abortOn(string|array $classes): self
    {
        $this->abortOn = array_values(array_unique(array_merge($this->abortOn, (array) $classes)));

        return $this;
    }

    /**
     * @param class-string<Throwable> $class
     * @param callable(Throwable, int, array<string, mixed>): bool $handler
     */
    public function handleException(string $class, callable $handler): self
    {
        $this->exceptionHandlers[$class] = $handler;

        return $this;
    }

    /**
     * @param callable(int, Throwable, array<string, mixed>): int $resolver  Returns seconds.
     */
    public function intervalUsing(callable $resolver): self
    {
        $this->intervalResolver = $resolver;
        $this->intervalIsMillis = false;

        return $this;
    }

    /**
     * Same as intervalUsing() but the resolver returns milliseconds.
     * Use for sub-second backoffs (DB deadlocks, fast remote APIs) where 1s minimum granularity is too coarse.
     *
     * @param callable(int, Throwable, array<string, mixed>): int $resolver  Returns ms.
     */
    public function intervalUsingMillis(callable $resolver): self
    {
        $this->intervalResolver = $resolver;
        $this->intervalIsMillis = true;

        return $this;
    }

    /**
     * @param callable(int, Throwable, int, array<string, mixed>): void $callback
     */
    public function onInterval(callable $callback): self
    {
        $this->intervalCallback = $callback;

        return $this;
    }

    public function criticality(string $criticality): self
    {
        $this->criticality = ReliabilityPolicy::normalizeCriticality($criticality);

        return $this;
    }

    public function channel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function operationLedger(?OperationLedger $ledger): self
    {
        $this->operationLedger = $ledger;

        return $this;
    }

    public function outboxMessage(?OutboxMessage $message): self
    {
        $this->outboxMessage = $message;

        return $this;
    }

    public function events(?OperationalEventService $events): self
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Whether Retry::run() should sleep(intervalSeconds) between attempts.
     * Default true (Spring Retry semantics). Outbox/queue dispatchers should pass false —
     * their scheduler enforces backoff externally and in-process sleep would block the worker.
     */
    public function withSleep(bool $sleep): self
    {
        $this->sleepBetweenAttempts = $sleep;

        return $this;
    }

    /**
     * Register a fallback to invoke when all attempts are exhausted (Spring's @Recover analogue).
     * The recover callback receives ($throwable, $finalAttempt, $context) and its return value is
     * returned from Retry::run() instead of rethrowing. Use for synchronous paths that should
     * degrade gracefully (cached/stale response, dead-letter, manual-review queue) rather than fail.
     *
     * @param callable(Throwable, int, array<string, mixed>): mixed $recover
     */
    public function recoverWith(callable $recover): self
    {
        $this->recover = $recover;

        return $this;
    }

    public function build(): Retry
    {
        return new Retry(
            $this->name,
            $this->maxAttempts,
            $this->retryOn,
            $this->abortOn,
            $this->exceptionHandlers,
            $this->intervalResolver,
            $this->intervalCallback,
            $this->criticality,
            $this->channel,
            $this->operationLedger,
            $this->outboxMessage,
            $this->events,
            $this->sleepBetweenAttempts,
            $this->recover,
            $this->intervalIsMillis,
        );
    }
}
