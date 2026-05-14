<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;

class CircuitBreakerBuilder
{
    private string $name;

    private ?bool $enabled = null;

    private int $slidingWindowSize = 20;

    private int $slidingWindowSeconds = 300;

    private float $failureRateThreshold = 50.0;

    private int $minimumCalls = 10;

    private int $openStateSeconds = 60;

    private int $halfOpenAllowedCalls = 3;

    private ?float $halfOpenSuccessThreshold = null;

    private ?bool $halfOpenConservative = null;

    private ?int $slowCallDurationMs = null;

    private ?float $slowCallRateThreshold = null;

    private string $criticality = ReliabilityPolicy::CRITICALITY_MEDIUM;

    private string $channel = 'system';

    private ?OperationLedger $operationLedger = null;

    private ?OutboxMessage $outboxMessage = null;

    private ?OperationalEventService $events = null;

    public function __construct(private string $breakerKey)
    {
        $this->name = $breakerKey;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function enabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function slidingWindowSize(int $size): self
    {
        $this->slidingWindowSize = max(1, $size);

        return $this;
    }

    public function slidingWindowSeconds(int $seconds): self
    {
        $this->slidingWindowSeconds = max(1, $seconds);

        return $this;
    }

    public function failureRateThreshold(float $threshold): self
    {
        $this->failureRateThreshold = max(1.0, min(100.0, $threshold));

        return $this;
    }

    public function minimumCalls(int $minimumCalls): self
    {
        $this->minimumCalls = max(1, $minimumCalls);

        return $this;
    }

    public function openStateDurationSeconds(int $seconds): self
    {
        $this->openStateSeconds = max(1, $seconds);

        return $this;
    }

    public function halfOpenAllowedCalls(int $allowedCalls): self
    {
        $this->halfOpenAllowedCalls = max(1, $allowedCalls);

        return $this;
    }

    public function halfOpenSuccessThreshold(float $threshold): self
    {
        $this->halfOpenSuccessThreshold = max(1.0, min(100.0, $threshold));

        return $this;
    }

    public function halfOpenConservative(bool $conservative): self
    {
        $this->halfOpenConservative = $conservative;

        return $this;
    }

    /**
     * Calls that take longer than $durationMs are flagged as "slow"; if their rate
     * in the closed window meets $rateThreshold (0–100), the breaker trips even when
     * the failure-rate threshold is not yet met. Pass nulls to disable (default).
     */
    public function slowCallThreshold(?int $durationMs, ?float $rateThreshold): self
    {
        $this->slowCallDurationMs = $durationMs !== null ? max(1, $durationMs) : null;
        $this->slowCallRateThreshold = $rateThreshold !== null ? max(1.0, min(100.0, $rateThreshold)) : null;

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

    public function build(): CircuitBreaker
    {
        $enabled = $this->enabled ?? ReliabilityPolicy::circuitBreakerRequired($this->criticality);
        $conservative = $this->halfOpenConservative ?? in_array($this->criticality, [
            ReliabilityPolicy::CRITICALITY_HIGH,
            ReliabilityPolicy::CRITICALITY_CRITICAL,
        ], true);

        return new CircuitBreaker(
            $this->breakerKey,
            $this->name,
            $enabled,
            $this->slidingWindowSize,
            $this->slidingWindowSeconds,
            $this->failureRateThreshold,
            $this->minimumCalls,
            $this->openStateSeconds,
            $this->halfOpenAllowedCalls,
            $this->halfOpenSuccessThreshold ?? ReliabilityPolicy::defaultHalfOpenSuccessThreshold($this->criticality),
            $conservative,
            $this->criticality,
            $this->channel,
            $this->operationLedger,
            $this->outboxMessage,
            $this->events,
            $this->slowCallDurationMs,
            $this->slowCallRateThreshold,
        );
    }
}
