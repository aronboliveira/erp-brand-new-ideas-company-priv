<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class OtherPayment extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY  = 'created_by';
    private const COL_EMPLOYEE_ID = 'employee_id';
    private const COL_TYPE        = 'type';

    protected $fillable = [
        self::COL_EMPLOYEE_ID,
        'title',
        'amount',
        self::COL_TYPE,       // * ADDED
        self::COL_CREATED_BY,
    ];

    public static $otherPaymentType = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    public function employee(): HasOne
    {
        return $this
            ->hasOne(Employee::class, 'id', self::COL_EMPLOYEE_ID);
        // * consider using belongsTo(Employee::class, self::COL_EMPLOYEE_ID)
    }
}
