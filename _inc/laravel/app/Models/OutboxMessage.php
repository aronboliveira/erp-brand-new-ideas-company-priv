<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo, Relations\HasMany};

class OutboxMessage extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_OUTBOX_MESSAGES;

    protected $fillable = [
        'message_key',
        'stream',
        'event_type',
        'criticality',
        'storage_mode',
        'status',
        'aggregate_type',
        'aggregate_id',
        'operation_ledger_id',
        'payload',
        'headers',
        'metadata',
        DC::COL_RTR_CT,
        'max_attempts',
        'last_error',
        'available_at',
        'next_retry_at',
        'dispatched_at',
        DC::COL_FL_AT,
        'expires_at',
        'compressed_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'metadata' => 'array',
        DC::COL_RTR_CT => 'integer',
        'max_attempts' => 'integer',
        'available_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'dispatched_at' => 'datetime',
        DC::COL_FL_AT => 'datetime',
        'expires_at' => 'datetime',
        'compressed_at' => 'datetime',
    ];

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }

    public function operationalEvents(): HasMany
    {
        return $this->hasMany(OperationalEvent::class, 'outbox_message_id');
    }
}
