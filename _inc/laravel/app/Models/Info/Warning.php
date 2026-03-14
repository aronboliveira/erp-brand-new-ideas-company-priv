<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Models\Employee;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
/**
 * @property mixed $created_by
 * @property string|null $warning_by
 * @property \Illuminate\Support\Carbon|string|null $warning_date
 * @property \Illuminate\Support\Carbon|string|null $warning_to
 * @property string|null $subject
 * @property string|null $description
 */

class Warning extends Model
{
    use HasAuditFields, UsesUuids;
    protected $table = DC::TABLE_WRN;
    protected $fillable = [
        CC::COL_WRN_TO,
        CC::COL_WRN_BY,
        CC::COL_WRN_DATE,
        'subject',
        'description',
        UC::COL_EMP_ID,
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $casts = [
        CC::COL_WRN_DATE => 'date',
    ];
    protected $with = [
        'employee'
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
    }

    public function warningTo(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', CC::COL_WRN_TO);
    }

    public function warningBy(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', CC::COL_WRN_BY);
    }

    /**
     * Dynamic warning relation: returns HasOne Employee by given column.
     */
    public function warning(string $field): HasOne
    {
        return $this->hasOne(Employee::class, 'id', $field);
    }
}
