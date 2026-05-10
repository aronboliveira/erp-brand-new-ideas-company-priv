<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use App\Services\Reliability\Concerns\EmitsReliabilityEvents;

abstract class AbstractReliabilityGuard
{
    use EmitsReliabilityEvents;

    public function __construct(
        string $criticality,
        string $channel,
        ?OperationLedger $operationLedger = null,
        ?OutboxMessage $outboxMessage = null,
        ?OperationalEventService $events = null,
    ) {
        $this->criticality = ReliabilityPolicy::normalizeCriticality($criticality);
        $this->channel = $channel;
        $this->operationLedger = $operationLedger;
        $this->outboxMessage = $outboxMessage;
        $this->events = $events ?? new OperationalEventService();
        $this->source = static::class;
    }
}
