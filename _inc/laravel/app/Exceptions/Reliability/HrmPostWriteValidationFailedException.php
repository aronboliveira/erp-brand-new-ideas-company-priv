<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Services\Reliability\{HrmReliabilityAssessment, PostWriteValidationResult};
use RuntimeException;

class HrmPostWriteValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly HrmReliabilityAssessment $assessment,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function assessment(): HrmReliabilityAssessment
    {
        return $this->assessment;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }
}
