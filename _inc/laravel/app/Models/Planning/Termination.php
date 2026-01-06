<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Termination extends Model
{
    use  UsesUuids, HasAuditFields, DefinesDates;

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

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
        // * consider belongsTo(Employee::class,self::COL_EMPLOYEE_ID,'id')
    }

    public function terminationType(): HasOne
    {
        return $this->hasOne(TerminationType::class, 'id', UC::COL_TERMINATION_TP);
        // * consider belongsTo(TerminationType::class,self::COL_TERMINATION_TYPE,'id')
    }
}
