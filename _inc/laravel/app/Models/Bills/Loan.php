<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC,
    UsersConstants as UC
};
use App\Enums\{DeductionType, PaymentPatternType};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasOne
};
use Illuminate\Support\Facades\{DB, Schema};

/**
 * @property float|int|string|null $amount
 * @property string|null $deduction_type
 * @property string|\Illuminate\Support\Carbon|null $end_date
 * @property float|int|string|null $installments
 * @property string|null $loan_option
 * @property string|\Illuminate\Support\Carbon|null $start_date
 * @property string|null $type
 */
class Loan extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, DefinesDates;

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
        BC::COL_IS_PAY_RL_DDT,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        PJC::COL_S_DT   => 'date',
        PJC::COL_E_DT   => 'date',
        UC::COL_EMP_ID  => 'string',
        BC::COL_LN_OPT  => 'string',
        'installments'  => 'integer',
        BC::COL_DD_TYPE => DeductionType::class,
        'type'          => PaymentPatternType::class,
        BC::COL_IS_PAY_RL_DDT => 'boolean',
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
            if (Schema::hasColumn(DC::TABLE_LOAN_OPTS, BC::COL_ALW_PAY_RL_DDT)) {
                if ($loan->getAttribute(BC::COL_LN_OPT) && DB::table(DC::TABLE_LOAN_OPTS)->where('id', $loan->getAttribute(BC::COL_LN_OPT))->exists()) {
                    /** @var LoanOption|null $option */
                    $option = LoanOption::query()->find($loan->getAttribute(BC::COL_LN_OPT));
                    $option && $option->getAttribute(BC::COL_ALW_PAY_RL_DDT) === false &&
                        $loan->setAttribute(BC::COL_IS_PAY_RL_DDT, false);
                }
            }
            if ($loan->{BC::COL_DD_TYPE} !== null) {
                $normalized = DeductionType::normalize($loan->{BC::COL_DD_TYPE});
                $loan->{BC::COL_DD_TYPE} = $normalized?->value;
            }
            if ($loan->type !== null) {
                $normalizedType = PaymentPatternType::normalize($loan->type);
                $loan->type = $normalizedType?->value;
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
        return $this->type === PaymentPatternType::Percentage;
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
