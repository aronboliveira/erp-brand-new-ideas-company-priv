<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Services\Reliability\ReliabilityPolicy;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo, Relations\HasMany};
use Illuminate\Support\Facades\DB;

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

    /**
     * Ops handle — flip a manual-review quarantine to recovered. Use when the underlying business
     * invariant has been re-verified (manually or by a follow-up validation run) and the source
     * record is safe to allow downstream consumers to act on. Writes a 'recovered' audit row.
     *
     * Intended invocation: `php artisan tinker` →
     *   `OperationQuarantine::find('uuid')->recover('issue #123 resolved after manual reconciliation', $user->id)`
     */
    public function recover(string $notes = 'Manually recovered.', ?string $actorId = null): self
    {
        return $this->transitionTo(
            ReliabilityPolicy::QUARANTINE_RECOVERED,
            ReliabilityPolicy::QUARANTINE_ACTION_RECOVERED,
            $notes,
            $actorId,
        );
    }

    /**
     * Ops handle — flip a manual-review quarantine to dismissed. Use when the quarantine signal was
     * a false positive, the underlying record is already corrected by other means, or the quarantine
     * is no longer actionable (e.g. the source row was hard-deleted out of band). Writes a 'dismissed'
     * audit row. Unlike recover(), dismiss does NOT imply the data is now valid — only that this
     * quarantine entry is no longer worth tracking.
     */
    public function dismiss(string $notes = 'Manually dismissed.', ?string $actorId = null): self
    {
        return $this->transitionTo(
            ReliabilityPolicy::QUARANTINE_DISMISSED,
            ReliabilityPolicy::QUARANTINE_ACTION_DISMISSED,
            $notes,
            $actorId,
        );
    }

    private function transitionTo(string $newStatus, string $auditAction, string $notes, ?string $actorId): self
    {
        DB::transaction(function () use ($newStatus, $auditAction, $notes, $actorId): void {
            $this->forceFill([
                'status' => $newStatus,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
            ])->save();

            OperationQuarantineAudit::create([
                'operation_quarantine_id' => $this->id,
                'operation_ledger_id' => $this->operation_ledger_id,
                'source_record_id' => $this->source_record_id,
                'domain' => $this->domain,
                'action' => $auditAction,
                'actor_type' => $actorId ? 'human' : 'system',
                'actor_id' => $actorId,
                'details' => $notes,
                'occurred_at' => now(),
            ]);
        });

        return $this;
    }
}
