<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo, Relations\HasMany};

class OperationQuarantine extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_OPERATION_QUARANTINES;

    protected $fillable = [
        'operation_ledger_id',
        'source_table',
        'source_type',
        'source_record_id',
        'domain',
        'severity',
        'status',
        'remediation_decision',
        'failed_criteria',
        'validation_errors',
        'snapshot_payload',
        'origin_event',
        'resolution_notes',
        'actor_id',
        'quarantined_at',
        'resolved_at',
        'expires_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'failed_criteria' => 'array',
        'validation_errors' => 'array',
        'snapshot_payload' => 'array',
        'origin_event' => 'array',
        'quarantined_at' => 'datetime',
        'resolved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(OperationQuarantineAudit::class, 'operation_quarantine_id');
    }
}
