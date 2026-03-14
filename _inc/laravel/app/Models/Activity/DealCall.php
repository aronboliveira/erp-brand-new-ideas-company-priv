<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\CallType;
use App\Traits\{HasAuditFields, NormalizesAddresses, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{Log};

class DealCall extends Model
{
    use HasAuditFields;
    use NormalizesAddresses;
    use UsesUuids;

    protected $table = DC::TABLE_DL_CALLS;

    private const FILLABLE_FIELDS = [
        UC::COL_USER_ID,
        'from',
        AC::COL_TO_ID,
        'to',
        AC::COL_FRM_ID,
        AC::COL_DL,
        'subject',
        AC::COL_CL_TP,
        AC::COL_CL_DT,
        AC::COL_CL_DUR,
        'duration',
        'description',
        AC::COL_CL_RS,
        'notes',
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $guarded = ['id'];

    protected $casts = [
        AC::COL_CL_TP => CallType::class,
        AC::COL_CL_DT => 'datetime',
        // call_duration é TIME no banco; manter como string HH:MM:SS
        AC::COL_CL_DUR => 'string',
    ];

    protected $appends = [
        'call_type_label',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (DealCall $model): void {
            $model->normalizeEndpoints();
            $model->normalizeCallType();
            $model->normalizeDurations();
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            if ($model->getAttribute('phone') && $isNormalizePhoneCallable)
                $model->setAttribute('phone', self::normalizePhone($model->getAttribute('phone'), 'Deal Call Phone', $model->id ?? null));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_FRM_ID, 'id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, AC::COL_TO_ID, 'id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function getCallTypeLabelAttribute(): string
    {
        try {
            $type = $this->getAttribute(AC::COL_CL_TP);
            if ($type instanceof CallType)
                return $type->label();
            if (is_string($type) && $type !== '')
                return CallType::normalize($type)->label();
            return '';
        } catch (\Throwable $e) {
            Log::error(static::class . '::getCallTypeLabelAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    private function normalizeEndpoints(): void
    {
        try {
            foreach (['from', 'to'] as $field) {
                $value = $this->getAttribute($field);
                $normalized = $this->normalizeEndpointValue($value);
                $this->setAttribute($field, $normalized);
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeEndpoints — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeEndpointValue(mixed $value): ?string
    {
        try {
            if ($value === null)
                return null;
            $value = trim((string) $value);
            if ($value === '')
                return null;
            if (str_contains($value, '@'))
                return filter_var(strtolower($value), FILTER_VALIDATE_EMAIL) ?: $value;
            $hasPlus = str_starts_with($value, '+');
            $digits = preg_replace('/\D+/', '', $value) ?: '';
            if ($digits === '')
                return null;
            return $hasPlus ? '+' . $digits : $digits;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeEndpointValue — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    private function normalizeCallType(): void
    {
        try {
            $raw = $this->getAttribute(AC::COL_CL_TP);
            if ($raw instanceof CallType)
                return;
            $enum = CallType::normalize(is_string($raw) ? $raw : null);
            $this->setAttribute(AC::COL_CL_TP, $enum);
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeCallType — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function normalizeDurations(): void
    {
        try {
            $durationRaw = $this->getAttribute('duration');
            $callDurationRaw = $this->getAttribute(AC::COL_CL_DUR);
            if (($durationRaw === null || $durationRaw === '') && is_string($callDurationRaw) && $callDurationRaw !== '') {
                $normalized = $this->normalizeDurationString($callDurationRaw);
                $this->setAttribute('duration', $normalized ?? substr($callDurationRaw, 0, 20));
                $this->setAttribute(AC::COL_CL_DUR, $normalized);
                return;
            }
            if ($durationRaw === null || $durationRaw === '') {
                $this->setAttribute('duration', null);
                $this->setAttribute(AC::COL_CL_DUR, null);
                return;
            }
            $normalized = $this->normalizeDurationString($durationRaw);
            if ($normalized !== null) {
                $this->setAttribute('duration', $normalized);
                $this->setAttribute(AC::COL_CL_DUR, $normalized);
                return;
            }
            $clean = substr((string) $durationRaw, 0, 20);
            $this->setAttribute('duration', $clean);
            $this->setAttribute(AC::COL_CL_DUR, null);
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeDurations — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
    private function normalizeDurationString(mixed $value): ?string
    {
        try {
            if ($value === null)
                return null;
            $value = trim((string) $value);
            if ($value === '')
                return null;
            if (preg_match('/^\d+$/', $value)) {
                $seconds = (int) $value;
                if ($seconds < 0)
                    $seconds = 0;
                $hours = intdiv($seconds, 3600);
                $minutes = intdiv($seconds % 3600, 60);
                $secs = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            }
            if (preg_match('/^(?<h>\d{1,2}):(?<m>\d{2})(:(?<s>\d{2}))?$/', $value, $m)) {
                $h = (int) $m['h'];
                $mi = (int) $m['m'];
                $s = isset($m['s']) && $m['s'] !== '' ? (int) $m['s'] : 0;
                if ($h < 0 || $mi < 0 || $mi > 59 || $s < 0 || $s > 59)
                    return null;
                return sprintf('%02d:%02d:%02d', $h, $mi, $s);
            }
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value))
                return $value;

            return null;
        } catch (\Throwable $e) {
            Log::error(static::class . '::normalizeDurationString — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return '';
        }
    }

    public function getDealCallUser(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
    }
}
