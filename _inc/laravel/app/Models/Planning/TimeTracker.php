<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    SettingsConstants as SC,
    UsersConstants as UC
};
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TimeTracker extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_TM_TRK;

    protected $fillable = [
        // Core
        PJC::COL_NM,                  // name
        'description',

        // Links
        PJC::COL_PJ_ID,               // project_id
        AC::COL_TSK_ID,               // task_id
        UC::COL_USER_ID,              // user_id
        CC::COL_DEP_ID,               // department_id

        // Tags
        PJC::COL_TAG_ID,              // tag_id (legacy / not clear yet)
        'tags',                       // json
        'attachments',                // json

        // Billing
        PJC::COL_IS_BLB,              // is_billable
        PJC::COL_BLB_HRS,             // billable_hours
        PJC::COL_HRS_WTT_TIMER,       // hours_without_timer
        PJC::COL_HR_PRC,              // hourly_price
        'currency',

        // Time
        AC::COL_ST_TIME,              // start_time
        AC::COL_E_TIME,               // end_time
        AC::COL_TTL_TIME,             // total_time (legacy string)
        AC::COL_IA,                   // is_active (legacy string)
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        PJC::COL_IS_BLB        => 'boolean',
        PJC::COL_BLB_HRS       => 'decimal:2',
        PJC::COL_HRS_WTT_TIMER => 'decimal:2',
        PJC::COL_HR_PRC        => 'decimal:2',

        AC::COL_ST_TIME        => 'datetime',
        AC::COL_E_TIME         => 'datetime',

        'tags'                 => 'array',
        'attachments'          => 'array',

        AC::COL_IA             => 'boolean',
    ];

    protected $appends = [
        AC::COL_PJ_NM, // project_name
        'project_task',
        'total',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            if (!$m->getAttribute(PJC::COL_IS_BLB))
                $m->setAttribute(PJC::COL_HR_PRC, null);
            $hourly = $m->getAttribute(PJC::COL_HR_PRC);
            if ($hourly !== null && (float)$hourly > 0 && blank($m->getAttribute('currency')))
                $m->setAttribute('currency', SC::DEF_SITE_CURRENCY_ID);
            $diffSeconds = self::diffSeconds($m->getAttribute(AC::COL_ST_TIME), $m->getAttribute(AC::COL_E_TIME));
            if ($diffSeconds !== null) {
                $ttlSeconds = self::totalTimeToSeconds($m->getAttribute(AC::COL_TTL_TIME));
                if ($ttlSeconds === null || $diffSeconds > $ttlSeconds) {
                    $m->setAttribute(AC::COL_TTL_TIME, self::secondsToTotalTime($diffSeconds, $m->getAttribute(AC::COL_TTL_TIME)));
                    $ttlSeconds = $diffSeconds;
                }
                if ($m->getAttribute(PJC::COL_HRS_WTT_TIMER) !== null && $ttlSeconds !== null) {
                    $maxHours = round($ttlSeconds / 3600, 2);
                    if ((float)$m->getAttribute(PJC::COL_HRS_WTT_TIMER) > $maxHours)
                        $m->setAttribute(PJC::COL_HRS_WTT_TIMER, $maxHours);
                }
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, PJC::COL_PJ_ID);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, AC::COL_TSK_ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CC::COL_DEP_ID);
    }

    protected function projectName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $pid = $this->{PJC::COL_PJ_ID};
                if (blank($pid)) return '';
                $name = Project::query()
                    ->whereKey($pid)
                    ->value(AC::COL_PJ_NM);
                return (string)($name ?? '');
            }
        );
    }

    protected function projectTask(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $tid = $this->{AC::COL_TSK_ID};
                if (blank($tid)) return '';
                $name = ProjectTask::query()
                    ->whereKey($tid)
                    ->value(PJC::COL_NM);
                return (string)($name ?? '');
            }
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $raw = (string)($this->{AC::COL_TTL_TIME} ?? '0');
                if (preg_match('/^\d{1,3}:\d{2}:\d{2}$/', $raw))
                    return $raw;
                if (preg_match('/^\d+$/', $raw))
                    return gmdate('H:i:s', (int)$raw);
                return '00:00:00';
            }
        );
    }

    private static function diffSeconds(mixed $start, mixed $end): ?int
    {
        if (!($start instanceof Carbon) || !($end instanceof Carbon))
            return null;
        $diff = $start->diffInSeconds($end, false);
        return $diff < 0 ? 0 : $diff;
    }

    private static function totalTimeToSeconds(mixed $ttl): ?int
    {
        if ($ttl === null) return null;
        $ttl = (string)$ttl;
        if (preg_match('/^\d+$/', $ttl))
            return (int)$ttl;
        if (preg_match('/^(\d{1,3}):([0-5]\d):([0-5]\d)$/', $ttl, $m)) {
            $h = (int)$m[1];
            $i = (int)$m[2];
            $s = (int)$m[3];
            return ($h * 3600) + ($i * 60) + $s;
        }

        return null;
    }

    private static function secondsToTotalTime(int $seconds, mixed $currentTtl): string
    {
        $currentTtl = $currentTtl === null ? null : (string)$currentTtl;
        if ($currentTtl !== null && preg_match('/^\d+$/', $currentTtl))
            return (string)$seconds;
        return gmdate('H:i:s', max(0, $seconds));
    }
}
