<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|int $id
 * @property int|null $employee_id
 * @property \Illuminate\Support\Carbon|string|null $notice_date
 * @property \Illuminate\Support\Carbon|string|null $resignation_date
 */
class Resignation extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, DefinesDates;
    protected $table = DC::TABLE_RSG;
    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_RESIGNATION_NDT,
        UC::COL_RESIGNATION_DT,
        'description',
        'notes'
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $with = ['employee'];
    protected $casts = [
        UC::COL_RESIGNATION_NDT => 'date',
        UC::COL_RESIGNATION_DT  => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID);
    }
}
