<?php

namespace App\Services\Reliability;

class QuarantineRemediationJudge
{
    public function decide(PostWriteValidationResult $validation): QuarantineDecision
    {
        if ($validation->domain === 'finance') {
            return QuarantineDecision::rollback(
                'Finance post-write validation failed; the domain write was rolled back before outbox dispatch.'
            );
        }

        return QuarantineDecision::manualReview(
            'The quarantine decision is ambiguous outside the finance slice and requires manual review.'
        );
    }
}
