<?php

namespace App\Services\Reliability;

use App\Exceptions\Reliability\CircuitBreakerOpenException;
use App\Models\{OperationLedger, OutboxMessage};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HeavyIoOperationService
{
    private CriticalOperationService $operations;

    private OperationalEventService $events;

    private OutboxService $outbox;

    private HeavyIoReliabilityPolicy $policy;

    private HeavyIoPostWriteValidator $validator;

    private QuarantineService $quarantine;

    public function __construct(
        ?CriticalOperationService $operations = null,
        ?OperationalEventService $events = null,
        ?OutboxService $outbox = null,
        ?HeavyIoReliabilityPolicy $policy = null,
        ?HeavyIoPostWriteValidator $validator = null,
        ?QuarantineService $quarantine = null,
    ) {
        $this->operations = $operations ?? new CriticalOperationService();
        $this->events = $events ?? new OperationalEventService();
        $this->outbox = $outbox ?? new OutboxService();
        $this->policy = $policy ?? new HeavyIoReliabilityPolicy();
        $this->validator = $validator ?? new HeavyIoPostWriteValidator();
        $this->quarantine = $quarantine ?? new QuarantineService();
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(): array<string, mixed> $runner
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function runPythonImport(string $importerName, array $data, callable $runner, array $options = []): array
    {
        $context = $this->pythonContext('importer', $importerName, $data, $options);

        $result = $this->runGuarded(
            'heavy_io.python_import',
            'heavy_io.python_import.completed',
            $importerName,
            $context,
            function () use ($runner): array {
                $result = $runner();
                if (!$this->pythonImportSucceeded($result)) {
                    throw new RuntimeException($this->importErrorMessage($result));
                }

                return $result;
            },
            fn(array $value): array => $this->importPayload($context, $value),
            fn(Throwable $throwable): array => [
                'status' => 'error',
                'errors' => [$throwable->getMessage()],
                'rows' => [],
            ],
            $options,
        );

        return is_array($result) ? $result : [
            'status' => 'error',
            'errors' => ['Heavy I/O import returned an invalid result.'],
            'rows' => [],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(): string $runner
     * @param array<string, mixed> $options
     */
    public function runPythonExport(string $exporterName, array $data, callable $runner, array $options = []): string
    {
        $context = $this->pythonContext('exporter', $exporterName, $data, $options);
        $context['output_path'] = $this->stringOrNull($options['output_path'] ?? null);

        $result = $this->runGuarded(
            'heavy_io.python_export',
            'heavy_io.python_export.completed',
            $exporterName,
            $context,
            function () use ($runner): string {
                $result = $runner();
                if ($result === '') {
                    throw new RuntimeException('Python export produced no output.');
                }

                return $result;
            },
            fn(string $value): array => $this->exportPayload($context, $value),
            fn(Throwable $throwable): string => '',
            $options,
        );

        return is_string($result) ? $result : '';
    }

    /**
     * @param mixed $parameter
     * @param callable(): bool $runner
     * @param array<string, mixed> $options
     */
    public function runWebhook(?string $url, mixed $parameter, string $method, callable $runner, array $options = []): bool
    {
        if (empty($url) || empty($parameter)) {
            return false;
        }

        $context = [
            'integration_name' => 'webhook',
            'operation_type' => 'heavy_io.webhook',
            'method' => strtoupper($method),
            'url_fingerprint' => sha1((string) $url),
            'payload_bytes' => $this->payloadBytes($parameter),
            'source_class' => $options['source_class'] ?? null,
        ];

        $result = $this->runGuarded(
            'heavy_io.webhook',
            'heavy_io.webhook.delivered',
            'webhook:' . sha1((string) $url),
            $context,
            function () use ($runner): bool {
                $delivered = (bool) $runner();
                if (!$delivered) {
                    throw new RuntimeException('Webhook delivery failed.');
                }

                return true;
            },
            fn(bool $value): array => array_merge($context, [
                'delivered' => $value,
                'status' => $value ? 'success' : 'error',
            ]),
            fn(Throwable $throwable): bool => false,
            $options,
        );

        return (bool) $result;
    }

    /**
     * @template TResult
     *
     * @param array<string, mixed> $context
     * @param callable(): TResult $runner
     * @param callable(TResult): array<string, mixed> $payloadResolver
     * @param callable(Throwable): TResult $fallback
     * @param array<string, mixed> $options
     * @return TResult
     */
    private function runGuarded(
        string $operationType,
        string $eventType,
        string $integrationName,
        array $context,
        callable $runner,
        callable $payloadResolver,
        callable $fallback,
        array $options,
    ): mixed {
        $context = array_merge($context, [
            'integration_name' => $integrationName,
            'operation_type' => $operationType,
        ]);
        $preAssessment = $this->policy->assess($eventType, $context, null, $options);

        if (!ReliabilityPolicy::ledgerRequired($preAssessment->criticality)) {
            return $this->runVolatile($operationType, $eventType, $context, $runner, $payloadResolver, $fallback, $options, $preAssessment);
        }

        $operationKey = (string) ($options['operation_key'] ?? 'heavy-io-' . (string) Str::uuid());
        $ledger = $this->createLedger($operationType, $operationKey, $context, $preAssessment, $options);
        $this->operations->recordStep($ledger, 'operation.begin', 'Heavy I/O operation accepted', [
            'step_type' => 'validation',
            'sequence' => 1,
            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
            'payload' => $context,
            'started_at' => $ledger->started_at,
            'finished_at' => now(),
        ]);
        $this->events->record('heavy_io.operation.started', $options['summary'] ?? $operationType, [
            'operation_type' => $operationType,
            'event_type' => $eventType,
            'assessment' => $preAssessment->toArray(),
        ], $this->eventOptions($ledger, null, 'notice'));

        $step = $this->operations->recordStep($ledger, 'heavy_io.execute', 'Execute heavy I/O boundary call', [
            'step_type' => 'external_io',
            'sequence' => 20,
            'status' => ReliabilityPolicy::STEP_RUNNING,
            'payload' => $context,
            'started_at' => now(),
        ]);
        $startedAt = microtime(true);

        try {
            $value = $this->executeWithGuards($eventType, $runner, $ledger, null, $preAssessment);
            $payload = array_merge(
                $payloadResolver($value),
                ['elapsed_seconds' => round(microtime(true) - $startedAt, 3)]
            );
            $assessment = $this->policy->assess($eventType, $payload, $ledger, $options);

            $this->validateAfterWrite($eventType, $payload, $ledger, $assessment, $options);
            $this->operations->succeedStep($step, [
                'event_type' => $eventType,
                'assessment' => $assessment->toArray(),
            ]);

            $ledger->forceFill([
                'status' => ReliabilityPolicy::STATUS_COMMITTED,
                'result' => $this->resultSummary($eventType, $payload),
                'committed_at' => now(),
            ])->save();

            $outbox = $this->recordOutbox($ledger, $eventType, $payload, $assessment, $options);

            $this->events->record('heavy_io.operation.committed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'event_type' => $eventType,
                'outbox_message_id' => $outbox?->id,
            ], $this->eventOptions($ledger, $outbox));

            return $value;
        } catch (Throwable $throwable) {
            $this->operations->failStep($step, $throwable->getMessage());
            $payload = array_merge($context, [
                'status' => 'error',
                'failed' => true,
                'error_message' => $throwable->getMessage(),
                'elapsed_seconds' => round(microtime(true) - $startedAt, 3),
            ]);
            $assessment = $this->policy->assess($eventType, $payload, $ledger, $options);
            $this->routeQuarantineIfQualified($eventType, $payload, $ledger, $assessment);

            $ledger->forceFill([
                'status' => ReliabilityPolicy::STATUS_FAILED,
                'error_message' => $throwable->getMessage(),
                'failed_at' => now(),
            ])->save();

            $this->events->record('heavy_io.operation.failed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'event_type' => $eventType,
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
                'assessment' => $assessment->toArray(),
            ], $this->eventOptions($ledger, null, 'error'));

            return $fallback($throwable);
        }
    }

    /**
     * @template TResult
     *
     * @param array<string, mixed> $context
     * @param callable(): TResult $runner
     * @param callable(TResult): array<string, mixed> $payloadResolver
     * @param callable(Throwable): TResult $fallback
     * @param array<string, mixed> $options
     * @return TResult
     */
    private function runVolatile(
        string $operationType,
        string $eventType,
        array $context,
        callable $runner,
        callable $payloadResolver,
        callable $fallback,
        array $options,
        HeavyIoReliabilityAssessment $assessment,
    ): mixed {
        $this->events->record('heavy_io.operation.volatile.started', $options['summary'] ?? $operationType, [
            'operation_type' => $operationType,
            'event_type' => $eventType,
            'assessment' => $assessment->toArray(),
        ], [
            'criticality' => $assessment->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_MEMORY,
            'channel' => 'heavy_io',
            'source' => static::class,
        ]);

        try {
            $value = $runner();
            $payload = $payloadResolver($value);
            $this->events->record('heavy_io.operation.volatile.succeeded', $options['summary'] ?? $operationType, $payload, [
                'criticality' => $assessment->criticality,
                'storage_mode' => ReliabilityPolicy::STORAGE_MEMORY,
                'channel' => 'heavy_io',
                'source' => static::class,
            ]);

            return $value;
        } catch (Throwable $throwable) {
            $this->events->record('heavy_io.operation.volatile.failed', $options['summary'] ?? $operationType, [
                'operation_type' => $operationType,
                'event_type' => $eventType,
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ], [
                'criticality' => $assessment->criticality,
                'storage_mode' => ReliabilityPolicy::STORAGE_MEMORY,
                'channel' => 'heavy_io',
                'source' => static::class,
                'severity' => 'warning',
            ]);

            return $fallback($throwable);
        }
    }

    /**
     * @template TResult
     *
     * @param callable(): TResult $runner
     * @return TResult
     */
    private function executeWithGuards(
        string $eventType,
        callable $runner,
        ?OperationLedger $ledger,
        ?OutboxMessage $outboxMessage,
        HeavyIoReliabilityAssessment $assessment,
    ): mixed {
        $context = [
            'event_type' => $eventType,
            'cluster' => $assessment->cluster,
        ];

        $guardedRunner = function () use ($eventType, $runner, $ledger, $outboxMessage, $assessment, $context): mixed {
            if (!$assessment->circuitBreakerEligible) {
                return $runner();
            }

            $breaker = CircuitBreaker::builder('heavy_io.' . $this->breakerKeySegment($eventType))
                ->name('Heavy I/O ' . $eventType)
                ->criticality($assessment->criticality)
                ->channel('heavy_io')
                ->operationLedger($ledger)
                ->outboxMessage($outboxMessage)
                ->slidingWindowSize(20)
                ->slidingWindowSeconds(300)
                ->failureRateThreshold(50.0)
                ->minimumCalls(5)
                ->openStateDurationSeconds(90)
                ->halfOpenAllowedCalls($assessment->criticality === ReliabilityPolicy::CRITICALITY_CRITICAL ? 2 : 3)
                ->halfOpenConservative(in_array($assessment->criticality, [ReliabilityPolicy::CRITICALITY_CRITICAL, ReliabilityPolicy::CRITICALITY_HIGH], true))
                ->build();

            return $breaker->call(fn(): mixed => $runner(), $context);
        };

        if (!$assessment->retryEligible) {
            return $guardedRunner();
        }

        return Retry::builder('heavy_io.' . $eventType)
            ->criticality($assessment->criticality)
            ->channel('heavy_io')
            ->operationLedger($ledger)
            ->outboxMessage($outboxMessage)
            ->maxAttempts($assessment->maxAttempts)
            ->intervalUsing(fn(int $attempt): int => ReliabilityPolicy::retryDelaySeconds($attempt))
            ->withSleep(false)
            ->retryOnAny()
            ->abortOn(CircuitBreakerOpenException::class)
            ->build()
            ->run(fn(): mixed => $guardedRunner(), $context);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function validateAfterWrite(
        string $eventType,
        array $payload,
        OperationLedger $ledger,
        HeavyIoReliabilityAssessment $assessment,
        array $options,
    ): void {
        if (!$assessment->postWriteValidationRequired) {
            $this->operations->recordStep($ledger, 'heavy_io.post_write_validation', 'Assess heavy I/O post-write validation threshold', [
                'step_type' => 'validation',
                'sequence' => 90,
                'status' => ReliabilityPolicy::STEP_SKIPPED,
                'payload' => [
                    'event_type' => $eventType,
                    'cluster' => $assessment->cluster,
                ],
                'result' => [
                    'validated' => false,
                    'reason' => 'below_heavy_io_post_write_threshold',
                    'assessment' => $assessment->toArray(),
                ],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            return;
        }

        $validator = $options['post_write_validator'] ?? $this->validator;
        $validation = $validator instanceof HeavyIoPostWriteValidator
            ? $validator->validate($eventType, $payload, $ledger)
            : $this->validator->validate($eventType, $payload, $ledger);

        $this->operations->recordStep($ledger, 'heavy_io.post_write_validation', 'Validate heavy I/O result after execution', [
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

        if ($validation->passed) {
            return;
        }

        if ($this->policy->shouldQuarantine($validation, $assessment, $ledger)) {
            $this->quarantine->route($validation, $ledger);
        }

        throw new RuntimeException($validation->firstErrorMessage());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function routeQuarantineIfQualified(
        string $eventType,
        array $payload,
        OperationLedger $ledger,
        HeavyIoReliabilityAssessment $assessment,
    ): void {
        if (!$assessment->postWriteValidationRequired || !$assessment->quarantineCandidate) {
            return;
        }

        $validation = $this->validator->validate($eventType, $payload, $ledger);
        if (!$validation->passed && $this->policy->shouldQuarantine($validation, $assessment, $ledger)) {
            $this->quarantine->route($validation, $ledger);
        }
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $options
     */
    private function createLedger(
        string $operationType,
        string $operationKey,
        array $context,
        HeavyIoReliabilityAssessment $assessment,
        array $options,
    ): OperationLedger {
        return OperationLedger::create([
            'operation_key' => $operationKey,
            'operation_type' => $operationType,
            'domain' => 'heavy_io',
            'criticality' => $assessment->criticality,
            'isolation_level' => null,
            'status' => ReliabilityPolicy::STATUS_STARTED,
            'subject_type' => $options['subject_type'] ?? $context['source_class'] ?? null,
            'subject_id' => isset($options['subject_id']) ? (string) $options['subject_id'] : null,
            'actor_id' => $options['actor_id'] ?? Auth::id(),
            'correlation_id' => $options['correlation_id'] ?? null,
            'request_id' => $options['request_id'] ?? $this->requestId(),
            'summary' => $options['summary'] ?? 'Heavy I/O operation',
            'context' => array_merge($context, ['assessment' => $assessment->toArray()]),
            'started_at' => now(),
            'expires_at' => $options['expires_at'] ?? now()->addDays(ReliabilityPolicy::retentionDays('operation_ledger', $assessment->criticality)),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recordOutbox(
        OperationLedger $ledger,
        string $eventType,
        array $payload,
        HeavyIoReliabilityAssessment $assessment,
        array $options,
    ): ?OutboxMessage {
        $recorded = $this->outbox->record($eventType, $payload, [
            'message_key' => $this->normalizeMessageKey((string) ($options['message_key'] ?? $this->defaultMessageKey($eventType, $payload, $ledger))),
            'stream' => 'heavy_io.operations',
            'criticality' => $assessment->criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'status' => ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => $ledger->subject_type,
            'aggregate_id' => $ledger->subject_id,
            'operation_ledger_id' => $ledger->id,
            'headers' => $options['headers'] ?? null,
            'metadata' => [
                'operation_key' => $ledger->operation_key,
                'heavy_io_reliability' => $assessment->toArray(),
                'retry' => [
                    'eligible' => $assessment->retryEligible,
                    'max_attempts' => $assessment->maxAttempts,
                ],
                'circuit_breaker' => [
                    'eligible' => $assessment->circuitBreakerEligible,
                ],
            ],
            'max_attempts' => $assessment->maxAttempts,
        ]);

        return $recorded instanceof OutboxMessage ? $recorded : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function pythonContext(string $nameKey, string $name, array $data, array $options): array
    {
        return [
            $nameKey => $name,
            'integration_name' => $name,
            'rows_count' => is_countable($data['rows'] ?? null) ? count($data['rows']) : 0,
            'fields_count' => is_countable($data['fields'] ?? null) ? count($data['fields']) : 0,
            'payload_bytes' => $this->payloadBytes($data),
            'payload_fingerprint' => sha1($this->payloadForHash($data)),
            'source_class' => $options['source_class'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function importPayload(array $context, array $result): array
    {
        return array_merge($context, [
            'status' => (string) ($result['status'] ?? 'success'),
            'imported' => (int) ($result['imported'] ?? 0),
            'skipped' => (int) ($result['skipped'] ?? 0),
            'errors_count' => is_countable($result['errors'] ?? null) ? count($result['errors']) : 0,
        ]);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function exportPayload(array $context, string $result): array
    {
        $isFile = $result !== '' && is_file($result);

        return array_merge($context, [
            'status' => 'success',
            'output_path' => $isFile ? $result : null,
            'output_bytes' => $isFile ? (int) filesize($result) : strlen($result),
            'output_fingerprint' => sha1($isFile ? (string) realpath($result) : $result),
        ]);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function pythonImportSucceeded(array $result): bool
    {
        return strtolower((string) ($result['status'] ?? 'success')) !== 'error';
    }

    /**
     * @param array<string, mixed> $result
     */
    private function importErrorMessage(array $result): string
    {
        $errors = $result['errors'] ?? null;
        if (is_array($errors)) {
            foreach ($errors as $error) {
                if (is_string($error) && $error !== '') {
                    return $error;
                }
            }
        }

        return 'Python import returned an error status.';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function resultSummary(string $eventType, array $payload): array
    {
        return [
            'event_type' => $eventType,
            'status' => $payload['status'] ?? null,
            'rows_count' => $payload['rows_count'] ?? null,
            'imported' => $payload['imported'] ?? null,
            'skipped' => $payload['skipped'] ?? null,
            'errors_count' => $payload['errors_count'] ?? null,
            'output_bytes' => $payload['output_bytes'] ?? null,
            'delivered' => $payload['delivered'] ?? null,
            'elapsed_seconds' => $payload['elapsed_seconds'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function defaultMessageKey(string $eventType, array $payload, OperationLedger $ledger): string
    {
        $reference = $payload['payload_fingerprint']
            ?? $payload['output_fingerprint']
            ?? $payload['url_fingerprint']
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

    private function payloadBytes(mixed $payload): int
    {
        return strlen($this->payloadForHash($payload));
    }

    private function payloadForHash(mixed $payload): string
    {
        $encoded = json_encode($payload, JSON_PARTIAL_OUTPUT_ON_ERROR);

        return is_string($encoded) ? $encoded : serialize($payload);
    }

    private function requestId(): string
    {
        try {
            $request = request();
            $requestId = $request->headers->get('X-Request-Id') ?: $request->headers->get('X-Correlation-Id');
            if (is_string($requestId) && $requestId !== '') {
                return $requestId;
            }
        } catch (Throwable) {
            // CLI/test contexts may not have a request bound.
        }

        return 'req-' . (string) Str::uuid();
    }

    private function breakerKeySegment(string $eventType): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $eventType) ?: 'unknown';
    }

    /**
     * @return array<string, mixed>
     */
    private function eventOptions(?OperationLedger $ledger, ?OutboxMessage $message = null, string $severity = 'info'): array
    {
        return [
            'severity' => $severity,
            'criticality' => $ledger?->criticality ?? ReliabilityPolicy::CRITICALITY_MEDIUM,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => 'heavy_io',
            'source' => static::class,
            'operation_ledger_id' => $ledger?->id,
            'outbox_message_id' => $message?->id,
            'subject_type' => $ledger?->subject_type,
            'subject_id' => $ledger?->subject_id,
            'actor_id' => $ledger?->actor_id,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}

