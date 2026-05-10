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

    private string $criticality = ReliabilityPolicy::CRITICALITY_MEDIUM;

    private string $channel = 'system';

    private ?OperationLedger $operationLedger = null;

    private ?OutboxMessage $outboxMessage = null;

    private ?OperationalEventService $events = null;

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
     * @param callable(int, Throwable, array<string, mixed>): int $resolver
     */
    public function intervalUsing(callable $resolver): self
    {
        $this->intervalResolver = $resolver;

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
        );
    }
}
