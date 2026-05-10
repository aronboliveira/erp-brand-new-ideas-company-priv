<?php

namespace App\Services\Reliability;

use App\Models\OperationLedger;
use App\Models\OperationStep;
use App\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionFunction;
use Throwable;

class CriticalOperationService
{
    private OutboxService $outbox;

    private OperationalEventService $events;

    public function __construct(
        ?OutboxService $outbox = null,
        ?OperationalEventService $events = null,
    ) {
        $this->outbox = $outbox ?? new OutboxService();
        $this->events = $events ?? new OperationalEventService();
    }

    /**
     * @template TResult
     *
     * @param callable(?OperationLedger, self): TResult $callback
     * @return TResult
     *
     * @throws Throwable
     */
    public function run(string $operationType, callable $callback, array $options = []): mixed
    {
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? ReliabilityPolicy::CRITICALITY_MEDIUM);
        $storageMode = ReliabilityPolicy::storageMode($criticality, $options['storage_mode'] ?? null);

        if (!ReliabilityPolicy::ledgerRequired($criticality)) {
            return $this->runVolatile($operationType, $callback, $criticality, $storageMode, $options);
        }

        $ledger = $this->createLedger($operationType, $criticality, $options);
        $this->recordStep($ledger, 'operation.begin', 'Operation accepted', [
            'step_type' => 'validation',
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'sequence' => 1,
            'payload' => $options['context'] ?? null,
            'started_at' => $ledger->started_at,
            'finished_at' => now(),
        ]);

        $this->events->record('operation.started', $options['summary'] ?? $operationType, [
            'operation_type' => $operationType,
            'domain' => $options['domain'] ?? 'system',
        ], $this->eventOptions($ledger, $criticality, $options, 'notice'));

        $transactionStep = $this->recordStep($ledger, 'operation.db_transaction', 'Database transaction', [
            'step_type' => 'db_write',
            'status' => ReliabilityPolicy::STEP_RUNNING,
            'sequence' => 10,
            'started_at' => now(),
        ]);

        try {
            $this->setIsolationLevelIfPossible($options['isolation_level'] ?? ReliabilityPolicy::defaultIsolationLevel($criticality));

            $result = DB::transaction(function () use ($callback, $ledger, $options) {
                $result = $this->invokeCallback($callback, $ledger);

                if (array_key_exists('outbox', $options)) {
                    $outbox = $this->resolveOutboxOptions($options['outbox'], $result, $ledger);
                    if ($outbox) {
                        $this->recordOutbox($ledger, $outbox);
                    }
                }

                return $result;
            });

            $this->succeedStep($transactionStep, ['result_type' => get_debug_type($result)]);

            $finalStatus = $options['final_status'] ?? ReliabilityPolicy::STATUS_COMMITTED;
            $ledger->forceFill([
                'status' => $finalStatus,
                'result' => $this->normalizeResult($result),
                'committed_at' => now(),
                'posted_at' => $finalStatus === ReliabilityPolicy::STATUS_POSTED_TO_LEDGER ? now() : null,
            ])->save();

            $this->events->record('operation.committed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'final_status' => $finalStatus,
            ], $this->eventOptions($ledger, $criticality, $options));

