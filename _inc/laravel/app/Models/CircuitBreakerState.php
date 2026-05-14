<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Services\Reliability\ReliabilityPolicy;
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
        'slow_call_duration_ms',
        'slow_call_rate_threshold',
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
        'slow_call_duration_ms' => 'integer',
        'slow_call_rate_threshold' => 'float',
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

    /**
     * Ops handle — force the breaker into CIRCUIT_DISABLED. Subsequent guarded calls bypass admission
     * control (tryAcquirePermit returns [true, null]) but the persisted breaker config is preserved,
     * so re-enabling resumes normal sliding-window evaluation. Distinct from the builder's enabled(false)
     * which short-circuits the whole CircuitBreaker::call() path and writes no rows.
     *
     * Intended invocation: `php artisan tinker` → `CircuitBreakerState::where('breaker_key', 'foo')->first()->disable('incident #123')`
     */
    public function disable(string $reason = 'Manually disabled.'): self
    {
        $this->forceFill([
            'state' => ReliabilityPolicy::CIRCUIT_DISABLED,
            'last_failure_message' => $reason,
            'opened_at' => null,
            'half_opened_at' => null,
            'next_attempt_at' => null,
        ])->save();

        return $this;
    }

    /**
     * Ops handle — return a disabled breaker to closed state so the sliding window starts fresh.
     */
    public function enable(): self
    {
        $this->forceFill([
            'state' => ReliabilityPolicy::CIRCUIT_CLOSED,
            'closed_at' => now(),
            'opened_at' => null,
            'half_opened_at' => null,
            'next_attempt_at' => null,
            'last_failure_message' => null,
        ])->save();

        return $this;
    }
}
