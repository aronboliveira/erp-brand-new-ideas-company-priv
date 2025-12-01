<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};

class Payslip extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_PAY_SLP;

    protected $table = self::TABLE;

    protected $fillable = [
        UC::COL_EMP_ID,
        BC::COL_NET_PAYABLE,
        BC::COL_G_SLR,
        BC::COL_N_SLR,
        BC::COL_SLR_M,
        BC::COL_P_DAY,
        'status',
        'allowance',
        'commission',
        'loan',
        BC::COL_ST_DD,
        BC::COL_OT_PAY,
        'overtime',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        UC::COL_EMP_ID      => 'string',
        BC::COL_NET_PAYABLE => 'integer',
        'status'            => 'integer',
        BC::COL_G_SLR       => 'decimal:2',
        BC::COL_N_SLR       => 'decimal:2',
        BC::COL_P_DAY       => 'date',
        BC::COL_SLR_M       => 'string',
        'allowance'         => 'string',
        'commission'        => 'string',
        'loan'              => 'string',
        BC::COL_ST_DD       => 'string',
        BC::COL_OT_PAY      => 'string',
        'overtime'          => 'string',
    ];

    protected $with = [
        'employees',
        'allowanceRelation',
        'commissionRelation',
        'loanRelation',
        'saturationDeduction',
        'otherPayment',
        'overtimeRelation',
    ];

    protected $appends = [
        'effective_net_salary',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $payslip): void {
            $payslip->{BC::COL_NET_PAYABLE} ??= 0;
            if ($payslip->{BC::COL_NET_PAYABLE} < 0)
                $payslip->{BC::COL_NET_PAYABLE} = 0;

            $payslip->{BC::COL_G_SLR} ??= DC::MININUM_WAGE_BR;
            if ($payslip->{BC::COL_G_SLR} < 0)
                $payslip->{BC::COL_G_SLR} = 0;

            if ($payslip->{BC::COL_N_SLR} !== null) {
                if ($payslip->{BC::COL_N_SLR} < 0)
                    $payslip->{BC::COL_N_SLR} = 0;

                if ($payslip->{BC::COL_N_SLR} > $payslip->{BC::COL_G_SLR})
                    $payslip->{BC::COL_N_SLR} = $payslip->{BC::COL_G_SLR};
            }
            $netBase = $payslip->{BC::COL_N_SLR} ?? $payslip->{BC::COL_G_SLR};
            if ($netBase !== null && $payslip->{BC::COL_NET_PAYABLE} > $netBase)
                $payslip->{BC::COL_NET_PAYABLE} = (int) floor($netBase);
            if (isset($payslip->{BC::COL_SLR_M}) && is_string($payslip->{BC::COL_SLR_M}))
                $payslip->{BC::COL_SLR_M} = trim($payslip->{BC::COL_SLR_M});
            if ($payslip->status === null)
                $payslip->status = 0;
            elseif ($payslip->status < 0)
                $payslip->status = 0;
        });
    }

    public function getEffectiveNetSalaryAttribute(): float
    {
        $net   = $this->{BC::COL_N_SLR};
        $gross = $this->{BC::COL_G_SLR};

        return (float) ($net ?? $gross ?? 0.0);
    }

    public static function employee(string $id): ?Employee
    {
        return Employee::find($id);
    }

    public function employees(): HasOne
    {
        return $this->hasOne(
            Employee::class,
            'id',
            UC::COL_EMP_ID
        );
        // * consider using belongsTo(Employee::class, UC::COL_EMP_ID, 'id')
    }

    public function allowanceRelation(): HasOne
    {
        return $this->hasOne(
            Allowance::class,
            'id',
            'allowance'
        );
    }

    public function commissionRelation(): HasOne
    {
        return $this->hasOne(
            Commission::class,
            'id',
            'commission'
        );
    }

    public function loanRelation(): HasOne
    {
        return $this->hasOne(
            Loan::class,
            'id',
            'loan'
        );
    }

    public function saturationDeduction(): HasOne
    {
        return $this->hasOne(
            SaturationDeduction::class,
            'id',
            BC::COL_ST_DD
        );
    }

    public function otherPayment(): HasOne
    {
        return $this->hasOne(
            OtherPayment::class,
            'id',
            BC::COL_OT_PAY
        );
    }

    public function overtimeRelation(): HasOne
    {
        return $this->hasOne(
            Overtime::class,
            'id',
            'overtime'
        );
    }
}
