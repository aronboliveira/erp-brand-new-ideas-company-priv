<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use UsesUuids;

    private const FILLABLE = [
        'title', 'start_date', 'end_date', 'branch_id', 'department_id',
        'employee_id', 'description', 'created_by'
    ];
    protected $fillable = self::FILLABLE;

    // * consider adding: belongsTo(Branch::class,'branch_id','id')
    // * consider adding: belongsTo(Department::class,'department_id','id')
    // * consider adding: belongsTo(Employee::class,'employee_id','id')
}
