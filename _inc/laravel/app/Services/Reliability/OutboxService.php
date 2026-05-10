<?php

namespace App\Services\Reliability;

use App\Models\OutboxMessage;
use Illuminate\Support\Str;

class OutboxService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $memoryMessages = [];

    /**
     * @return OutboxMessage|array<string, mixed>
     */
    public function record(string $eventType, array $payload = [], array $options = []): OutboxMessage|array
    {
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? ReliabilityPolicy::CRITICALITY_MEDIUM);
        $storageMode = ReliabilityPolicy::storageMode($criticality, $options['storage_mode'] ?? null);
        $messageKey = $options['message_key'] ?? 'outbox-' . (string) Str::uuid();

        $record = [
            'message_key' => $messageKey,
            'stream' => $options['stream'] ?? 'system.default',
            'event_type' => $eventType,
            'criticality' => $criticality,
            'storage_mode' => $storageMode,
            'status' => $options['status'] ?? ReliabilityPolicy::OUTBOX_PENDING,
            'aggregate_type' => $options['aggregate_type'] ?? null,
            'aggregate_id' => isset($options['aggregate_id']) ? (string) $options['aggregate_id'] : null,
            'operation_ledger_id' => $options['operation_ledger_id'] ?? null,
            'payload' => $payload ?: null,
            'headers' => $options['headers'] ?? null,
            'metadata' => $options['metadata'] ?? null,
            'retry_count' => $options['retry_count'] ?? 0,
            'max_attempts' => $options['max_attempts'] ?? 3,
            'last_error' => $options['last_error'] ?? null,
            'available_at' => $options['available_at'] ?? now(),
            'next_retry_at' => $options['next_retry_at'] ?? null,
            'expires_at' => $options['expires_at'] ?? now()->addDays(ReliabilityPolicy::retentionDays('outbox_message', $criticality)),
        ];

        if ($storageMode === ReliabilityPolicy::STORAGE_MEMORY) {
            self::$memoryMessages[] = $record;

            return $record;
        }

        $values = $record;
        unset($values['message_key']);

        return OutboxMessage::firstOrCreate(['message_key' => $messageKey], $values);
    }

    public function markReady(OutboxMessage $message): OutboxMessage
    {
        $message->forceFill([
            'status' => ReliabilityPolicy::OUTBOX_READY,
            'available_at' => $message->available_at ?? now(),
        ])->save();

        return $message;
    }

    public function markDispatched(OutboxMessage $message): OutboxMessage
    {
        $message->forceFill([
            'status' => ReliabilityPolicy::OUTBOX_DISPATCHED,
            'dispatched_at' => now(),
            'failed_at' => null,
            'next_retry_at' => null,
            'last_error' => null,
        ])->save();

        return $message;
    }

    public function markFailed(OutboxMessage $message, string $error, bool $deadLetter = false): OutboxMessage
    {
        $retryCount = ((int) $message->retry_count) + 1;

        $message->forceFill([
            'status' => $deadLetter ? ReliabilityPolicy::OUTBOX_DEAD_LETTER : ReliabilityPolicy::OUTBOX_FAILED,
            'failed_at' => now(),
            'last_error' => $error,
            'retry_count' => $retryCount,
            'next_retry_at' => $deadLetter ? null : now()->addSeconds(ReliabilityPolicy::retryDelaySeconds($retryCount)),
        ])->save();

        return $message;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function memoryMessages(): array
    {
        return self::$memoryMessages;
    }

    public static function flushMemory(): void
    {
        self::$memoryMessages = [];
    }
}
