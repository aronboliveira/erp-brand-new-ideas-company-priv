<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{Confirmation, Frequency, Weekday, WorkShift};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOnBoard extends Model
{
    use UsesUuids, HasAuditFields, DefinesDates;

    protected $table = DC::TABLE_JB_BRD;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER];

    protected $fillable = [
        AC::COL_APLN_ID,

        AC::COL_WRK_AUTH_APV,
        AC::COL_RLC_CMP,
        AC::COL_TRMS_ACPT,
        AC::COL_NTC_PRD_CMP,
        AC::COL_RFR_APV,
        AC::COL_FM_APV,
        AC::COL_RCT_APV,
        AC::COL_MNG_APV,
        AC::COL_EXP_APV,
        AC::COL_SKL_APV,
        AC::COL_LCT_APV,
        AC::COL_ITV_APRV,
        AC::COL_TEST_APV,
        AC::COL_BG_CK_APV,
        AC::COL_DEI_APV,

        AC::COL_JNG_DT,
        'status',
        AC::COL_CNV_TO_EMP,

        AC::COL_JB_TP,
        AC::COL_DYS_WK,
        AC::COL_WRK_DYS,

        'salary',
        AC::COL_SLR_TP,
        AC::COL_SLR_DUR,

        'contract',
        'trainer',
        'trainings',
    ];

    protected $with = [
        'application',
    ];

    protected $casts = [
        AC::COL_WRK_AUTH_APV => 'bool',
        AC::COL_RLC_CMP => 'bool',
        AC::COL_TRMS_ACPT => 'bool',
        AC::COL_NTC_PRD_CMP => 'bool',
        AC::COL_RFR_APV => 'bool',
        AC::COL_FM_APV => 'bool',
        AC::COL_RCT_APV => 'bool',
        AC::COL_MNG_APV => 'bool',
        AC::COL_EXP_APV => 'bool',
        AC::COL_SKL_APV => 'bool',
        AC::COL_LCT_APV => 'bool',
        AC::COL_ITV_APRV => 'bool',
        AC::COL_TEST_APV => 'bool',
        AC::COL_BG_CK_APV => 'bool',
        AC::COL_DEI_APV => 'bool',

        AC::COL_JNG_DT => 'date',

        AC::COL_CNV_TO_EMP => 'int',
        AC::COL_DYS_WK => 'int',

        'salary' => 'decimal:2',

        AC::COL_WRK_DYS => 'array',
        'trainings' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            $m->enforcePseudoBoolean(AC::COL_CNV_TO_EMP);

            $days = (int) ($m->getAttribute(AC::COL_DYS_WK) ?? 5);
            if ($days < 1) $days = 1;
            if ($days > 7) $days = 7;
            $m->setAttribute(AC::COL_DYS_WK, $days);

            $shift = WorkShift::normalize($m->getAttribute(AC::COL_JB_TP) ?? null) ?? WorkShift::FullTime;
            $m->setAttribute(AC::COL_JB_TP, $shift->value);

            $raw = $m->getAttribute(AC::COL_WRK_DYS);
            $normDays = $m->normalizeWeekdays($raw);
            $m->setAttribute(AC::COL_WRK_DYS, $normDays);

            $st = $m->getAttribute(AC::COL_SLR_TP);
            $conf = Confirmation::normalize($st);
            $freq = Frequency::normalize($st);
            if ($conf) $m->setAttribute(AC::COL_SLR_TP, $conf->value);
            elseif ($freq) $m->setAttribute(AC::COL_SLR_TP, $freq->value);

            $salary = $m->getAttribute('salary');
            if ($salary !== null && is_numeric($salary) && (float) $salary < 0) $m->setAttribute('salary', 0);

            $dei = $m->getAttribute(AC::COL_DEI_APV);
            if ($dei !== null && $dei === false) $m->setAttribute(AC::COL_DEI_APV, null);
        });
    }

    protected function enforcePseudoBoolean(string $column): void
    {
        $v = (int) ($this->getAttribute($column) ?? 0);
        $this->setAttribute($column, ($v % 2 === 0) ? 0 : 1);
    }

    protected function normalizeWeekdays(mixed $value): ?array
    {
        if ($value === null) return null;
        $arr = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: []) : []);
        if (!is_array($arr)) return null;

        $out = [];
        foreach ($arr as $v) {
            if (!is_scalar($v)) continue;
            $e = Weekday::normalize((string) $v);
            if ($e) $out[] = $e->value;
        }
        $out = array_values(array_unique($out));
        return $out ?: null;
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, AC::COL_APLN_ID, 'id');
    }

    public function applications(): BelongsTo // * legacy, DO NOT use
    {
        return $this->application();
    }

    public function job(): ?BelongsTo
    {
        $jobApplication = $this->application;
        if ($jobApplication)
            return $this->belongsTo(Job::class, 'id', 'job')->where('id', $jobApplication->job);
        return null;
    }

    public function contract(): ?BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract', 'id');
    }

    public function trainer(): ?BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer', 'id');
    }

