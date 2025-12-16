<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\Log;

class PosPayment extends Model
{
    use UsesUuids;

    protected $table = 'pos_payments';

    public $timestamps = false;

    protected $fillable = [
        BC::COL_POS_ID,     // 'pos_id'
        'payment',          // FK para payments.id
        BC::COL_BACC_ID,    // 'bank_account_id'
        'date',
        'amount',
        'discount',
        BC::COL_DSC_AMT,    // 'discount_amount'
    ];

    protected $guarded = [];

    protected $with = [
        'pos',
        'payment',
        'bankAccount',
    ];

    protected $casts = [
        'date'               => 'date',
        'amount'             => 'decimal:2',
        'discount'           => 'decimal:2',
        BC::COL_DSC_AMT      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach (['amount', 'discount', BC::COL_DSC_AMT] as $field) {
                if ($m->{$field} !== null) {
                    $val = (float) $m->{$field};
                    if ($val < 0.0)
                        $val = 0.0;
                    $m->{$field} = $val;
                }
            }

            if (!empty($m->payment)) {
                try {
                    /** @var \App\Models\Payment|null $payment */
                    $payment = $m->relationLoaded('payment')
                        ? $m->getRelation('payment')
                        : Payment::find($m->payment);
                    if ($payment) {
                        if ($m->date !== null)
                            $payment->date = $m->date;
                        if ($m->{BC::COL_BACC_ID} !== null)
                            $payment->{BC::COL_BACC_ID} = $m->{BC::COL_BACC_ID};
                        if ($m->discount !== null)
                            $payment->discount = $m->discount;
                        if ($m->amount !== null)
                            $payment->amount = $m->amount;
                        $payment->save();
                    }
                } catch (\Throwable $e) {
                    Log::error(
                        static::class . '::saving failed to sync Payment: ' . $e->getMessage(),
                        ['pos_payment_id' => $m->id ?? null]
                    );
                }
            }
        });
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(
            Pos::class,
            BC::COL_POS_ID,
            'id'
        );
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            Payment::class,
            'payment',
            'id'
        );
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_BACC_ID,
            'id'
        );
    }
}
