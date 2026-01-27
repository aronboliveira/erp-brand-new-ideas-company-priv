<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\Frequency;
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\Log;

class PlanRequest extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_PLAN_REQUESTS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        UC::COL_USER_ID,
        UC::COL_PLAN_ID,
        'duration',
        'notes',
        'attachments',
    ];

    protected $appends = [
        'duration_label',
        'is_monthly',
    ];

    protected $with = [
        'user',
        'plan',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            try {
                $uid = trim((string) $m->getAttribute(UC::COL_USER_ID));
                $m->setAttribute(UC::COL_USER_ID, $uid === '' ? null : $uid);
                $pid = trim((string) $m->getAttribute(UC::COL_PLAN_ID));
                $m->setAttribute(UC::COL_PLAN_ID, $pid === '' ? null : $pid);
                $m->setAttribute('duration', $m->durationEnum()->value);
                $m->setAttribute('attachments', self::normalizeArrayField($m->getAttribute('attachments')));
            } catch (\Throwable $e) {
                Log::error(self::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'table' => $m->getTable(),
                    'model_id' => $m->getAttribute('id') ?? null,
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, UC::COL_PLAN_ID, 'id');
    }

    public function durationEnum(): Frequency
    {
        try {
            $raw = $this->getAttribute('duration');
            $norm = Frequency::normalize($raw instanceof Frequency ? $raw : $raw);
            return $norm ?? Frequency::Monthly;
        } catch (\Throwable $e) {
            Log::warning(self::class . ' failed normalizing duration enum', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'model_id' => $this->getAttribute('id') ?? null,
                'raw' => $this->getAttribute('duration'),
            ]);
            return Frequency::Monthly;
        }
    }

    public function getDurationLabelAttribute(): string
    {
        try {
            $enum = $this->durationEnum();
            return is_callable([$enum, 'label']) ? $enum->label() : $enum->value;
        } catch (\Throwable $e) {
            Log::debug(self::class . ' duration label fallback', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'model_id' => $this->getAttribute('id') ?? null,
            ]);
            return (string) ($this->getAttribute('duration') ?? Frequency::Monthly->value);
        }
    }

    public function getIsMonthlyAttribute(): bool
    {
        return $this->durationEnum() === Frequency::Monthly;
    }

    public function setDuration(Frequency|string|null $duration): void
    {
        $enum = $duration instanceof Frequency ? $duration : (Frequency::normalize($duration) ?? Frequency::Monthly);
        $this->setAttribute('duration', $enum->value);
    }
}
