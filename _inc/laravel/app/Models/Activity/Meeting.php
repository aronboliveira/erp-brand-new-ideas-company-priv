<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use UsesUuids;

    public const TABLE = DC::TABLE_MEETINGS;

    protected $table = self::TABLE;

    protected $fillable = [
        'code',
        'employee_id',
        CC::COL_BRC_ID,
        CC::COL_DEP_ID,
        'title',
        'date',
        'time',
        PJC::COL_MIN_DR,
        PJC::COL_EXP_DR,
        PJC::COL_MAX_DR,
        'url',
        'note',
        'attachments',
        'invited',
        'conditions',
        'reminders',
        'tags',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'date'                => 'date',
        'time'                => 'string',
        PJC::COL_MIN_DR       => 'integer',
        PJC::COL_EXP_DR       => 'integer',
        PJC::COL_MAX_DR       => 'integer',
        'attachments'         => 'array',
        'invited'             => 'array',
        'conditions'          => 'array',
        'reminders'           => 'array',
        'tags'                => 'array',
    ];

    protected $with = [
        'employee',
        'branch',
        'department',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                if (empty($m->code))
                    do $m->code = (string) Str::uuid();
                    while (self::where('code', $m->code)->exists());

                foreach (['title', 'url', 'note'] as $field)
                    if (isset($m->{$field}) && is_string($m->{$field}))
                        $m->{$field} = trim($m->{$field});

                // Data e hora
                $today = today();

                if ($m->date) {
                    try {
                        $date = Carbon::parse($m->date);
                    } catch (\Throwable) {
                        $date = $today->copy();
                    }
                } else $date = $today->copy();

                $m->date = $date->toDateString();

                if ($m->time && is_string($m->time)) {
                    $time = trim($m->time);
                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time))
                        $time = '09:00:00';
                    elseif (strlen($time) === 5)
                        $time .= ':00';
                    $m->time = $time;
                } else $m->time = '09:00:00';

                $min = (int) ($m->{PJC::COL_MIN_DR} ?? 15);
                $exp = (int) ($m->{PJC::COL_EXP_DR} ?? 30);
                $max = (int) ($m->{PJC::COL_MAX_DR} ?? 60);

                if ($min <= 0)  $min = 1;
                if ($exp <= 0)  $exp = $min;
                if ($max <= 0)  $max = max($exp, $min);

                if ($exp < $min) $exp = $min;
                if ($max < $exp) $max = $exp;

                $min = max(1, min($min, 8 * 60));
                $exp = max($min, min($exp, 8 * 60));
                $max = max($exp, min($max, 12 * 60));

                $m->{PJC::COL_MIN_DR} = $min;
                $m->{PJC::COL_EXP_DR} = $exp;
                $m->{PJC::COL_MAX_DR} = $max;

                $m->attachments = self::normalizeArrayField($m->attachments ?? null);
                $m->invited     = self::normalizeArrayField($m->invited ?? null);
                $m->conditions  = self::normalizeArrayField($m->conditions ?? null);
                $m->reminders   = self::normalizeArrayField($m->reminders ?? null);
                $m->tags        = self::normalizeArrayField($m->tags ?? null);
            } catch (\Throwable $e) {
                Log::warning(self::class . '::saving normalization failed', [
                    'id'    => $m->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, CC::COL_BRC_ID, 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, CC::COL_DEP_ID, 'id');
    }
}
