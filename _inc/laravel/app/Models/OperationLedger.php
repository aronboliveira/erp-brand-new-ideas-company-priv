<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany};

class OperationLedger extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_OPERATION_LEDGERS;

    protected $fillable = [
        'operation_key',
        'operation_type',
        'domain',
        'criticality',
        'isolation_level',
        'status',
        'subject_type',
        'subject_id',
        'actor_id',
        'correlation_id',
        'request_id',
        'summary',
        'context',
        'result',
        'error_message',
        'started_at',
        'committed_at',
        'posted_at',
        'failed_at',
        'closed_at',
        'expires_at',
        'compressed_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'context' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'committed_at' => 'datetime',
        'posted_at' => 'datetime',
        'failed_at' => 'datetime',
        'closed_at' => 'datetime',
        'expires_at' => 'datetime',
        'compressed_at' => 'datetime',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(OperationStep::class, 'operation_ledger_id');
    }

    public function outboxMessages(): HasMany
    {
        return $this->hasMany(OutboxMessage::class, 'operation_ledger_id');
    }

    public function operationalEvents(): HasMany
    {
        return $this->hasMany(OperationalEvent::class, 'operation_ledger_id');
    }

    public function quarantines(): HasMany
    {
        return $this->hasMany(OperationQuarantine::class, 'operation_ledger_id');
    }
}
