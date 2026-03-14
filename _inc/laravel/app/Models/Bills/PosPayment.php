<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Traits\{
    ExtendsPaymentTable,
    HasAuditFields,
    UsesUuids
};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log, Schema};

class PosPayment extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, ExtendsPaymentTable;

    protected $table = DC::TABLE_POS_PAY;

    protected $guarded = ['id'];

    protected $fillable = [
        BC::COL_POS_ID,
        'payment',
        BC::COL_BACC_ID,
        'date',
        'amount',
        'discount',
        BC::COL_DSC_AMT,
        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
        DC::COL_TABLE_CREATOR,
    ];

    protected $attributes = [
        'payment' => null,
    ];

    protected $with = [
        'pos',
        'paymentModel',
        'bankAccount',
    ];

    protected $casts = [
        'date'          => 'date',
        'amount'        => 'decimal:2',
        'discount'      => 'decimal:2',
        BC::COL_DSC_AMT => 'decimal:2',
    ];

    protected $appends = [
        'total_discount',
        'net_amount',
        'has_discount',
    ];

    protected static bool $paymentSyncInitialized = false;
    protected static ?string $paymentSyncTable = null;
    protected static array $aggregateCache = [];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            $m->sanitizeFields();
        });

        static::saved(function (self $m): void {
            $m->syncPaymentTableSafe();
        });
    }

    protected function sanitizeFields(): void
    {
        try {
            foreach (['amount', 'discount', BC::COL_DSC_AMT] as $field) {
                $raw = $this->getAttribute($field);

                if ($raw === null) {
                    if ($field === 'amount') $this->setAttribute($field, 0.0);
                    continue;
                }

                $val = (float) $raw;
                if ($val < 0.0) $val = 0.0;

                $this->setAttribute($field, $val);
            }

            $date = $this->getAttribute('date');
            if ($date === null) $this->setAttribute('date', now()->toDateString());
        } catch (\Throwable $e) {
            Log::error(static::class . '::sanitizeFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    /**
     * POS payments table is the source of truth for payment-related columns.
     * This updates overlapping compatible columns in DC::TABLE_PAY (usually: payments).
     */
    protected function syncPaymentTableSafe(): bool
    {
        try {
            $paymentId = $this->getAttribute('payment');
            if (empty($paymentId)) return false;

            try {
                if (!self::$paymentSyncInitialized)
                    $this->setupPaymentSync();

                if (empty(self::$paymentSyncTable)) return false;

                return $this->synchronizeExtendsBaseTableColumns(
                    self::$paymentSyncTable,
                    'payment',
                    $paymentId
                );
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed syncing Payment table: ' . $e->getMessage(), [
                    'pos_payment_id' => $this->getAttribute('id'),
                    'payment_id'     => $paymentId,
                    'file'           => $e->getFile(),
                    'line'           => $e->getLine(),
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error(static::class . '::syncPaymentTableSafe — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return false;
        }
    }

    protected function setupPaymentSync(): void
    {
        try {
            $paymentsTable = \defined(DC::class . '::TABLE_PAY') ? DC::TABLE_PAY : 'payments';
            if (!Schema::hasTable($paymentsTable)) {
                Log::warning(static::class . ' payment sync skipped: payments table not found', [
                    'payments_table' => $paymentsTable,
                ]);
                return;
            }
            self::$paymentSyncTable = $paymentsTable;
            $this->setupExtendsBaseTable(self::$paymentSyncTable, 'payment');
            self::$paymentSyncInitialized = true;
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed initializing payment sync: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, BC::COL_POS_ID, 'id');
    }

    /**
     * Relation name avoids collision with "payment" column.
     * Access via $m->paymentModel (NOT $m->payment).
     */
    public function paymentModel(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment', 'id');
    }

    /**
     * Compatibility method (do not rely on property access due to column name collision).
     * Use $m->paymentModel or $m->payment() when you need the relation query builder.
     */
    public function payment(): BelongsTo
    {
        return $this->paymentModel();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID, 'id');
    }

    public function getTotalDiscountAttribute(): float
    {
        try {
            $discount = (float) ($this->getAttribute('discount') ?? 0.0);
            $discountAmt = (float) ($this->getAttribute(BC::COL_DSC_AMT) ?? 0.0);
            $total = $discount + $discountAmt;

            return $total < 0.0 ? 0.0 : $total;
        } catch (\Throwable $e) {
            Log::error(static::class . '::getTotalDiscountAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return 0.0;
        }
    }

    public function getNetAmountAttribute(): float
    {
        try {
            $amount = (float) ($this->getAttribute('amount') ?? 0.0);
            $net = $amount - (float) $this->getAttribute('total_discount');

            return $net < 0.0 ? 0.0 : $net;
        } catch (\Throwable $e) {
            Log::error(static::class . '::getNetAmountAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return 0.0;
        }
    }

    public function getHasDiscountAttribute(): bool
    {
        return (float) $this->getAttribute('total_discount') > 0.0;
    }

    public function isCashLike(): bool
    {
        return $this->getAttribute(BC::COL_BACC_ID) === null;
    }

    public static function totalNetByPos(string $posId, ?string $fromDate = null, ?string $toDate = null): float
    {
        try {
            $posId = trim((string) $posId);
            if ($posId === '') return 0.0;

            $key = implode('|', [
                'netByPos',
                $posId,
                (string) $fromDate,
                (string) $toDate,
            ]);

            if (array_key_exists($key, self::$aggregateCache))
                return (float) self::$aggregateCache[$key];

            $q = DB::table(DC::TABLE_POS_PAY)
                ->where(BC::COL_POS_ID, $posId);

            if (!empty($fromDate)) $q->whereDate('date', '>=', $fromDate);
            if (!empty($toDate)) $q->whereDate('date', '<=', $toDate);

            $row = $q->selectRaw(
                'COALESCE(SUM(amount - COALESCE(discount,0) - COALESCE(' . BC::COL_DSC_AMT . ',0)), 0) AS total'
            )->first();

            $total = (float) ($row->total ?? 0.0);
            self::$aggregateCache[$key] = $total;

            return $total;
        } catch (\Throwable $e) {
            Log::error(static::class . '::totalNetByPos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return 0.0;
        }
    }
}
