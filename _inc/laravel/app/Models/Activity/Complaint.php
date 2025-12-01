<?php

namespace App\Models;

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Models\Employee;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Complaint extends Model
{
    use HasAuditFields, UsesUuids;
    protected $table = DC::TABLE_CPT;
    protected $fillable = [
        CC::COL_CPT_AGST,
        CC::COL_CPT_DT,
        CC::COL_CPT_FRM,
        UC::COL_EMP_ID,
        'description',
        'notes',
        'title',
        'reason'
    ];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $casts = [
        CC::COL_CPT_DT => 'date',
    ];
    protected $with = [
        'employee',
    ];

    public function complaintAgainst(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', CC::COL_CPT_AGST);
    }

    public function complaintFrom(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', CC::COL_CPT_FRM);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
    }
}
