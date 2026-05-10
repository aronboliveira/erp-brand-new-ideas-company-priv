<?php

namespace App\Services\Reliability;

use App\Models\InboxMessage;

class InboxService
{
    public function recordReceived(string $messageKey, string $eventType, array $payload = [], array $options = []): InboxMessage
    {
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? ReliabilityPolicy::CRITICALITY_MEDIUM);
        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');

        return InboxMessage::firstOrCreate(
            ['message_key' => $messageKey],
            [
                'source' => $options['source'] ?? null,
                'event_type' => $eventType,
                'criticality' => $criticality,
                'status' => $options['status'] ?? 'received',
                'payload_hash' => $payloadHash,
                'operation_ledger_id' => $options['operation_ledger_id'] ?? null,
                'payload' => $payload ?: null,
                'metadata' => $options['metadata'] ?? null,
                'retry_count' => 0,
                'received_at' => $options['received_at'] ?? now(),
                'expires_at' => $options['expires_at'] ?? now()->addDays(ReliabilityPolicy::retentionDays('inbox_message', $criticality)),
            ]
        );
    }

    public function isProcessed(string $messageKey): bool
    {
        return InboxMessage::where('message_key', $messageKey)
            ->where('status', 'processed')
            ->exists();
    }

    public function markProcessed(InboxMessage $message): InboxMessage
    {
        $message->forceFill([
            'status' => 'processed',
            'processed_at' => now(),
            'last_error' => null,
        ])->save();

        return $message;
    }

    public function markFailed(InboxMessage $message, string $error): InboxMessage
    {
        $message->forceFill([
            'status' => 'failed',
            'failed_at' => now(),
            'last_error' => $error,
            'retry_count' => ((int) $message->retry_count) + 1,
        ])->save();

        return $message;
    }
}
