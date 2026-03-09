<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AttendanceStatus;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
/**
 * @property \Illuminate\Support\Carbon|string|null $clock_in
 * @property \Illuminate\Support\Carbon|string|null $clock_out
 * @property mixed $created_by
 * @property float|int|null $early_arrival_count
 * @property string|null $early_leaving
 * @property float|int|null $early_leaving_count
 * @property float|int|null $late_count
 * @property float|int|null $total_rest
 * @property float|int|null $total_work
 * @property \Illuminate\Support\Carbon|null $date
 * @property mixed $late

 * @property mixed $overtime
 * @property int|null $status
 */

class EmployeeAttendance extends Model
{
    use HasAuditFields;
    use HasFactory;
    use UsesUuids;

    protected $table = DC::TABLE_EATD;

    protected $fillable = [
        UC::COL_EMP_ID,
        'date',
        'status',
        AC::COL_CLK_IN,
        AC::COL_CLK_OUT,
        AC::COL_ERL_ARV,
        AC::COL_ERL_AV_CT,
        'late',
        AC::COL_LT_CT,
        AC::COL_ERL_LV,
        AC::COL_ERL_LV_CT,
        'overtime',
        AC::COL_OVT_CT,
        AC::COL_OVT_ID,
        AC::COL_TT_RST,
        AC::COL_TT_WRK,
    ];

