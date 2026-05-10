<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class OperationStep extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_OPERATION_STEPS;

    protected $fillable = [
        'operation_ledger_id',
        'step_key',
        'step_name',
        'step_type',
        'sequence',
        'status',
        'criticality',
        'storage_mode',
        'payload',
        'result',
        'error_message',
        DC::COL_RTR_CT,
        'started_at',
        'finished_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'sequence' => 'integer',
        DC::COL_RTR_CT => 'integer',
        'payload' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function operationLedger(): BelongsTo
    {
        return $this->belongsTo(OperationLedger::class, 'operation_ledger_id');
    }
}
