<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{DeductionType, LoanType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};

class Loan extends Model
{
    use HasFactory, UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_LN;

    /** @var array<string,string> */
    public static array $loanTypes = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];

    protected $fillable = [
        UC::COL_EMP_ID,
        BC::COL_LN_OPT,
        'title',
        'amount',
        PJC::COL_S_DT,
        PJC::COL_E_DT,
        'reason',
        'type',
        BC::COL_DD_TYPE,
        'installments',
    ];

    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        PJC::COL_S_DT   => 'date',
        PJC::COL_E_DT   => 'date',
        UC::COL_EMP_ID  => 'string',
        BC::COL_LN_OPT  => 'string',
        'installments'  => 'integer',
        BC::COL_DD_TYPE => DeductionType::class,
        'type'          => LoanType::class,
    ];

    protected $with = [
        'employee',
        'loanOption',
    ];

    protected $appends = [
        'is_percentage',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (Loan $loan): void {
            if ($loan->{BC::COL_DD_TYPE} !== null) {
                $normalized = DeductionType::normalize($loan->{BC::COL_DD_TYPE});
                $loan->{BC::COL_DD_TYPE} = $normalized;
            }

            if ($loan->type !== null) {
                $normalizedType = LoanType::normalize($loan->type);
                $loan->type = $normalizedType;
            }

            if ($loan->{PJC::COL_S_DT} && $loan->{PJC::COL_E_DT} && $loan->{PJC::COL_E_DT} < $loan->{PJC::COL_S_DT})
                $loan->{PJC::COL_E_DT} = $loan->{PJC::COL_S_DT};

            if ($loan->amount < 0)
                $loan->amount = 0;

            if ($loan->installments !== null && $loan->installments < 1)
                $loan->installments = 1;

            if ($loan->{BC::COL_LN_OPT}) {
                /** @var LoanOption|null $option */
                $option = LoanOption::query()->find($loan->{BC::COL_LN_OPT});

                if ($option) {
                    $minAmount = $option->{BC::COL_EXP_BDG} ?? 0.0;
                    $maxAmount = $option->{BC::COL_MAX_BDG} ?? null;

                    if ($loan->amount < $minAmount)
                        $loan->amount = $minAmount;

                    if ($maxAmount !== null && $loan->amount > $maxAmount)
                        $loan->amount = $maxAmount;

                    if ($loan->installments !== null) {
                        $minInst = $option->{BC::COL_MIN_ITM} ?? null;
                        $maxInst = $option->{BC::COL_MAX_ITM} ?? null;

                        if ($minInst !== null && $loan->installments < $minInst)
                            $loan->installments = $minInst;

                        if ($maxInst !== null && $loan->installments > $maxInst)
                            $loan->installments = $maxInst;
                    }
                }
            }
        });
    }

    public function getIsPercentageAttribute(): bool
    {
        return $this->type === LoanType::Percentage;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', UC::COL_EMP_ID);
    }

    public function loanOption(): HasOne
    {
        return $this->hasOne(LoanOption::class, 'id', BC::COL_LN_OPT);
    }
}
