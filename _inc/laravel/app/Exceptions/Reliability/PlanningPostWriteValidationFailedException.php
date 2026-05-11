<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Services\Reliability\{PlanningReliabilityAssessment, PostWriteValidationResult};
use RuntimeException;

class PlanningPostWriteValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly PlanningReliabilityAssessment $assessment,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function assessment(): PlanningReliabilityAssessment
    {
        return $this->assessment;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }
}
