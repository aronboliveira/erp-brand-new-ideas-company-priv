<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Services\Reliability\{PostWriteValidationResult, WarehouseReliabilityAssessment};
use RuntimeException;

class WarehousePostWriteValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly WarehouseReliabilityAssessment $assessment,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function assessment(): WarehouseReliabilityAssessment
    {
        return $this->assessment;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }
}
