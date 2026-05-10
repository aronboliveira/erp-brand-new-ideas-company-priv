<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class InboxMessage extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_INBOX_MESSAGES;

    protected $fillable = [
        'message_key',
        'source',
        'event_type',
        'criticality',
        'status',
        'payload_hash',
        'operation_ledger_id',
        'payload',
        'metadata',
        DC::COL_RTR_CT,
        'last_error',
        'received_at',
        'processed_at',
        DC::COL_FL_AT,
        'expires_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'payload' => 'array',
        'metadata' => 'array',
        DC::COL_RTR_CT => 'integer',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        DC::COL_FL_AT => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }
}
