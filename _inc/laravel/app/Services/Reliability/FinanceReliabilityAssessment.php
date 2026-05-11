<?php

namespace App\Services\Reliability;

class FinanceReliabilityAssessment
{
    /**
     * @param array<string, mixed> $signals
     * @param array<string, mixed> $thresholds
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $amountTier,
        public readonly string $criticality,
        public readonly int $maxAttempts,
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
            'amount' => $this->amount,
            'amount_tier' => $this->amountTier,
            'criticality' => $this->criticality,
            'retry' => [
                'eligible' => true,
                'max_attempts' => $this->maxAttempts,
            ],
            'post_write_validation_required' => $this->postWriteValidationRequired,
            'persistent_failure_detected' => $this->persistentFailureDetected,
            'quarantine_candidate' => $this->quarantineCandidate,
            'signals' => $this->signals,
            'thresholds' => $this->thresholds,
        ];
    }
}
