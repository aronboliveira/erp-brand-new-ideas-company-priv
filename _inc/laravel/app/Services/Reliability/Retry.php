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
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $result = $this->invokeCallback($callback, $attempt);
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

                    throw $throwable;
                }

                $intervalSeconds = $this->intervalSeconds($attempt, $throwable, $context);
                $this->emitReliabilityEvent('reliability.retry.retrying', 'Retry guarded call will retry', [
                    'name' => $this->name,
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1,
                    'max_attempts' => $this->maxAttempts,
                    'interval_seconds' => $intervalSeconds,
                    'exception' => $throwable::class,
                    'message' => $throwable->getMessage(),
                    'context' => $context,
                ], 'warning');

                if (is_callable($this->intervalCallback)) {
                    ($this->intervalCallback)($attempt, $throwable, $intervalSeconds, $context);
                }
            }
        }
    }

    private function invokeCallback(callable $callback, int $attempt): mixed
    {
        $callable = \Closure::fromCallable($callback);
        $parameterCount = (new ReflectionFunction($callable))->getNumberOfParameters();

        return $parameterCount >= 1 ? $callable($attempt) : $callable();
    }

    /**
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

        if ($this->retryOn === []) {
            return true;
        }

        foreach ($this->retryOn as $class) {
            if ($throwable instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function intervalSeconds(int $attempt, Throwable $throwable, array $context): int
    {
        if (!is_callable($this->intervalResolver)) {
            return 0;
        }

        $seconds = ($this->intervalResolver)($attempt, $throwable, $context);

        return max(0, (int) $seconds);
    }
}
