<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinanceOperationService
{
    private CriticalOperationService $operations;

    public function __construct(?CriticalOperationService $operations = null)
    {
        $this->operations = $operations ?? new CriticalOperationService();
    }

    /**
     * @template TResult
     *
     * @param callable(?OperationLedger, CriticalOperationService): TResult $callback
     * @return FinanceOperationResult
     */
    public function run(string $operationType, callable $callback, array $options = []): FinanceOperationResult
    {
        $operationKey = (string) ($options['operation_key'] ?? 'finance-op-' . (string) Str::uuid());
        $eventType = (string) ($options['event_type'] ?? $operationType . '.completed');
        $stream = (string) ($options['stream'] ?? 'finance.ledger');

        $value = $this->operations->run($operationType, $callback, array_merge($options, [
            'operation_key' => $operationKey,
            'domain' => $options['domain'] ?? 'finance',
            'criticality' => $options['criticality'] ?? ReliabilityPolicy::CRITICALITY_CRITICAL,
            'final_status' => $options['final_status'] ?? ReliabilityPolicy::STATUS_COMMITTED,
            'outbox' => function (mixed $result, OperationLedger $ledger) use ($options, $eventType, $stream): array {
                $payload = $this->resolveArray($options['payload'] ?? null, $result, $ledger, true);
                $messageKey = $this->resolveValue($options['message_key'] ?? null, $result, $ledger);

                return [
                    'message_key' => $this->normalizeMessageKey($messageKey ?: $this->defaultMessageKey($eventType, $payload, $ledger)),
                    'stream' => $stream,
                    'event_type' => $eventType,
                    'aggregate_type' => $options['aggregate_type'] ?? $options['subject_type'] ?? $ledger->subject_type,
                    'aggregate_id' => $options['aggregate_id'] ?? $options['subject_id'] ?? $ledger->subject_id,
                    'payload' => $payload,
                    'headers' => $this->resolveArray($options['headers'] ?? null, $result, $ledger),
                    'metadata' => array_merge(
                        ['operation_key' => $ledger->operation_key],
                        $this->resolveArray($options['metadata'] ?? null, $result, $ledger),
                    ),
                    'max_attempts' => $options['max_attempts'] ?? 3,
                ];
            },
        ]));

        $ledger = OperationLedger::where('operation_key', $operationKey)->first();
        $outboxMessage = $ledger
            ? $ledger->outboxMessages()->where('event_type', $eventType)->latest('created_at')->first()
            : null;

        return new FinanceOperationResult(
            $value,
            $ledger,
            $outboxMessage instanceof OutboxMessage ? $outboxMessage : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveArray(mixed $resolver, mixed $result, OperationLedger $ledger, bool $defaultToResult = false): array
    {
        $value = $this->resolveValue($resolver, $result, $ledger);

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Model) {
            return [
                'model' => $value::class,
                'id' => (string) $value->getKey(),
            ];
        }

        if ($value === null) {
            return $defaultToResult ? $this->defaultPayload($result) : [];
        }

        return ['value' => $value];
    }

    private function resolveValue(mixed $resolver, mixed $result, OperationLedger $ledger): mixed
    {
        if ($resolver instanceof \Closure || (is_array($resolver) && is_callable($resolver))) {
            $callable = \Closure::fromCallable($resolver);
            $parameters = (new \ReflectionFunction($callable))->getNumberOfParameters();

            return match (true) {
                $parameters >= 2 => $callable($result, $ledger),
                $parameters === 1 => $callable($result),
                default => $callable(),
            };
        }

        return $resolver;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPayload(mixed $result): array
    {
        if (is_array($result)) {
            return $result;
        }

        if ($result instanceof Model) {
            return [
                'model' => $result::class,
                'id' => (string) $result->getKey(),
            ];
        }

        return ['result_type' => get_debug_type($result)];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function defaultMessageKey(string $eventType, array $payload, OperationLedger $ledger): string
    {
        $reference = $payload['payment_id']
            ?? $payload['invoice_id']
            ?? $payload['bill_id']
            ?? $payload['reference']
            ?? $ledger->operation_key;

        return $eventType . ':' . (string) $reference;
    }

    private function normalizeMessageKey(string $messageKey): string
    {
        if (strlen($messageKey) <= 191) {
            return $messageKey;
        }

        return substr($messageKey, 0, 145) . ':' . sha1($messageKey);
    }
}
