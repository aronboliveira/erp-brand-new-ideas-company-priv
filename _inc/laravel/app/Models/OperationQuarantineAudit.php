<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class OperationQuarantineAudit extends Model
{
    use HasFactory, UsesUuids, NormalizesArrays;

    protected $table = DC::TABLE_OPERATION_QUARANTINE_AUDITS;

    protected $fillable = [
        'operation_quarantine_id',
        'operation_ledger_id',
        'source_record_id',
        'domain',
        'action',
        'actor_type',
        'actor_id',
        'details',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function quarantine(): BelongsTo
    {
        return $this->belongsTo(OperationQuarantine::class, 'operation_quarantine_id');
    }

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }
}
