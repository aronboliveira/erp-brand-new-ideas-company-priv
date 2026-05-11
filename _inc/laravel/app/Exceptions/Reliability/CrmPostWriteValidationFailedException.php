<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Services\Reliability\{CrmReliabilityAssessment, PostWriteValidationResult};
use RuntimeException;

class CrmPostWriteValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly CrmReliabilityAssessment $assessment,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function assessment(): CrmReliabilityAssessment
    {
        return $this->assessment;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }
}
