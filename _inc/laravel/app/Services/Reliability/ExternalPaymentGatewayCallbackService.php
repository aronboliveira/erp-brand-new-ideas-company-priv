<?php

namespace App\Services\Reliability;

use App\Models\{InboxMessage, OperationLedger};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionFunction;
use RuntimeException;
use Throwable;

class ExternalPaymentGatewayCallbackService
{
    private CriticalOperationService $operations;

    private InboxService $inbox;

    private OperationalEventService $events;

    public function __construct(
        ?CriticalOperationService $operations = null,
        ?InboxService $inbox = null,
        ?OperationalEventService $events = null,
    ) {
        $this->operations = $operations ?? new CriticalOperationService();
        $this->inbox = $inbox ?? new InboxService();
        $this->events = $events ?? new OperationalEventService();
    }

    /**
     * @template TResult
     *
     * @param callable(?OperationLedger, CriticalOperationService, InboxMessage): TResult $callback
     * @return TResult
     *
     * @throws Throwable
     */
    public function handle(string $provider, string $flow, Request $request, callable $callback, array $options = []): mixed
    {
        $provider = $this->normalizeSegment($provider);
        $flow = $this->normalizeSegment($flow);
        $eventType = $this->eventType($provider, $flow, 'processed');
        $payload = $this->payload($provider, $flow, $request, $options);
        $assessment = (new FinanceReliabilityPolicy())->assess($eventType, $payload, null, array_merge($options, [
            'external_origin' => true,
        ]));
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? $assessment->criticality);
        $messageKey = $this->messageKey($provider, $flow, $payload, $options);
        $payloadHash = $this->payloadHash($payload);

        $inbox = $this->inbox->recordReceived($messageKey, $this->eventType($provider, $flow, 'received'), $payload, [
            'source' => "external_payment_gateway:{$provider}",
            'criticality' => $criticality,
            'metadata' => $this->inboxMetadata($request, $options),
        ]);

