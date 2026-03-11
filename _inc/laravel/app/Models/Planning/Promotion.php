<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property mixed $created_by
 * @property int|null $designation_id
 * @property int|null $employee_id
 * @property \Illuminate\Support\Carbon|string|null $promotion_date
 * @property string|null $promotion_title
 */

class Promotion extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields, DefinesDates;

    protected $table = DC::TABLE_PRMT;
    protected $fillable = [
        UC::COL_EMP_ID,
        UC::COL_DSG_ID,
        UC::COL_PRMT_TL,
        UC::COL_PRMT_DT,
        'description'
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $with = [
        'employee',
        'designation',
    ];
    protected $casts = [
        UC::COL_PRMT_DT => 'date',
    ];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, UC::COL_DSG_ID, 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, UC::COL_EMP_ID, 'id');
    }
}
