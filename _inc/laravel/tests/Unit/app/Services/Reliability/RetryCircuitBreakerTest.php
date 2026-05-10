<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Reliability;

use App\Config\Constants\DatabaseConstants as DC;
use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\CircuitBreakerCall;
use App\Models\CircuitBreakerState;
use App\Models\OperationalEvent;
use App\Services\Reliability\CircuitBreaker;
use App\Services\Reliability\CircuitBreakerBuilder;
use App\Services\Reliability\Retry;
use App\Services\Reliability\RetryBuilder;
use App\Services\Reliability\ReliabilityPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use RuntimeException;
use Tests\TestCase;
use Throwable;

#[CoversClass(CircuitBreaker::class)]
#[CoversClass(CircuitBreakerBuilder::class)]
#[CoversClass(Retry::class)]
#[CoversClass(RetryBuilder::class)]
#[CoversClass(ReliabilityPolicy::class)]
#[Group('services')]
#[Group('reliability')]
class RetryCircuitBreakerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function retry_policy_uses_capped_exponential_backoff(): void
    {
        $this->assertSame(30, ReliabilityPolicy::retryDelaySeconds(0));
        $this->assertSame(30, ReliabilityPolicy::retryDelaySeconds(1));
        $this->assertSame(60, ReliabilityPolicy::retryDelaySeconds(2));
        $this->assertSame(120, ReliabilityPolicy::retryDelaySeconds(3));
        $this->assertSame(240, ReliabilityPolicy::retryDelaySeconds(4));
        $this->assertSame(300, ReliabilityPolicy::retryDelaySeconds(5));
        $this->assertSame(300, ReliabilityPolicy::retryDelaySeconds(99));
    }

    #[Test]
    public function retry_builder_retries_configured_exception_and_emits_events(): void
    {
        $attempts = 0;
        $intervals = [];

        $result = Retry::builder('test.retry.success.' . Str::uuid())
            ->maxAttempts(3)
            ->retryOn(RuntimeException::class)
            ->intervalUsing(fn(int $attempt): int => $attempt * 5)
            ->onInterval(function (int $attempt, Throwable $throwable, int $seconds) use (&$intervals): void {
                $intervals[] = [$attempt, $throwable->getMessage(), $seconds];
            })
            ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
            ->channel('finance.retry')
            ->build()
            ->run(function () use (&$attempts): string {
                $attempts++;
                if ($attempts < 3) {
                    throw new RuntimeException('temporary-' . $attempts);
                }

                return 'ok';
            }, ['case' => 'retry-success']);

        $this->assertSame('ok', $result);
        $this->assertSame(3, $attempts);
        $this->assertSame([[1, 'temporary-1', 5], [2, 'temporary-2', 10]], $intervals);
        $this->assertSame(2, OperationalEvent::where('event_type', 'reliability.retry.retrying')->where('channel', 'finance.retry')->count());
        $this->assertSame(1, OperationalEvent::where('event_type', 'reliability.retry.success')->where('channel', 'finance.retry')->count());
    }

    #[Test]
    public function retry_builder_uses_default_exponential_backoff_when_no_interval_resolver_is_configured(): void
    {
        $attempts = 0;
        $intervals = [];

        $result = Retry::builder('test.retry.default-backoff.' . Str::uuid())
            ->maxAttempts(3)
            ->retryOn(RuntimeException::class)
            ->onInterval(function (int $attempt, Throwable $throwable, int $seconds) use (&$intervals): void {
                $intervals[] = [$attempt, $throwable->getMessage(), $seconds];
            })
            ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
            ->channel('finance.retry.default-backoff')
            ->build()
            ->run(function () use (&$attempts): string {
                $attempts++;
                if ($attempts < 3) {
                    throw new RuntimeException('temporary-' . $attempts);
                }

                return 'ok';
            });

        $this->assertSame('ok', $result);
        $this->assertSame([[1, 'temporary-1', 30], [2, 'temporary-2', 60]], $intervals);
    }

    #[Test]
    public function retry_abort_exception_stops_without_extra_attempts(): void
    {
        $attempts = 0;

        try {
            Retry::builder('test.retry.abort.' . Str::uuid())
                ->maxAttempts(3)
                ->retryOn(Throwable::class)
                ->abortOn(InvalidArgumentException::class)
                ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
                ->channel('finance.retry')
                ->build()
                ->run(function () use (&$attempts): never {
                    $attempts++;
                    throw new InvalidArgumentException('invalid request');
                });

            $this->fail('Abort exception should have been rethrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('invalid request', $exception->getMessage());
        }

        $this->assertSame(1, $attempts);
        $this->assertSame(1, OperationalEvent::where('event_type', 'reliability.retry.failed')->where('channel', 'finance.retry')->count());
    }

    #[Test]
    public function circuit_breaker_opens_when_sliding_window_failure_rate_reaches_threshold(): void
    {
        $key = 'test.cb.open.' . Str::uuid();
        $breaker = $this->breaker($key);

        $breaker->call(fn(): string => 'ok');
        $this->ignoreFailure(fn() => $breaker->call(fn(): never => throw new RuntimeException('failure-1')));
        $this->ignoreFailure(fn() => $breaker->call(fn(): never => throw new RuntimeException('failure-2')));
        $breaker->call(fn(): string => 'ok');

        $state = CircuitBreakerState::where('breaker_key', $key)->firstOrFail();
        $this->assertSame(ReliabilityPolicy::CIRCUIT_OPEN, $state->state);
        $this->assertNotNull($state->next_attempt_at);

        try {
            $breaker->call(fn(): string => 'blocked');
            $this->fail('Open circuit should reject the guarded call.');
        } catch (CircuitBreakerOpenException) {
        }

        $this->assertDatabaseHas(DC::TABLE_CIRCUIT_BREAKER_CALLS, [
            'breaker_key' => $key,
            'status' => ReliabilityPolicy::CIRCUIT_CALL_REJECTED,
        ]);
        $this->assertDatabaseHas(DC::TABLE_OPERATIONAL_EVENTS, [
            'event_type' => 'reliability.circuit.rejected',
            'channel' => 'finance.circuit',
        ]);
    }

    #[Test]
    public function conservative_half_open_closes_only_after_all_allowed_probes_succeed(): void
    {
        $key = 'test.cb.half-open.all.' . Str::uuid();
        $breaker = $this->openedBreaker($key, true, 100.0, 2);

        $this->assertSame('probe-1', $breaker->call(fn(): string => 'probe-1'));
        $this->assertSame(ReliabilityPolicy::CIRCUIT_HALF_OPEN, CircuitBreakerState::where('breaker_key', $key)->value('state'));

        $this->assertSame('probe-2', $breaker->call(fn(): string => 'probe-2'));

        $this->assertSame(ReliabilityPolicy::CIRCUIT_CLOSED, CircuitBreakerState::where('breaker_key', $key)->value('state'));
    }

    #[Test]
    public function percentage_half_open_can_close_after_threshold_is_met(): void
    {
        $key = 'test.cb.half-open.percent.' . Str::uuid();
        $breaker = $this->openedBreaker($key, false, 50.0, 4);

        $this->assertSame('ok-1', $breaker->call(fn(): string => 'ok-1'));
        $this->ignoreFailure(fn() => $breaker->call(fn(): never => throw new RuntimeException('probe-failed-1')));
        $this->assertSame('ok-2', $breaker->call(fn(): string => 'ok-2'));
        $this->ignoreFailure(fn() => $breaker->call(fn(): never => throw new RuntimeException('probe-failed-2')));

        $this->assertSame(ReliabilityPolicy::CIRCUIT_CLOSED, CircuitBreakerState::where('breaker_key', $key)->value('state'));
    }

    #[Test]
    public function low_criticality_circuit_breaker_is_disabled_by_default(): void
    {
        $key = 'test.cb.low.' . Str::uuid();

        $result = CircuitBreaker::builder($key)
            ->criticality(ReliabilityPolicy::CRITICALITY_LOW)
            ->channel('ui.low')
            ->build()
            ->call(fn(): string => 'ok');

        $this->assertSame('ok', $result);
        $this->assertFalse(CircuitBreakerState::where('breaker_key', $key)->exists());
        $this->assertFalse(CircuitBreakerCall::where('breaker_key', $key)->exists());
    }

    private function breaker(string $key): CircuitBreaker
    {
        return CircuitBreaker::builder($key)
            ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
            ->channel('finance.circuit')
            ->slidingWindowSize(4)
            ->slidingWindowSeconds(300)
            ->failureRateThreshold(50.0)
            ->minimumCalls(4)
            ->openStateDurationSeconds(60)
            ->halfOpenAllowedCalls(2)
            ->halfOpenConservative(true)
            ->build();
    }

    private function openedBreaker(string $key, bool $conservative, float $successThreshold, int $allowedCalls): CircuitBreaker
    {
        $breaker = CircuitBreaker::builder($key)
            ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
            ->channel('finance.circuit')
            ->slidingWindowSize(4)
            ->slidingWindowSeconds(300)
            ->failureRateThreshold(50.0)
            ->minimumCalls(4)
            ->openStateDurationSeconds(60)
            ->halfOpenAllowedCalls($allowedCalls)
            ->halfOpenSuccessThreshold($successThreshold)
            ->halfOpenConservative($conservative)
            ->build();

        CircuitBreakerState::create([
            'breaker_key' => $key,
            'name' => $key,
            'domain' => 'finance.circuit',
            'criticality' => ReliabilityPolicy::CRITICALITY_HIGH,
            'state' => ReliabilityPolicy::CIRCUIT_OPEN,
            'sliding_window_size' => 4,
            'sliding_window_seconds' => 300,
            'failure_rate_threshold' => 50.0,
            'minimum_calls' => 4,
            'open_state_seconds' => 60,
            'half_open_allowed_calls' => $allowedCalls,
            'half_open_success_threshold' => $successThreshold,
            'half_open_conservative' => $conservative,
            'opened_at' => now()->subMinutes(2),
            'next_attempt_at' => now()->subSecond(),
            'expires_at' => now()->addDay(),
        ]);

        return $breaker;
    }

    private function ignoreFailure(callable $callback): void
    {
        try {
            $callback();
        } catch (RuntimeException) {
        }
    }
}
