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

        if ($validation->domain === 'crm') {
            return QuarantineDecision::manualReview(
                'CRM post-write validation failed after persistent retry/circuit instability; keep the lead, deal, or access signal in manual review before further customer-impacting actions.'
            );
        }

        if ($validation->domain === 'planning') {
            return QuarantineDecision::manualReview(
                'Planning post-write validation failed after persistent retry/circuit instability; keep the final project, milestone, or task signal in manual review before further project-impacting actions.'
            );
        }

        if ($validation->domain === 'heavy_io') {
            return QuarantineDecision::manualReview(
                'Heavy I/O integration validation failed after persistent retry/circuit instability; keep the import, export, webhook, or callback signal in manual review before further downstream processing.'
            );
        }

        return QuarantineDecision::manualReview(
            'The quarantine decision is ambiguous outside the mature domain slices and requires manual review.'
        );
    }
}
