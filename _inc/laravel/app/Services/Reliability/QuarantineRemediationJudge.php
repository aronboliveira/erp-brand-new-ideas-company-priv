<?php

namespace App\Services\Reliability;

class QuarantineRemediationJudge
{
    public function decide(PostWriteValidationResult $validation): QuarantineDecision
    {
        if ($validation->domain === 'finance') {
            return QuarantineDecision::rollback(
                'Finance post-write validation failed after persistent instability signals; the domain write was rolled back before outbox dispatch.'
            );
        }

        if ($validation->domain === 'hrm') {
            return QuarantineDecision::manualReview(
                'HRM post-write validation failed after persistent retry/circuit instability; keep the source signal in manual review before further employee-impacting actions.'
            );
        }

        if ($validation->domain === 'warehouse') {
            return QuarantineDecision::manualReview(
                'Warehouse post-write validation failed after persistent retry/circuit instability; keep the stock or product signal in manual review before further inventory-impacting actions.'
            );
        }

        return QuarantineDecision::manualReview(
            'The quarantine decision is ambiguous outside the mature domain slices and requires manual review.'
        );
    }
}
