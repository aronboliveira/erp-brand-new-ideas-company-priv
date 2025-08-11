<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Allowance extends Model
{
    use HasFactory, UsesUuids;

    protected $fillable = [
        'employee_id', 'allowance_option', 'title',
        'amount', 'type', 'created_by'
    ];

    /** @var array<string,string> */
    public static array $allowanceType = [ // ! CHANGED
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    private const FK_EMPLOYEE       = 'employee_id';
    private const FK_ALLOWANCE_OPT  = 'allowance_option';

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            self::FK_EMPLOYEE
        );
    }

    public function allowanceOption(): HasOne
    {
        return $this->hasOne(
            AllowanceOption::class,
            'id',
            self::FK_ALLOWANCE_OPT
        );
    }
}
