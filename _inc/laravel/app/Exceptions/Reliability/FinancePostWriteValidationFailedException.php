<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Services\Reliability\{FinanceReliabilityAssessment, PostWriteValidationResult};
use RuntimeException;

class FinancePostWriteValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly FinanceReliabilityAssessment $assessment,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function assessment(): FinanceReliabilityAssessment
    {
        return $this->assessment;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }
}