// Legacy UI maps (kept for backward compatibility; prefer enums in new code paths).

    /**
     * Maps to Frequency enum cases
     * @see App\Enums\Frequency
     */
    public static array $status = [
        '' => 'Select Status',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'confirm' => 'Confirmed', // Legacy alias
        'declined' => 'Declined',
        'cancelled' => 'Cancelled',
        'cancel' => 'Cancelled', // Legacy alias
        'expired' => 'Expired',
        'failed' => 'Failed',
        'on_hold' => 'On Hold',
        'rescheduled' => 'Rescheduled',
        'tentative' => 'Tentative',
    ];

    /**
     * Maps to WorkShift enum cases
     * @see App\Enums\WorkShift
     */
    public static array $job_type = [
        '' => 'Select Job Type',
        'full_time' => 'Full Time',
        'full time' => 'Full Time', // Legacy space separator
        'part_time' => 'Part Time',
        'part time' => 'Part Time', // Legacy space separator
        'mixed' => 'Mixed',
        'rotating' => 'Rotating',
        'night' => 'Night',
        'evening' => 'Evening',
        'day' => 'Day',
        'split' => 'Split',
        'on_call' => 'On Call',
        'flexible' => 'Flexible',
        'compressed' => 'Compressed',
        'seasonal' => 'Seasonal',
        'internship' => 'Internship',
        'freelance' => 'Freelance',
        'temporary' => 'Temporary',
        'contract' => 'Contract',
        'casual' => 'Casual',
        'zero_hours' => 'Zero Hours',
        'project_based' => 'Project Based',
        'shift_work' => 'Shift Work',
        'fixed_shift' => 'Fixed Shift',
        'swing_shift' => 'Swing Shift',
        'graveyard' => 'Graveyard',
        'afternoon' => 'Afternoon',
        'morning' => 'Morning',
        '24_7' => '24/7',
        '4_day_week' => '4 Day Week',
        '9_day_fortnight' => '9 Day Fortnight',
        'reduced_hours' => 'Reduced Hours',
        'job_share' => 'Job Share',
        'term_time' => 'Term Time',
        'annualized' => 'Annualized',
        'bank_hours' => 'Bank Hours',
        'on_demand' => 'On Demand',
        'peak_season' => 'Peak Season',
    ];

    /**
     * Maps to Frequency enum cases
     * @see App\Enums\Frequency
     */
    public static array $salary_duration = [
        '' => 'Select Salary Duration',
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
        'biweekly' => 'Biweekly',
        'semimonthly' => 'Semimonthly',
        'semestral' => 'Semestral',
        'annual' => 'Annual',
        'once' => 'Once',
        'variable' => 'Variable',
        'hourly' => 'Hourly',
    ];

    /**
     * Convert legacy status value to enum
     */
    public static function legacyStatusToEnum(string $legacy): ?string
    {
        return match ($legacy) {
            'confirm' => 'confirmed',
            'cancel' => 'cancelled',
            default => $legacy
        };
    }

    /**
     * Convert legacy job type to WorkShift enum value
     */
    public static function legacyJobTypeToEnum(string $legacy): ?string
    {
        return str_replace(' ', '_', strtolower($legacy));
    }

    /**
     * Get all status options for select dropdown (enum-based)
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        $options = ['' => 'Select Status'];

        foreach (Confirmation::cases() as $case) {
            $options[$case->value] = ucwords(str_replace('_', ' ', $case->value));
        }

        return $options;
    }

    /**
     * Get all job type options for select dropdown (enum-based)
     * @return array<string, string>
     */
    public static function getJobTypeOptions(): array
    {
        $options = ['' => 'Select Job Type'];

        foreach (WorkShift::cases() as $case) {
            $options[$case->value] = ucwords(str_replace('_', ' ', $case->value));
        }

        return $options;
    }

    /**
     * Get all salary duration options for select dropdown (enum-based)
     * @return array<string, string>
     */
    public static function getSalaryDurationOptions(): array
    {
        $options = ['' => 'Select Salary Duration'];

        foreach (Frequency::cases() as $case) {
            $options[$case->value] = ucwords(str_replace('_', ' ', $case->value));
        }

        return $options;
    }
}
