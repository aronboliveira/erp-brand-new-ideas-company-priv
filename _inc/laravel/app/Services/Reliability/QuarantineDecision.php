<?php

namespace App\Services\Reliability;

class QuarantineDecision
{
    public function __construct(
        public readonly string $decision,
        public readonly string $status,
        public readonly string $action,
        public readonly string $details,
    ) {
    }

    public static function rollback(string $details): self
    {
        return new self(
            ReliabilityPolicy::QUARANTINE_DECISION_ROLLBACK,
            ReliabilityPolicy::QUARANTINE_ROLLED_BACK,
            ReliabilityPolicy::QUARANTINE_ACTION_ROLLED_BACK,
            $details,
        );
    }

    public static function manualReview(string $details): self
    {
        return new self(
            ReliabilityPolicy::QUARANTINE_DECISION_MANUAL_REVIEW,
            ReliabilityPolicy::QUARANTINE_MANUAL_REVIEW,
            ReliabilityPolicy::QUARANTINE_ACTION_MANUAL_REVIEW_REQUESTED,
            $details,
        );
    }
}
