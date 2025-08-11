<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Commission extends Model
{
    use UsesUuids;

    protected $fillable = ['employee_id', 'title', 'amount', 'type', 'created_by'];
    public static $commissionType = [ // ! CHANGED
        'fixed'     => 'Fixed',
        'percentage' => 'Percentage',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }
}
