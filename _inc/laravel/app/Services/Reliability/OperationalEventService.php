<?php

namespace App\Services\Reliability;

use App\Models\OperationalEvent;
use Illuminate\Support\Str;

class OperationalEventService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $memoryEvents = [];

    /**
     * @return OperationalEvent|array<string, mixed>
     */
    public function record(string $eventType, ?string $summary = null, array $context = [], array $options = []): OperationalEvent|array
    {
        $criticality = ReliabilityPolicy::normalizeCriticality($options['criticality'] ?? ReliabilityPolicy::CRITICALITY_LOW);
        $storageMode = ReliabilityPolicy::storageMode($criticality, $options['storage_mode'] ?? null);
        $occurredAt = $options['occurred_at'] ?? now();

        $record = [
            'event_key' => $options['event_key'] ?? 'evt-' . (string) Str::uuid(),
            'event_type' => $eventType,
            'severity' => $options['severity'] ?? 'info',
            'criticality' => $criticality,
            'storage_mode' => $storageMode,
            'channel' => $options['channel'] ?? null,
            'source' => $options['source'] ?? null,
            'operation_ledger_id' => $options['operation_ledger_id'] ?? null,
            'outbox_message_id' => $options['outbox_message_id'] ?? null,
            'subject_type' => $options['subject_type'] ?? null,
            'subject_id' => isset($options['subject_id']) ? (string) $options['subject_id'] : null,
            'actor_id' => $options['actor_id'] ?? null,
            'status' => $options['status'] ?? 'recorded',
            'summary' => $summary,
            'context' => $context ?: null,
            'occurred_at' => $occurredAt,
            'expires_at' => $options['expires_at'] ?? now()->addDays(ReliabilityPolicy::retentionDays('operational_event', $criticality)),
        ];

        if ($storageMode === ReliabilityPolicy::STORAGE_MEMORY) {
            self::$memoryEvents[] = $record;

            return $record;
        }

        return OperationalEvent::create($record);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function memoryEvents(): array
    {
        return self::$memoryEvents;
    }

    public static function flushMemory(): void
    {
        self::$memoryEvents = [];
    }
}
