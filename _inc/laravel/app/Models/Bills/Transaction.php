<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};

class Transaction extends Model
{
    use UsesUuids;

    private const COL_ACCOUNT     = 'account';
    private const COL_TYPE        = 'type';
    private const COL_AMOUNT      = 'amount';
    private const COL_DESCRIPTION = 'description';
    private const COL_DATE        = 'date';
    private const COL_CREATED_BY  = 'created_by';
    private const COL_CUSTOMER_ID = 'customer_id';
    private const COL_PAYMENT_ID  = 'payment_id';
    private const COL_USER_ID     = 'user_id';
    private const COL_USER_TYPE   = 'user_type';
    private const COL_CATEGORY    = 'category';
    private const FILLABLE        = [
        self::COL_USER_ID,
        self::COL_USER_TYPE,
        self::COL_ACCOUNT,
        self::COL_TYPE,
        self::COL_AMOUNT,
        self::COL_DESCRIPTION,
        self::COL_DATE,
        self::COL_CREATED_BY,
        self::COL_CUSTOMER_ID,
        self::COL_PAYMENT_ID,
        self::COL_CATEGORY,
    ];

    private const EDITABLE_FIELDS = [
        self::COL_ACCOUNT,
        self::COL_AMOUNT,
        self::COL_DESCRIPTION,
        self::COL_DATE,
        self::COL_CATEGORY,
    ];

    protected $fillable = self::FILLABLE;

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'id', self::COL_ACCOUNT);
        // * consider belongsTo(BankAccount::class, self::COL_ACCOUNT, 'id')
    }

    public function payment(): HasOne
    {
        return $this->hasOne(InvoicePayment::class, 'id', self::COL_PAYMENT_ID);
        // * consider polymorphic relation with InvoicePayment vs. BillPayment
    }

    public function billPayment(): HasOne
    {
        return $this->hasOne(BillPayment::class, 'id', self::COL_PAYMENT_ID);
        // * consider polymorphic as above
    }

    public static function addTransaction(Request|Model $source): void // ! CHANGED
    {
        DB::transaction(function () use ($source) {
            try {
                $trx = new self();
                foreach (self::FILLABLE as $field) {
                    $value = $source instanceof Request
                        ? ($source->input($field) ?? null)
                        : ($source->{$field}        ?? null);
                    $trx->{$field} = $value;
                }
                $trx->save();
            } catch (\Throwable $e) {
                Log::error(static::class . '::' . __FUNCTION__ . " failed to add transaction: {$e->getMessage()}");
            }
        });
    }

    public static function editTransaction(Request|Model $source): void
    {
        DB::transaction(function () use ($source) {
            try {
                $paymentId = $source instanceof Request
                    ? $source->input(self::COL_PAYMENT_ID)
                    : ($source->{self::COL_PAYMENT_ID} ?? null);
                $type = $source instanceof Request
                    ? $source->input(self::COL_TYPE)
                    : ($source->{self::COL_TYPE} ?? null);
                $trx = self::where(self::COL_PAYMENT_ID, $paymentId)
                    ->where(self::COL_TYPE, $type)
                    ->first();
                if (!$trx) return;
                foreach (self::EDITABLE_FIELDS as $field)
                    $trx->{$field} = $source instanceof Request
                        ? ($source->input($field) ?? $trx->{$field})
                        : ($source->{$field}        ?? $trx->{$field});
                $trx->save();
            } catch (\Throwable $e) {
                Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed to edit transaction: {$e->getMessage()}");
            }
        });
    }

    public static function destroyTransaction(string $paymentId, string $type, string $userType): void
    {
        try {
            self::where(self::COL_PAYMENT_ID, $paymentId)
                ->where(self::COL_TYPE, $type)
                ->where(self::COL_USER_TYPE, $userType)
                ->delete();
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed to destroy transaction: {$e->getMessage()}");
        }
    }

    public static function accounts(string $account): string
    {
        $names = '';
        foreach (explode(',', $account) as $acctId) {
            if (!$acctId) continue;
            $acct = BankAccount::find($acctId);
            $names = ($acct?->bank_name ?? '') . '  ' . ($acct?->holder_name ?? '');
        }
        return $names;
    }
}
