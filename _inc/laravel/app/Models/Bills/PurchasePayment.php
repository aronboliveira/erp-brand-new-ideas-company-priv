<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsPaymentTable, HasAuditFields, UsesUuids};
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};

class PurchasePayment extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, ExtendsPaymentTable;

    protected $table = DC::TABLE_PRC_PAY;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        BC::COL_PRC_ID,
        BC::COL_PAY_ID,
        BC::COL_BACC_ID,
        'date',
        'amount',
        BC::COL_PAY_MTD,
        'reference',
        'description',
        BC::COL_ADD_RCP,

        BC::COL_NFE_KEY,
        BC::COL_NFE_NUMBER,
        BC::COL_NFE_SERIES,
        BC::COL_NFE_XML_PATH,
        BC::COL_NFE_PROTOCOL,
        BC::COL_NFE_AUTH_AT,
    ];

    protected $with = [
        'purchase',
        'payment',
        'bankAccount',
    ];

    protected $casts = [
        'date'            => 'date',
        'amount'          => 'decimal:2',
        BC::COL_PAY_MTD   => 'integer',
        BC::COL_NFE_AUTH_AT => 'datetime',
    ];

    protected $appends = [
        'has_payment_link',
        'has_nfe',
    ];

    protected static array $totalPaidCache = [];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                $m->normalizeBridgeFields();
                $m->ensureCompositeUniquenessWhenFullyLinked();
            } catch (\Throwable $e) {
                Log::warning(static::class . '::saving normalization failed: ' . $e->getMessage(), [
                    'id' => $m->getAttribute('id') ?? null,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, BC::COL_PRC_ID, 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, BC::COL_PAY_ID, 'id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID, 'id');
    }

    public function getHasPaymentLinkAttribute(): bool
    {
        $v = $this->getAttribute(BC::COL_PAY_ID);
        return is_string($v) && trim($v) !== '';
    }

    public function getHasNfeAttribute(): bool
    {
        $key = $this->getAttribute(BC::COL_NFE_KEY);
        return is_string($key) && trim($key) !== '';
    }

    public function setAmountAttribute(mixed $value): void
    {
        $val = is_numeric($value) ? (float) $value : 0.0;
        if ($val < 0.0) $val = 0.0;
        $this->attributes['amount'] = $val;
    }

    public function setReferenceAttribute(mixed $value): void
    {
        $v = is_string($value) ? trim($value) : (is_scalar($value) ? trim((string) $value) : '');
        $this->attributes['reference'] = $v === '' ? null : mb_substr($v, 0, 255);
    }

    public function setDescriptionAttribute(mixed $value): void
    {
        $v = is_string($value) ? trim($value) : (is_scalar($value) ? trim((string) $value) : '');
        $this->attributes['description'] = $v === '' ? null : $v;
    }

    public function setNfeKeyAttribute(mixed $value): void
    {
        $v = is_string($value) ? preg_replace('/\D+/', '', $value) : '';
        $v = trim((string) $v);
        $this->attributes[BC::COL_NFE_KEY] = $v === '' ? null : mb_substr($v, 0, 44);
    }

    public function setNfeAuthAtAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes[BC::COL_NFE_AUTH_AT] = null;
            return;
        }
        if ($value instanceof CarbonInterface) {
            $this->attributes[BC::COL_NFE_AUTH_AT] = $value;
            return;
        }
        try {
            $this->attributes[BC::COL_NFE_AUTH_AT] = \Carbon\Carbon::parse((string) $value);
        } catch (\Throwable) {
            $this->attributes[BC::COL_NFE_AUTH_AT] = null;
        }
    }
    public static function totalPaidForPurchase(string $purchaseId): float
    {
        $purchaseId = trim((string) $purchaseId);
        if ($purchaseId === '') return 0.0;

        if (array_key_exists($purchaseId, static::$totalPaidCache))
            return (float) static::$totalPaidCache[$purchaseId];

        try {
            $sum = (float) DB::table((new static)->getTable()) // @phpstan-ignore new.static
                ->where(BC::COL_PRC_ID, $purchaseId)
                ->sum('amount');

            static::$totalPaidCache[$purchaseId] = $sum;
            return $sum;
        } catch (\Throwable $e) {
            Log::debug(static::class . '::totalPaidForPurchase failed: ' . $e->getMessage(), [
                'purchase_id' => $purchaseId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return 0.0;
        }
    }

    protected function normalizeBridgeFields(): void
    {
        $method = $this->getAttribute(BC::COL_PAY_MTD);
        $method = is_numeric($method) ? (int) $method : 0;
        if ($method < 0) $method = 0;
        if ($method > 255) $method = 255;
        $this->setAttribute(BC::COL_PAY_MTD, $method);

        $amount = $this->getAttribute('amount');
        $amount = is_numeric($amount) ? (float) $amount : 0.0;
        if ($amount < 0.0) $amount = 0.0;
        $this->setAttribute('amount', $amount);

        $date = $this->getAttribute('date');
        if ($date === null || $date === '') $this->setAttribute('date', now()->toDateString());
    }

    protected function ensureCompositeUniquenessWhenFullyLinked(): void
    {
        $purchaseId = (string) ($this->getAttribute(BC::COL_PRC_ID) ?? '');
        $paymentId  = (string) ($this->getAttribute(BC::COL_PAY_ID) ?? '');
        $bankAccId  = (string) ($this->getAttribute(BC::COL_BACC_ID) ?? '');

        if (trim($purchaseId) === '' || trim($paymentId) === '' || trim($bankAccId) === '') return;

        $id = (string) ($this->getAttribute('id') ?? '');

        try {
            $q = DB::table($this->getTable())
                ->where(BC::COL_PRC_ID, $purchaseId)
                ->where(BC::COL_PAY_ID, $paymentId)
                ->where(BC::COL_BACC_ID, $bankAccId);

            if ($id !== '') $q->where('id', '!=', $id);

            if (!$q->exists()) return;

            $this->setAttribute(BC::COL_PAY_ID, null);

            Log::debug(static::class . ' composite uniqueness conflict; nullified payment link to avoid constraint violation', [
                'id' => $id ?: null,
                BC::COL_PRC_ID => $purchaseId,
                BC::COL_PAY_ID => $paymentId,
                BC::COL_BACC_ID => $bankAccId,
            ]);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' uniqueness pre-check failed: ' . $e->getMessage(), [
                'id' => $id ?: null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