    protected $casts = [
        'date'             => 'date',
        'status'           => AttendanceStatus::class,
        AC::COL_ERL_AV_CT  => 'integer',
        AC::COL_LT_CT      => 'integer',
        AC::COL_ERL_LV_CT  => 'integer',
        AC::COL_OVT_CT     => 'integer',
        AC::COL_TT_RST     => 'string',
        AC::COL_TT_WRK     => 'string',
        AC::COL_CLK_IN     => 'string',
        AC::COL_CLK_OUT    => 'string',
        AC::COL_ERL_ARV    => 'string',
        AC::COL_ERL_LV     => 'string',
        'late'             => 'string',
        'overtime'         => 'string',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'employee',
        'overtime',
        'createdBy',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (!$model->{AC::COL_TT_RST}) $model->{AC::COL_TT_RST} = '00:00:00'; // @phpstan-ignore assign.propertyType
        });

        static::saving(function (self $model): void {
            $model->status = (int)AttendanceStatus::normalize($model->status ?? null)->value;

            $model->{AC::COL_ERL_AV_CT}  = max(0, (int) ($model->{AC::COL_ERL_AV_CT} ?? 0));
            $model->{AC::COL_LT_CT}      = max(0, (int) ($model->{AC::COL_LT_CT} ?? 0));
            $model->{AC::COL_ERL_LV_CT}  = max(0, (int) ($model->{AC::COL_ERL_LV_CT} ?? 0));
            $model->{AC::COL_OVT_CT}     = max(0, (int) ($model->{AC::COL_OVT_CT} ?? 0));

            $model->{AC::COL_TT_RST} = $model->normalizeDuration($model->{AC::COL_TT_RST} ?? '00:00:00') ?? '00:00:00'; // @phpstan-ignore assign.propertyType

            $model->ensureBefore(AC::COL_ERL_ARV, AC::COL_CLK_IN, AC::COL_ERL_AV_CT);
            $model->ensureAfter('late', AC::COL_CLK_IN, AC::COL_LT_CT);
            $model->ensureBefore(AC::COL_ERL_LV, AC::COL_CLK_OUT, AC::COL_ERL_LV_CT);
            $model->ensureAfter('overtime', AC::COL_CLK_OUT, AC::COL_OVT_CT);

            $model->{AC::COL_TT_WRK} = $model->normalizeDuration( // @phpstan-ignore assign.propertyType
                $model->{AC::COL_TT_WRK} ?: $model->calculateTotalWork()
            ) ?? '00:00:00';
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID);
    }

    public function overtime(): BelongsTo
    {
        return $this->belongsTo(Overtime::class, AC::COL_OVT_ID);
    }

    public function isPresent(): bool
    {
        return AttendanceStatus::normalize($this->status ?? null)->isPresent();
    }

    public function isAbsent(): bool
    {
        return AttendanceStatus::normalize($this->status ?? null)->isAbsent();
    }

    public function isAuthorizedAbsence(): bool
    {
        return AttendanceStatus::normalize($this->status ?? null)->isAuthorizedAbsence();
    }

    public function isRemote(): bool
    {
        return AttendanceStatus::normalize($this->status ?? null) === AttendanceStatus::Remote;
    }

    public function hasEarlyArrival(): bool
    {
        return $this->timeToSeconds($this->{AC::COL_ERL_ARV} ?? null) > 0;
    }

    public function hasLateArrival(): bool
    {
        return $this->timeToSeconds($this->{'late'} ?? null) > 0;
    }

    public function leftEarly(): bool
    {
        return $this->timeToSeconds($this->{AC::COL_ERL_LV} ?? null) > 0;
    }

    public function hasOvertime(): bool
    {
        return $this->timeToSeconds($this->{'overtime'} ?? null) > 0;
    }

    public function totalRestSeconds(): int
    {
        return $this->durationToSeconds($this->{AC::COL_TT_RST} ?? '00:00:00');
    }

    public function totalWorkSeconds(): int
    {
        return $this->durationToSeconds($this->{AC::COL_TT_WRK} ?? '00:00:00');
    }

    public function totalWorkAsDuration(): string
    {
        return $this->secondsToDuration($this->totalWorkSeconds());
    }

    public function calculateTotalWork(): string
    {
        $clockIn  = $this->timeToSeconds($this->{AC::COL_CLK_IN} ?? null);
        $clockOut = $this->timeToSeconds($this->{AC::COL_CLK_OUT} ?? null);

        if ($clockIn === null || $clockOut === null) return '00:00:00';
        if ($clockOut <= $clockIn) return '00:00:00';

        $workSeconds = $clockOut - $clockIn;
        $workSeconds -= $this->totalRestSeconds();
        if ($workSeconds < 0) $workSeconds = 0;

        return $this->secondsToDuration($workSeconds);
    }

    public function getWorkedInterval(): ?array
    {
        $clockIn  = $this->timeToSeconds($this->{AC::COL_CLK_IN} ?? null);
        $clockOut = $this->timeToSeconds($this->{AC::COL_CLK_OUT} ?? null);

        if ($clockIn === null || $clockOut === null || $clockOut <= $clockIn) return null;

        return [
            'start' => $this->{AC::COL_CLK_IN},
            'end'   => $this->{AC::COL_CLK_OUT},
            'seconds' => $clockOut - $clockIn,
        ];
    }

    protected function ensureBefore(string $field, string $referenceField, string $counterField): void
    {
        $time = $this->timeToSeconds($this->{$field} ?? null);
        $ref  = $this->timeToSeconds($this->{$referenceField} ?? null);

        if ($time === null || $ref === null) return;
        if ($time < $ref) return;

        $this->{$field} = '00:00:00';

        $count = (int) ($this->{$counterField} ?? 0);
        if ($count > 0) $this->{$counterField} = $count - 1;
    }

    protected function ensureAfter(string $field, string $referenceField, string $counterField): void
    {
        $time = $this->timeToSeconds($this->{$field} ?? null);
        $ref  = $this->timeToSeconds($this->{$referenceField} ?? null);

        if ($time === null || $ref === null) return;
        if ($time > $ref) return;

        $this->{$field} = '00:00:00';

        $count = (int) ($this->{$counterField} ?? 0);
        if ($count > 0) $this->{$counterField} = $count - 1;
    }

    protected function normalizeDuration(?string $value): ?string
    {
        if (!$value) return null;

        $seconds = $this->durationToSeconds($value);
        if ($seconds < 0) $seconds = 0;

        return $this->secondsToDuration($seconds);
    }

    protected function timeToSeconds(?string $value): ?int
    {
        if (!$value) return null;

        $value = trim($value);
        if ($value === '') return null;

        $parts = explode(':', $value);
        if (count($parts) < 2) return null;

        $h = (int) $parts[0];
        $m = (int) $parts[1];
        $s = isset($parts[2]) ? (int) $parts[2] : 0;

        if ($h < 0 || $m < 0 || $s < 0) return null;

        return $h * 3600 + $m * 60 + $s;
    }

    protected function durationToSeconds(?string $value): int
    {
        $seconds = $this->timeToSeconds($value);

        return $seconds === null ? 0 : $seconds;
    }

    protected function secondsToDuration(int $seconds): string
    {
        if ($seconds < 0) $seconds = 0;

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
