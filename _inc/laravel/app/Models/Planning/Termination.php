<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
/**
 * @property int|null $employee_id
 * @property \Illuminate\Support\Carbon|string|null $notice_date
 * @property \Illuminate\Support\Carbon|string|null $termination_date
 * @property string|null $termination_type
 */

class Termination extends Model
{
    use UsesUuids, HasAuditFields, DefinesDates;

    protected $table = DC::TABLE_TERMINATIONS;
    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_TERMINATION_NDT,
        UC::COL_TERMINATION_DT,
        UC::COL_TERMINATION_TP,
        'description',
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $with = [
        'employee',
        'terminationType',
    ];
    protected $casts = [
        UC::COL_TERMINATION_NDT => 'date',
        UC::COL_TERMINATION_DT  => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }

    public function terminationType(): BelongsTo
    {
        return $this->belongsTo(TerminationType::class, UC::COL_TERMINATION_TP, 'id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\TerminationType, $this> */
    public function termination_type(): BelongsTo
    {
        return $this->terminationType();
    }
}
