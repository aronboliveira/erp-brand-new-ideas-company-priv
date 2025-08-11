<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class SaturationDeduction extends Model
{
    use UsesUuids;

    private const FILLABLE = [
        'employee_id', 'deduction_option', 'title', 'amount', 'created_by'
    ];
    protected $fillable = self::FILLABLE;

    public static $saturationDeductionType = [
        'fixed'     => 'Fixed',
        'percentage' => 'Percentage',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
        // * consider belongsTo(Employee::class,'employee_id','id')
    }

    public function deductionOption(): HasOne
    {
        return $this->hasOne(DeductionOption::class, 'id', 'deduction_option');
        // * consider belongsTo(DeductionOption::class,'deduction_option','id')
    }
}