        $context = [
            'provider' => $provider,
            'flow' => $flow,
            'event_type' => $eventType,
            'message_key' => $messageKey,
            'subject_type' => $payload['subject_type'] ?? null,
            'subject_id' => $payload['subject_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
        ];

        if (!$inbox->wasRecentlyCreated && $inbox->payload_hash && !hash_equals((string) $inbox->payload_hash, $payloadHash)) {
            $this->events->record('finance.gateway_callback.replay_mismatch', 'Gateway callback replay used a mismatched payload.', $context, $this->eventOptions($criticality, $payload, 'warning'));

            if ($inbox->status === 'processed') {
                return $this->duplicateResponse($options, $inbox);
            }

            $this->inbox->markFailed($inbox, 'Gateway callback payload hash mismatch for idempotency key.');

            throw new RuntimeException('Gateway callback payload hash mismatch for idempotency key.');
        }

        if ($inbox->status === 'processed') {
            $this->events->record('finance.gateway_callback.duplicate', 'Gateway callback already processed.', $context, $this->eventOptions($criticality, $payload, 'notice'));

            return $this->duplicateResponse($options, $inbox);
        }

        $this->events->record('finance.gateway_callback.received', 'Gateway callback accepted for guarded processing.', $context, $this->eventOptions($criticality, $payload, 'notice'));

        $retry = Retry::builder("finance.gateway_callback.{$provider}.{$flow}")
            ->maxAttempts((int) ($options['max_attempts'] ?? $assessment->maxAttempts))
            ->criticality($criticality)
            ->channel('finance.gateway')
            ->events($this->events)
            // Synchronous webhook handler — caller holds the connection; default 30s-base backoff would time out the gateway. Preserve immediate retry; tune intervalUsing() if jitter/backoff is needed.
            ->withSleep(false)
            ->retryOnAny()
            ->build();

        try {
            return $retry->run(function (int $attempt) use ($provider, $flow, $callback, $options, $payload, $criticality, $messageKey, $eventType, $context, $inbox): mixed {
                $breaker = CircuitBreaker::builder("finance.gateway_callback.{$provider}.{$flow}")
                    ->name("Finance gateway callback: {$provider} {$flow}")
                    ->criticality($criticality)
                    ->channel('finance.gateway')
                    ->slidingWindowSize((int) ($options['sliding_window_size'] ?? 12))
                    ->slidingWindowSeconds((int) ($options['sliding_window_seconds'] ?? 300))
                    ->failureRateThreshold((float) ($options['failure_rate_threshold'] ?? 35.0))
                    ->minimumCalls((int) ($options['minimum_calls'] ?? 3))
                    ->openStateDurationSeconds((int) ($options['open_state_seconds'] ?? 60))
                    ->halfOpenAllowedCalls((int) ($options['half_open_allowed_calls'] ?? 2))
                    ->halfOpenConservative((bool) ($options['half_open_conservative'] ?? true))
                    ->events($this->events)
                    ->build();

                $operationKey = $this->operationKey($messageKey, (int) $inbox->retry_count, $attempt, $options);

                return $breaker->call(function () use ($callback, $options, $payload, $criticality, $messageKey, $eventType, $context, $inbox, $operationKey): mixed {
                    $result = $this->operations->run("finance.gateway_callback.{$payload['provider']}.{$payload['flow']}", function (?OperationLedger $ledger, CriticalOperationService $operations) use ($callback, $payload, $inbox): mixed {
                        if ($ledger) {
                            $inbox->forceFill([
                                'operation_ledger_id' => $ledger->id,
                                'status' => 'processing',
                            ])->save();
                        }

                        $operations->recordStep($ledger, 'gateway.callback.received', 'Gateway callback idempotency key accepted', [
                            'step_type' => 'validation',
                            'sequence' => 20,
                            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                            'payload' => [
                                'provider' => $payload['provider'],
                                'flow' => $payload['flow'],
                                'message_key' => $inbox->message_key,
                            ],
                            'started_at' => now(),
                            'finished_at' => now(),
                        ]);

                        $result = $this->invokeCallback($callback, $ledger, $operations, $inbox);

                        $this->inbox->markProcessed($inbox);

                        $operations->recordStep($ledger, 'gateway.callback.processed', 'Gateway callback mutation completed', [
                            'step_type' => 'dispatch',
                            'sequence' => 95,
                            'status' => ReliabilityPolicy::STEP_SUCCEEDED,
                            'result' => ['result_type' => get_debug_type($result)],
                            'started_at' => now(),
                            'finished_at' => now(),
                        ]);

                        return $result;
                    }, [
                        'operation_key' => $operationKey,
                        'domain' => 'finance',
                        'criticality' => $criticality,
                        'subject_type' => $payload['subject_type'] ?? 'external_payment_gateway_callback',
                        'subject_id' => $payload['subject_id'] ?? null,
                        'actor_id' => $payload['actor_id'] ?? null,
                        'correlation_id' => $messageKey,
                        'summary' => $options['summary'] ?? "External payment gateway callback: {$payload['provider']} {$payload['flow']}",
                        'context' => $payload,
                        'final_status' => ReliabilityPolicy::STATUS_COMMITTED,
                        'outbox' => [
                            'message_key' => $this->outboxMessageKey($messageKey),
                            'stream' => 'finance.ledger',
                            'event_type' => $eventType,
                            'aggregate_type' => $payload['subject_type'] ?? 'external_payment_gateway_callback',
                            'aggregate_id' => $payload['subject_id'] ?? $messageKey,
                            'payload' => $payload,
                            'metadata' => [
                                'idempotency' => [
                                    'inbox_message_id' => $inbox->id,
                                    'message_key' => $messageKey,
                                ],
                                'gateway_callback' => $context,
                                'retry' => [
                                    'eligible' => true,
                                ],
                            ],
                        ],
                    ]);

                    $ledger = OperationLedger::where('operation_key', $operationKey)->first();
                    $this->events->record('finance.gateway_callback.processed', 'Gateway callback processed.', $context, $this->eventOptions($criticality, $payload, 'info', $ledger));

                    return $result;
                }, $context);
            }, $context);
        } catch (Throwable $throwable) {
            if ($inbox->status !== 'processed') {
                $this->inbox->markFailed($inbox, $throwable->getMessage());
            }

            $this->events->record('finance.gateway_callback.failed', 'Gateway callback processing failed.', array_merge($context, [
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ]), $this->eventOptions($criticality, $payload, 'error'));

            throw $throwable;
        }
    }

    public function recordRejected(string $provider, string $flow, Request $request, string $reason, array $options = []): InboxMessage
    {
        $provider = $this->normalizeSegment($provider);
        $flow = $this->normalizeSegment($flow);
        $payload = $this->payload($provider, $flow, $request, $options);
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? ReliabilityPolicy::CRITICALITY_HIGH);
        $messageKey = $this->messageKey($provider, $flow, $payload, $options);

        $inbox = $this->inbox->recordReceived($messageKey, $this->eventType($provider, $flow, 'rejected'), $payload, [
            'source' => "external_payment_gateway:{$provider}",
            'criticality' => $criticality,
            'metadata' => array_merge($this->inboxMetadata($request, $options), ['rejection_reason' => $reason]),
        ]);
        if ($inbox->status !== 'processed') {
            $this->inbox->markFailed($inbox, $reason);
        }

        $this->events->record('finance.gateway_callback.rejected', 'Gateway callback rejected before processing.', [
            'provider' => $provider,
            'flow' => $flow,
            'message_key' => $messageKey,
            'reason' => $reason,
        ], $this->eventOptions($criticality, $payload, 'warning'));

