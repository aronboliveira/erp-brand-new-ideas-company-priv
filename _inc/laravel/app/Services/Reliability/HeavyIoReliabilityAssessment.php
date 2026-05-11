<?php

namespace App\Services\Reliability;

class HeavyIoReliabilityAssessment
{
    /**
     * @param array<string, mixed> $signals
     * @param array<string, mixed> $thresholds
     */
    public function __construct(
        public readonly string $cluster,
        public readonly string $criticality,
        public readonly int $maxAttempts,
        public readonly bool $retryEligible,
        public readonly bool $circuitBreakerEligible,
        public readonly bool $postWriteValidationRequired,
        public readonly bool $persistentFailureDetected,
        public readonly bool $quarantineCandidate,
        public readonly array $signals = [],
        public readonly array $thresholds = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cluster' => $this->cluster,
            'criticality' => $this->criticality,
            'retry' => [
                'eligible' => $this->retryEligible,
                'max_attempts' => $this->maxAttempts,
            ],
            'circuit_breaker' => [
                'eligible' => $this->circuitBreakerEligible,
            ],
            'post_write_validation_required' => $this->postWriteValidationRequired,
            'persistent_failure_detected' => $this->persistentFailureDetected,
            'quarantine_candidate' => $this->quarantineCandidate,
            'signals' => $this->signals,
            'thresholds' => $this->thresholds,
        ];
    }
}

