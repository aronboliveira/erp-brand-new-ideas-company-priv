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

class Leave extends Model
{
    use HasFactory;
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    public const TABLE = DC::TABLE_LV;

    protected $table = self::TABLE;

    protected $fillable = [
        'employee_id',
        CC::COL_LV_TP_ID,
        PJC::COL_APL_ON,
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        PJC::COL_TT_LV_DY,
        PJC::COL_LV_RS,
        'remark',
        PJC::COL_STATUS,
        'discount',
        'attachments',
        'conditions',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        PJC::COL_APL_ON   => 'date',
        PJC::COL_S_DT     => 'date',
        PJC::COL_E_DT     => 'date',
        PJC::COL_TT_LV_DY => 'string',
        'discount'        => 'integer',
        'attachments'     => 'array',
        'conditions'      => 'array',
    ];

    protected $with = [
        'employee',
        'leaveType',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                $today = today();

                // Normalização básica de textos
                if (isset($m->{PJC::COL_LV_RS}) && is_string($m->{PJC::COL_LV_RS}))
                    $m->{PJC::COL_LV_RS} = trim($m->{PJC::COL_LV_RS});

                if (isset($m->remark) && is_string($m->remark))
                    $m->remark = trim($m->remark);

                // Datas: applied_on, start_date, end_date
                $appliedOn = $m->{PJC::COL_APL_ON}
                    ? Carbon::parse($m->{PJC::COL_APL_ON})
                    : (clone $today);

                if ($appliedOn->lt($today))
                    $appliedOn = (clone $today);

                $m->{PJC::COL_APL_ON} = $appliedOn->toDateString();

                $start = $m->{PJC::COL_S_DT}
                    ? Carbon::parse($m->{PJC::COL_S_DT})
                    : (clone $today);

                if ($start->lt($today))
                    $start = (clone $today);

                $end = $m->{PJC::COL_E_DT}
                    ? Carbon::parse($m->{PJC::COL_E_DT})
                    : $start->copy()->addDay();

                if ($end->lte($start))
                    $end = $start->copy()->addDay();

                $leaveType   = null;
                $allowedDays = null;
                $minPct      = 0;
                $maxPct      = 100;

                if (!empty($m->{CC::COL_LV_TP_ID}))
                    $leaveType = $m->relationLoaded('leaveType')
                        ? $m->leaveType
                        : LeaveType::find($m->{CC::COL_LV_TP_ID});

                if ($leaveType) {
                    $baseDays = max(0, (int) ($leaveType->days ?? 0));
                    $extDays  = max(0, (int) ($leaveType->{PJC::COL_EXT_DY} ?? 0));
                    $allowedDays = $baseDays + $extDays;
                    if ($allowedDays <= 0)
                        $allowedDays = 1;

                    $minPct = (int) ($leaveType->{PJC::COL_SL_MIN_DD_PCT} ?? 0);
                    $maxPct = (int) ($leaveType->{PJC::COL_SL_MAX_DD_PCT} ?? 100);

                    if ($minPct < 0)   $minPct = 0;
                    if ($maxPct > 100) $maxPct = 100;
                    if ($minPct > $maxPct)
                        $minPct = $maxPct;
                }

                $totalDays = $start->diffInDays($end) + 1;

                if ($allowedDays !== null && $totalDays > $allowedDays) {
                    $end = $start->copy()->addDays($allowedDays - 1);
                    $totalDays = $allowedDays;
                }

                $m->{PJC::COL_S_DT} = $start->toDateString();
                $m->{PJC::COL_E_DT} = $end->toDateString();

                $m->{PJC::COL_TT_LV_DY} = (string) max(1, $totalDays);

                $discount = (int) ($m->discount ?? 0);
                if ($discount < 0)   $discount = 0;
                if ($discount > 100) $discount = 100;

                if ($leaveType) {
                    if ($discount < $minPct) $discount = $minPct;
                    if ($discount > $maxPct) $discount = $maxPct;
                }

                $m->discount = $discount;

                $m->attachments = self::normalizeArrayField($m->attachments ?? null);
                $m->conditions  = self::normalizeArrayField($m->conditions ?? null);

                $status = (string) ($m->{PJC::COL_STATUS} ?? '');
                $status = strtolower(trim($status));

                $validStatuses = PJC::$projectStatus ?? [];
                $validKeys     = is_array($validStatuses) ? array_keys($validStatuses) : [];

                if ($validKeys && !in_array($status, $validKeys, true))
                    $status = PJC::STT_INP_K;
                else if ($status === '')
                    $status = PJC::STT_INP_K;

                $m->{PJC::COL_STATUS} = $status;
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

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, CC::COL_LV_TP_ID, 'id');
    }
}