        return $inbox;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function messageKey(string $provider, string $flow, array $payload, array $options = []): string
    {
        if (isset($options['message_key']) && (string) $options['message_key'] !== '') {
            return $this->normalizeMessageKey((string) $options['message_key']);
        }

        $reference = $options['provider_reference']
            ?? $payload['provider_reference']
            ?? data_get($payload, 'request.tap_id')
            ?? data_get($payload, 'request.order_id')
            ?? data_get($payload, 'request.cf_payment_id')
            ?? data_get($payload, 'request.payment_id')
            ?? data_get($payload, 'request.tran_ref')
            ?? data_get($payload, 'request.transaction_id')
            ?? data_get($payload, 'request.cart_id')
            ?? data_get($payload, 'request.reference')
            ?? null;
        $subject = $options['subject_id'] ?? $payload['subject_id'] ?? null;

        if ($reference !== null && (string) $reference !== '') {
            $key = "external-gateway:{$provider}:{$flow}:ref:" . (string) $reference;
            if ($subject !== null && (string) $subject !== '') {
                $key .= ':subject:' . (string) $subject;
            }

            return $this->normalizeMessageKey($key);
        }

        return $this->normalizeMessageKey("external-gateway:{$provider}:{$flow}:payload:" . sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: ''));
    }

    private function duplicateResponse(array $options, InboxMessage $inbox): mixed
    {
        $duplicate = $options['duplicate_response'] ?? null;
        if (!is_callable($duplicate)) {
            return null;
        }

        $callable = \Closure::fromCallable($duplicate);
        $parameters = (new ReflectionFunction($callable))->getNumberOfParameters();

        return $parameters >= 1 ? $callable($inbox) : $callable();
    }

    private function invokeCallback(callable $callback, ?OperationLedger $ledger, CriticalOperationService $operations, InboxMessage $inbox): mixed
    {
        $callable = \Closure::fromCallable($callback);
        $parameters = (new ReflectionFunction($callable))->getNumberOfParameters();

        return match (true) {
            $parameters >= 3 => $callable($ledger, $operations, $inbox),
            $parameters === 2 => $callable($ledger, $operations),
            $parameters === 1 => $callable($ledger),
            default => $callable(),
        };
    }

    private function eventType(string $provider, string $flow, string $verb): string
    {
        return "finance.gateway_callback.{$provider}.{$flow}.{$verb}";
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $provider, string $flow, Request $request, array $options): array
    {
        $requestPayload = $request->all();
        ksort($requestPayload);

        $amount = $options['amount']
            ?? $requestPayload['amount']
            ?? $requestPayload['order_amount']
            ?? $requestPayload['payment_amount']
            ?? $requestPayload['value']
            ?? null;

        return [
            'provider' => $provider,
            'flow' => $flow,
            'request' => $requestPayload,
            'route_parameters' => $options['route_parameters'] ?? [],
            'provider_reference' => $options['provider_reference'] ?? null,
            'subject_type' => $options['subject_type'] ?? null,
            'subject_id' => isset($options['subject_id']) ? (string) $options['subject_id'] : null,
            'actor_id' => $options['actor_id'] ?? $request->user()?->getAuthIdentifier(),
            'amount' => is_numeric($amount) ? (float) $amount : $amount,
            'external_origin' => true,
            'transaction_type' => 'gateway_callback',
            'metadata' => $options['metadata'] ?? [],
            'signature' => $this->signatureContext($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function signatureContext(Request $request): array
    {
        $headers = [];
        foreach ([
            'stripe-signature',
            'x-cashfree-signature',
            'x-webhook-signature',
            'x-paytabs-signature',
            'tap-signature',
        ] as $header) {
            $value = $request->headers->get($header);
            if ($value !== null && $value !== '') {
                $headers[$header] = hash('sha256', $value);
            }
        }

        return [
            'status' => $headers === [] ? 'not_available_return_url' : 'present_hashed',
            'headers' => $headers,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inboxMetadata(Request $request, array $options): array
    {
        return [
            'method' => $request->method(),
            'path' => $request->path(),
            'gateway_callback' => true,
            'notes' => $options['notes'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function eventOptions(string $criticality, array $payload, string $severity, ?OperationLedger $ledger = null): array
    {
        return [
            'severity' => $severity,
            'criticality' => $criticality,
            'storage_mode' => ReliabilityPolicy::STORAGE_DATABASE,
            'channel' => 'finance.gateway',
            'source' => static::class,
            'operation_ledger_id' => $ledger?->id,
            'subject_type' => $payload['subject_type'] ?? null,
            'subject_id' => $payload['subject_id'] ?? null,
            'actor_id' => $payload['actor_id'] ?? null,
        ];
    }

    private function operationKey(string $messageKey, int $priorFailures, int $attempt, array $options): string
    {
        $base = (string) ($options['operation_key'] ?? "gateway-callback:{$messageKey}");

        return $this->normalizeMessageKey($base . ':retry:' . ($priorFailures + 1) . ':attempt:' . $attempt);
    }

    private function outboxMessageKey(string $messageKey): string
    {
        return $this->normalizeMessageKey($messageKey . ':processed');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');
    }

    private function normalizeSegment(string $segment): string
    {
        $normalized = (string) Str::of($segment)->lower()->replaceMatches('/[^a-z0-9_.-]+/', '_')->trim('_');

        return $normalized === '' ? 'unknown' : $normalized;
    }

    private function normalizeMessageKey(string $messageKey): string
    {
        $messageKey = preg_replace('/[^A-Za-z0-9:_.-]+/', '-', $messageKey) ?: 'external-gateway:unknown';

        if (strlen($messageKey) <= 191) {
            return $messageKey;
        }

        return substr($messageKey, 0, 145) . ':' . sha1($messageKey);
    }
}