            return $result;
        } catch (Throwable $throwable) {
            $this->failStep($transactionStep, $throwable->getMessage());
            $ledger->forceFill([
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'error_message' => $throwable->getMessage(),
                'failed_at' => now(),
            ])->save();

            $this->events->record('operation.failed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ], $this->eventOptions($ledger, $criticality, $options, 'error'));

            throw $throwable;
        }
    }

    public function recordStep(?OperationLedger $ledger, string $stepKey, string $stepName, array $options = []): ?OperationStep
    {
        if (!$ledger) {
            return null;
        }

        return OperationStep::updateOrCreate(
            [
                'operation_ledger_id' => $ledger->id,
                'step_key' => $stepKey,
            ],
            [
                'step_name' => $stepName,
                'step_type' => $options['step_type'] ?? 'other',
                'sequence' => $options['sequence'] ?? 0,
                'status' => $options['status'] ?? ReliabilityPolicy::STEP_PENDING,
                'criticality' => $options['criticality'] ?? $ledger->criticality,
                'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
                'payload' => $options['payload'] ?? null,
                'result' => $options['result'] ?? null,
                'error_message' => $options['error_message'] ?? null,
                'retry_count' => $options['retry_count'] ?? 0,
                'started_at' => $options['started_at'] ?? null,
                'finished_at' => $options['finished_at'] ?? null,
            ]
        );
    }

    public function succeedStep(?OperationStep $step, array $result = []): void
    {
        if (!$step) {
            return;
        }

        $step->forceFill([
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'result' => $result ?: null,
            'error_message' => null,
            'finished_at' => now(),
        ])->save();
    }

    public function failStep(?OperationStep $step, string $error): void
    {
        if (!$step) {
            return;
        }

        $step->forceFill([
            'status' => ReliabilityPolicy::STEP_FAILED,
            'error_message' => $error,
            'finished_at' => now(),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(OperationLedger $ledger, string $criticality, array $options, string $severity = 'info'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => $options['domain'] ?? 'system',
            'source' => static::class,
            'operation_ledger_id' => $ledger->id,
            'subject_type' => $ledger->subject_type,
            'subject_id' => $ledger->subject_id,
            'actor_id' => $ledger->actor_id,
        ];
    }

    private function createLedger(string $operationType, string $criticality, array $options): OperationLedger
    {
        return OperationLedger::create([
            'operation_key' => $options['operation_key'] ?? 'op-' . (string) Str::uuid(),
            'operation_type' => $operationType,
            'domain' => $options['domain'] ?? 'system',
            'criticality' => $criticality,
            'isolation_level' => $options['isolation_level'] ?? ReliabilityPolicy::defaultIsolationLevel($criticality),
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'subject_type' => $options['subject_type'] ?? null,
            'subject_id' => isset($options['subject_id']) ? (string) $options['subject_id'] : null,
            'actor_id' => $options['actor_id'] ?? null,
            'correlation_id' => $options['correlation_id'] ?? null,
            'request_id' => $options['request_id'] ?? $this->requestId(),
            'summary' => $options['summary'] ?? null,
            'context' => $options['context'] ?? null,
            'started_at' => now(),
            'expires_at' => $options['expires_at'] ?? now()->addDays(ReliabilityPolicy::retentionDays('operation_ledger', $criticality)),
        ]);
    }

    private function recordOutbox(OperationLedger $ledger, array $options): OutboxMessage|array
    {
        $payload = $options['payload'] ?? [];
        unset($options['payload']);

        return $this->outbox->record($options['event_type'] ?? $ledger->operation_type, $payload, array_merge([
            'criticality' => $ledger->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'operation_ledger_id' => $ledger->id,
            'aggregate_type' => $ledger->subject_type,
            'aggregate_id' => $ledger->subject_id,
            'stream' => $ledger->domain,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
        ], $options));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveOutboxOptions(mixed $outbox, mixed $result, OperationLedger $ledger): ?array
    {
        if (is_callable($outbox)) {
            $callable = \Closure::fromCallable($outbox);
            $parameters = (new ReflectionFunction($callable))->getNumberOfParameters();
            $outbox = match (true) {
                $parameters >= 2 => $callable($result, $ledger),
                $parameters === 1 => $callable($result),
                default => $callable(),
            };
        }

        return is_array($outbox) ? $outbox : null;
    }

    private function runVolatile(string $operationType, callable $callback, string $criticality, string $storageMode, array $options): mixed
    {
        $this->events->record('operation.volatile.started', $options['summary'] ?? $operationType, [
            'operation_type' => $operationType,
        ], [
            'criticality' => $criticality,
            'storage_mode' => $storageMode,
            'channel' => $options['domain'] ?? 'system',
            'source' => static::class,
        ]);

        try {
            return $this->invokeCallback($callback, null);
        } catch (Throwable $throwable) {
            $this->events->record('operation.volatile.failed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ], [
                'severity' => 'error',
                'criticality' => $criticality,
                'storage_mode' => $storageMode,
                'channel' => $options['domain'] ?? 'system',
                'source' => static::class,
            ]);

            throw $throwable;
        }
    }

    private function setIsolationLevelIfPossible(?string $isolationLevel): void
    {
        $isolationLevel = strtoupper(trim((string) $isolationLevel));
        $allowed = ['READ COMMITTED', 'REPEATABLE READ', 'SERIALIZABLE'];

        if (!$isolationLevel || !in_array($isolationLevel, $allowed, true) || DB::transactionLevel() > 0) {
            return;
        }

        DB::statement('SET TRANSACTION ISOLATION LEVEL ' . $isolationLevel);
    }

    private function requestId(): ?string
    {
        if (!function_exists('app') || !app()->bound('request')) {
            return null;
        }

        return request()->headers->get('X-Request-Id');
    }

    private function invokeCallback(callable $callback, ?OperationLedger $ledger): mixed
    {
        $reflection = new ReflectionFunction(\Closure::fromCallable($callback));
        $parameterCount = $reflection->getNumberOfParameters();

        if ($parameterCount >= 2) {
            return $callback($ledger, $this);
        }

        if ($parameterCount === 1) {
            return $callback($ledger);
        }

        return $callback();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeResult(mixed $result): ?array
    {
        if ($result === null) {
            return null;
        }

        if (is_scalar($result)) {
            return ['value' => $result];
        }

        if (is_object($result) && method_exists($result, 'getKey')) {
            return [
                'class' => $result::class,
                'id' => (string) $result->getKey(),
            ];
        }

        return ['type' => get_debug_type($result)];
    }
}
