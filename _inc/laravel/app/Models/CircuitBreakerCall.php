<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};

class CircuitBreakerCall extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_CIRCUIT_BREAKER_CALLS;

    protected $fillable = [
        'circuit_breaker_state_id',
        'breaker_key',
        'state_before',
        'state_after',
        'status',
        'duration_ms',
        'error_class',
        'error_message',
        'context',
        'occurred_at',
        'expires_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'context' => 'array',
        'occurred_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function circuitBreakerState(): BelongsTo
    {
        return $this->belongsTo(CircuitBreakerState::class, 'circuit_breaker_state_id');
    }
}
