<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, ProjectsConstants as PJC, DatabaseConstants as DC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealTask extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = [
        AC::COL_DL,
        PJC::COL_NM,
        AC::COL_TSK_DATE,
        AC::COL_TSK_TIME,
        PJC::COL_PRT,
        AC::COL_TSK_STT,
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $with = ['deal'];

    public static $priorities = [
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
    ];

    public static $status = [
        0 => 'On Going',
        1 => 'Completed',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, AC::COL_DL, 'id');
    }

    public function status(): string
    {
        return match ($this->{AC::COL_TSK_STT}) {
            0 => 'On Going',
            1 => 'Completed',
            default => 'Unknown',
        };
    }

    public function priority(): string
    {
        return match ($this->{PJC::COL_PRT}) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            default => 'Unknown',
        };
    }
}
