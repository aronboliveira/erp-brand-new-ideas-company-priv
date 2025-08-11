<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Transfer extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'employee_id', 'branch_id', 'department_id',
        'transfer_date', 'description', 'created_by'
    ];

    private const FK_EMPLOYEE  = 'employee_id';
    private const FK_BRANCH    = 'branch_id';
    private const FK_DEPARTMENT = 'department_id';
    private const FK_CREATED_BY = 'created_by';

    public function department(): HasOne
    {
        return $this->hasOne(
            Department::class,
            'id',
            self::FK_DEPARTMENT
        );
    }

    public function branch(): HasOne
    {
        return $this->hasOne(
            Branch::class,
            'id',
            self::FK_BRANCH
        );
    }

    public function employee(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            self::FK_EMPLOYEE
        );
    }
}
