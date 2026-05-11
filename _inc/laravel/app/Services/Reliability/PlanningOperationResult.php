<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;

class PlanningOperationResult
{
    public function __construct(
        private mixed $value,
        private ?OperationLedger $ledger,
        private ?OutboxMessage $outboxMessage,
    ) {
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function ledger(): ?OperationLedger
    {
        return $this->ledger;
    }

    public function outboxMessage(): ?OutboxMessage
    {
        return $this->outboxMessage;
    }
}
