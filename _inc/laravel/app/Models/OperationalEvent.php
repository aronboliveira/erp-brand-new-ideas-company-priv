<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class OperationalEvent extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_OPERATIONAL_EVENTS;

    protected $fillable = [
        'event_key',
        'event_type',
        'severity',
        'criticality',
        'storage_mode',
        'channel',
        'source',
        'operation_ledger_id',
        'outbox_message_id',
        'subject_type',
        'subject_id',
        'actor_id',
        'status',
        'summary',
        'context',
        'occurred_at',
        'expires_at',
        'compressed_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
        'expires_at' => 'datetime',
        'compressed_at' => 'datetime',
    ];

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }

    public function outboxMessage(): BelongsTo
    {
        return $this->belongsTo(OutboxMessage::class, 'outbox_message_id');
    }
}
