<?php

namespace App\Services\Reliability;

/**
 * Thin wrapper around ReliabilityPolicy::quarantineDecisionFor — kept as a class so callers can
 * inject a custom judge for tests or future per-quarantine logic (e.g. auto_recover when source
 * state has self-corrected by the time the judge runs). Today it's pure data-driven dispatch.
 */
class QuarantineRemediationJudge
{
    public function decide(PostWriteValidationResult $validation): QuarantineDecision
    {
        $mapped = ReliabilityPolicy::quarantineDecisionFor($validation->domain);

        return match ($mapped['decision']) {
            ReliabilityPolicy::QUARANTINE_DECISION_ROLLBACK => QuarantineDecision::rollback($mapped['details']),
            default => QuarantineDecision::manualReview($mapped['details']),
        };
    }
}
