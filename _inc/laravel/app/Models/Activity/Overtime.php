<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Overtime extends Model
{
    use HasFactory, UsesUuids;
    protected $fillable = [
        'employee_id', 'title', 'number_of_days', 'hours', 'rate',
        'type', 'created_by'
    ];
    private const FK_EMPLOYEE    = 'employee_id';
    private const FK_CREATED_BY  = 'created_by';
    private const LOCAL_KEY      = 'id';
    private const MODEL_EMPLOYEE = Employee::class;
    public function employee(): HasOne
    {
        return $this->hasOne(
            self::MODEL_EMPLOYEE,
            self::LOCAL_KEY,
            self::FK_EMPLOYEE
        );
    }
}
