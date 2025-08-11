<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class Loan extends Model
{
    use UsesUuids;

    private const FILLABLE = [
        'employee_id', 'loan_option', 'title', 'amount', 'start_date', 'end_date', 'reason', 'created_by'
    ];
    protected $fillable = self::FILLABLE;

    public static $loanTypes = [ // ! CHANGED
        'fixed'     => 'Fixed',
        'percentage' => 'Percentage',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id'); // * consider belongsTo(Employee::class,'employee_id','id')
    }

    public function loanOption(): HasOne
    {
        return $this->hasOne(LoanOption::class, 'id', 'loan_option'); // * consider belongsTo(LoanOption::class,'loan_option','id')
    }
}
