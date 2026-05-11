<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\{PlanningPostWriteValidationFailedException, QuarantineRollbackRequiredException};
use App\Models\OperationLedger;
use App\Models\OutboxMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlanningOperationService
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
     * @return PlanningOperationResult
     */
    public function run(string $operationType, callable $callback, array $options = []): PlanningOperationResult
    {
        $operationKey = (string) ($options['operation_key'] ?? 'planning-op-' . (string) Str::uuid());
        $eventType = (string) ($options['event_type'] ?? $operationType . '.completed');
        $stream = (string) ($options['stream'] ?? 'planning.operations');
        $policy = $this->planningReliabilityPolicy($options);
        $preAssessment = $policy->assess(
            $eventType,
            is_array($options['context'] ?? null) ? $options['context'] : [],
            null,
            $options,
        );

        $operationCallback = function (?OperationLedger $ledger, CriticalOperationService $operations) use ($callback, $options, $eventType): mixed {
            $result = $this->invokeOperationCallback($callback, $ledger, $operations);

            if ($this->postWriteValidationRequested($options)) {
                $payload = $this->resolveArray($options['payload'] ?? null, $result, $ledger, true);
                $policy = $this->planningReliabilityPolicy($options);
                $assessment = $policy->assess($eventType, $payload, $ledger, $options);

                if (!$assessment->postWriteValidationRequired) {
                    $operations->recordStep($ledger, 'planning.post_write_validation', 'Assess planning post-write validation threshold', [
                        'step_type' => 'validation',
                        'sequence' => 90,
                        'status' => ReliabilityPolicy::STEP_SKIPPED,
                        'payload' => [
                            'event_type' => $eventType,
                            'cluster' => $assessment->cluster,
                        ],
                        'result' => [
                            'validated' => false,
                            'reason' => 'below_planning_post_write_threshold',
                            'assessment' => $assessment->toArray(),
                        ],
                        'started_at' => now(),
                        'finished_at' => now(),
                    ]);

                    return $result;
                }

                $validator = $options['post_write_validator'] ?? new PlanningPostWriteValidator();
                $validation = $validator instanceof PlanningPostWriteValidator
                    ? $validator->validate($eventType, $payload, $ledger)
                    : (new PlanningPostWriteValidator())->validate($eventType, $payload, $ledger);

                $operations->recordStep($ledger, 'planning.post_write_validation', 'Validate planning write after persistence', [
                    'step_type' => 'validation',
                    'sequence' => 90,
                    'status' => $validation->passed ? ReliabilityPolicy::STEP_SUCCEEDED : ReliabilityPolicy::STEP_FAILED,
                    'payload' => [
                        'event_type' => $eventType,
                        'source_table' => $validation->sourceTable,
                        'source_record_id' => $validation->sourceRecordId,
                        'assessment' => $assessment->toArray(),
                    ],
                    'result' => $validation->passed ? ['validated' => true] : [
                        'failed_criteria' => $validation->failedCriteria,
                        'validation_errors' => $validation->validationErrors,
                        'quarantine_candidate' => $assessment->quarantineCandidate,
                    ],
                    'started_at' => now(),
                    'finished_at' => now(),
                ]);

                if (!$validation->passed) {
                    if ($policy->shouldQuarantine($validation, $assessment, $ledger)) {
                        throw new QuarantineRollbackRequiredException($validation, $ledger);
                    }

                    throw new PlanningPostWriteValidationFailedException($validation, $assessment, $ledger);
                }
            }

            return $result;
        };

        try {
            $value = $this->operations->run($operationType, $operationCallback, array_merge($options, [
                'operation_key' => $operationKey,
                'domain' => $options['domain'] ?? 'planning',
                'criticality' => $options['criticality'] ?? $preAssessment->criticality,
                'final_status' => $options['final_status'] ?? ReliabilityPolicy::STATUS_COMMITTED,
                'outbox' => function (mixed $result, OperationLedger $ledger) use ($options, $eventType, $stream): array {
                    $payload = $this->resolveArray($options['payload'] ?? null, $result, $ledger, true);
                    $assessment = $this->planningReliabilityPolicy($options)->assess($eventType, $payload, $ledger, $options);
                    $messageKey = $this->resolveValue($options['message_key'] ?? null, $result, $ledger);
                    $metadata = array_merge(
                        [
                            'operation_key' => $ledger->operation_key,
                            'planning_reliability' => $assessment->toArray(),
                            'retry' => [
                                'eligible' => $assessment->retryEligible,
                                'max_attempts' => $options['max_attempts'] ?? $assessment->maxAttempts,
                            ],
                            'circuit_breaker' => [
                                'eligible' => $assessment->circuitBreakerEligible,
                            ],
                        ],
                        $this->resolveArray($options['metadata'] ?? null, $result, $ledger),
                    );

                    return [
                        'message_key' => $this->normalizeMessageKey($messageKey ?: $this->defaultMessageKey($eventType, $payload, $ledger)),
                        'stream' => $stream,
                        'event_type' => $eventType,
                        'aggregate_type' => $options['aggregate_type'] ?? $options['subject_type'] ?? $ledger->subject_type,
                        'aggregate_id' => $options['aggregate_id'] ?? $options['subject_id'] ?? $ledger->subject_id,
                        'payload' => $payload,
                        'headers' => $this->resolveArray($options['headers'] ?? null, $result, $ledger),
                        'metadata' => $metadata,
                        'max_attempts' => $options['max_attempts'] ?? $assessment->maxAttempts,
                    ];
                },
            ]));
        } catch (QuarantineRollbackRequiredException $exception) {
            $quarantineService = $options['quarantine_service'] ?? new QuarantineService();
            $quarantine = $quarantineService instanceof QuarantineService
                ? $quarantineService->route($exception->validation(), $exception->ledger())
                : (new QuarantineService())->route($exception->validation(), $exception->ledger());
            $exception->setQuarantine($quarantine);

            throw $exception;
        }

        $ledger = OperationLedger::where('operation_key', $operationKey)->first();
        $outboxMessage = $ledger
            ? $ledger->outboxMessages()->where('event_type', $eventType)->latest('created_at')->first()
            : null;

        return new PlanningOperationResult(
            $value,
            $ledger,
            $outboxMessage instanceof OutboxMessage ? $outboxMessage : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveArray(mixed $resolver, mixed $result, ?OperationLedger $ledger, bool $defaultToResult = false): array
    {
        if (!$ledger) {
            return is_array($resolver) ? $resolver : [];
        }

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

    private function postWriteValidationRequested(array $options): bool
    {
        return (bool) ($options['post_write_validation'] ?? false);
    }

    private function planningReliabilityPolicy(array $options): PlanningReliabilityPolicy
    {
        $policy = $options['planning_reliability_policy'] ?? null;

        return $policy instanceof PlanningReliabilityPolicy ? $policy : new PlanningReliabilityPolicy();
    }

    private function invokeOperationCallback(callable $callback, ?OperationLedger $ledger, CriticalOperationService $operations): mixed
    {
        $callable = \Closure::fromCallable($callback);
        $parameters = (new \ReflectionFunction($callable))->getNumberOfParameters();

        return match (true) {
            $parameters >= 2 => $callable($ledger, $operations),
            $parameters === 1 => $callable($ledger),
            default => $callable(),
        };
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
        $reference = $payload['project_id']
            ?? $payload['milestone_id']
            ?? $payload['task_id']
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
