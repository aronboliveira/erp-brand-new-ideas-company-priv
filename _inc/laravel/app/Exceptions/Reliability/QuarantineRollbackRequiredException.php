<?php

namespace App\Exceptions\Reliability;

use App\Models\OperationLedger;
use App\Models\OperationQuarantine;
use App\Services\Reliability\PostWriteValidationResult;
use RuntimeException;

class QuarantineRollbackRequiredException extends RuntimeException
{
    private ?OperationQuarantine $quarantine = null;

    public function __construct(
        private readonly PostWriteValidationResult $validation,
        private readonly ?OperationLedger $ledger = null,
    ) {
        parent::__construct($validation->firstErrorMessage());
    }

    public function validation(): PostWriteValidationResult
    {
        return $this->validation;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }

    public function quarantine(): ?OperationQuarantine
    {
        return $this->quarantine;
    }

    public function setQuarantine(OperationQuarantine $quarantine): void
    {
        $this->quarantine = $quarantine;
    }
}
