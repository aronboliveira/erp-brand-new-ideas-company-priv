<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Reliability\FinanceOperationResult;
use App\Services\Reliability\FinanceOperationService;
use App\Services\Reliability\FinanceOutboxDispatcher;
use App\Services\Reliability\ReliabilityClientPayloadService;

trait HandlesFinanceReliability
{
    /**
     * @param callable(mixed...): mixed $callback
     * @param array<string, mixed> $options
     */
    protected function runFinanceReliabilityOperation(string $operationType, callable $callback, array $options): FinanceOperationResult
    {
        return (new FinanceOperationService())->run($operationType, $callback, $options);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function dispatchFinanceReliabilityOutbox(FinanceOperationResult $operation): ?array
    {
        $message = $operation->outboxMessage();

        return $message ? (new FinanceOutboxDispatcher())->dispatchMessage($message) : null;
    }

    /**
     * @param array<string, mixed>|null $dispatchReport
     * @return array<string, mixed>
     */
    protected function financeReliabilityClientPayload(FinanceOperationResult $operation, ?array $dispatchReport): array
    {
        return (new ReliabilityClientPayloadService())->fromFinanceResult($operation, $dispatchReport);
    }
}
