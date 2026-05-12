<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Reliability\PlanningOperationResult;
use App\Services\Reliability\PlanningOperationService;
use App\Services\Reliability\PlanningOutboxDispatcher;
use App\Services\Reliability\ReliabilityClientPayloadService;

trait HandlesPlanningReliability
{
    /**
     * @param callable(mixed...): mixed $callback
     * @param array<string, mixed> $options
     */
    protected function runPlanningReliabilityOperation(string $operationType, callable $callback, array $options): PlanningOperationResult
    {
        return (new PlanningOperationService())->run($operationType, $callback, $options);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function dispatchPlanningReliabilityOutbox(PlanningOperationResult $operation): ?array
    {
        $message = $operation->outboxMessage();

        return $message ? (new PlanningOutboxDispatcher())->dispatchMessage($message) : null;
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    protected function planningReliabilityClientPayload(PlanningOperationResult $operation, ?array $dispatchReport): array
    {
        return (new ReliabilityClientPayloadService())->fromPlanningResult($operation, $dispatchReport);
    }
}
