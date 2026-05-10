<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany};

class CircuitBreakerState extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays;

    protected $table = DC::TABLE_CIRCUIT_BREAKER_STATES;

    protected $fillable = [
        'breaker_key',
        'name',
        'domain',
        'criticality',
        'state',
        'sliding_window_size',
        'sliding_window_seconds',
        'failure_rate_threshold',
        'minimum_calls',
        'open_state_seconds',
        'half_open_allowed_calls',
        'half_open_success_threshold',
        'half_open_conservative',
        'config',
        'opened_at',
        'half_opened_at',
        'closed_at',
        'next_attempt_at',
        'last_failure_message',
        'expires_at',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'sliding_window_size' => 'integer',
        'sliding_window_seconds' => 'integer',
        'failure_rate_threshold' => 'float',
        'minimum_calls' => 'integer',
        'open_state_seconds' => 'integer',
        'half_open_allowed_calls' => 'integer',
        'half_open_success_threshold' => 'float',
        'half_open_conservative' => 'boolean',
        'config' => 'array',
        'opened_at' => 'datetime',
        'half_opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(CircuitBreakerCall::class, 'circuit_breaker_state_id');
    }
}
