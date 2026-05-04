<?php

namespace App\Models;

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\{
    TransactionType,
    PaymentMethod,
    MimeType
};
use App\Traits\{
    DefinesDates,
    HasAuditFields,
    HasPaymentColumns,
    TracksFailures,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Log
};
use Illuminate\Support\Str;

/**
 * @property array|string|null $attachments
 * @property int|string|null $currency_id
 * @property bool|null $is_secured
 * @property string|null $autoreconcile
 * @property bool|null $can_be_charged_back
 * @property mixed $created_by
 * @property int|null $payment_id
 * @property array|string|null $reconcile_rules
 * @property \Illuminate\Support\Carbon|string|null $reconciled_at
 * @property mixed $reconciled_by
 * @property array|string|null $terms_and_conditions
 * @property int|null $user_id
 * @property mixed $user_type
 * @property string|int|null $account
 * @property string|null $type
 * @property mixed $category
 * @property float|null $amount
 * @property \Illuminate\Support\Carbon|null $date
 * @property string|null $description
 */
class Transaction extends Model
{
    use HasFactory, UsesUuids, HasPaymentColumns, HasAuditFields, TracksFailures, DefinesDates;

    public const TABLE = DC::TABLE_TRS;

    public const TRS_PATTERN = '/^TRS-[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}-\d{10,}$/i';

    protected $table = self::TABLE;

    protected $fillable = [
        'code',
        'account',
        UC::COL_USER_ID,
        UC::COL_U_TP,
        BC::COL_PAY_TP,
        BC::COL_PAY_ID,
        'category',
        BC::COL_CUR_ID,
        'amount',
        BC::COL_SVC_FEE,
        BC::COL_TXS_FEE,
        BC::COL_TXS_LST,
        BC::COL_PAY_MTD,
        BC::COL_PAY_MTD_LB,
        BC::COL_N_INTR,
        BC::COL_CURR_N_INTR,
        BC::COL_SCHD_TRF_TS,
        BC::COL_EXC_AT,
        BC::COL_CNC_AT,
        BC::COL_CMP_AT,
        BC::COL_CNC_RS,
        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        BC::COL_PPS_CD,
        BC::COL_TRF_TP,
        BC::COL_PPS_DS,
        BC::COL_TC,
        BC::COL_AUTORCC,
        BC::COL_RCC_RL,
        BC::COL_RCC_AT,
        BC::COL_RCC_BY,
        'reference',
        'description',
        'notes',
        'attachments',
        'contract',
        'loan',
        'invoice',
        'payslip',
        BC::COL_PRD_SV_UNT,
        'type',
        'date',
        ...self::FAILURE_TRACKING_COLS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'code'                      => 'string',
        'amount'                    => 'decimal:2',
        BC::COL_SVC_FEE             => 'decimal:2',
        BC::COL_TXS_FEE             => 'decimal:2',
        BC::COL_N_INTR              => 'integer',
        BC::COL_CURR_N_INTR         => 'integer',
        DC::COL_RTR_CT              => 'integer',

        BC::COL_TXS_LST             => 'array',
        BC::COL_TC                  => 'array',
        BC::COL_RCC_RL              => 'array',
        DC::COL_ER_LG               => 'array',
        'attachments'               => 'array',

        BC::COL_SCHD_TRF_TS         => 'datetime',
        BC::COL_EXC_AT              => 'datetime',
        BC::COL_CNC_AT              => 'datetime',
        DC::COL_FL_AT               => 'datetime',
        BC::COL_CMP_AT              => 'datetime',
        BC::COL_RCC_AT              => 'datetime',
        DC::COL_LST_RTR_AT          => 'datetime',

        BC::COL_IS_SCD              => 'boolean',
        BC::COL_CAN_CHG_BK          => 'boolean',
        BC::COL_AUTORCC             => 'boolean',

        BC::COL_PAY_TP              => 'string',
        BC::COL_PAY_MTD_LB          => 'string',
        BC::COL_PPS_CD              => 'string',
        BC::COL_TRF_TP              => 'string',
        BC::COL_CUR_ID              => 'string',
    ];

    protected $with = [
        'bankAccount',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $transaction): void {
            self::normalizePaymentType($transaction);
            self::sanitizeNumericFields($transaction);
            self::normalizeDates($transaction);
            self::normalizePaymentMethodLabel($transaction);
            self::sanitizeAttachments($transaction);
            self::sanitizeTaxConfig($transaction);
            self::ensureDefaults($transaction);
            $rawCode = $transaction->getAttribute('code') ?? null;
            if (!$rawCode || $rawCode === '' || !preg_match(self::TRS_PATTERN, $rawCode)) {
                $maxAttempts = 160000;
                do {
                    $maxAttempts--;
                    $uuid = Str::uuid();
                    $timestamp = Carbon::now()->timestamp;
                    $code = "TRS-{$uuid}-{$timestamp}";
                } while (Transaction::query()->where('code', $code)->exists() && $maxAttempts > 0);
                if ($maxAttempts > 0) {
                    $transaction->setAttribute('code', $code);
                } else {
                    Log::error(
                        self::class . '::booted failed to generate unique code for Transaction after maximum attempts.'
                    );
                }
            }
        });
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'account');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, UC::COL_USER_ID);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, BC::COL_PAY_ID);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_PAY_ID);
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, BC::COL_PAY_ID);
    }

    public function payment(): ?Model
    {
        $type = TransactionType::tryFrom((string)($this->{BC::COL_PAY_TP} ?? ''));

        return match ($type) {
            TransactionType::Bill    => $this->bill,
            TransactionType::Invoice => $this->invoice,
            TransactionType::Pos     => $this->pos,
            default                  => null,
        };
    }

    public function scopeForBill($query, string $billId)
    {
        return $query
            ->where(BC::COL_PAY_TP, TransactionType::Bill->value)
            ->where(BC::COL_PAY_ID, $billId);
    }

    public function scopeForInvoice($query, string $invoiceId)
    {
        return $query
            ->where(BC::COL_PAY_TP, TransactionType::Invoice->value)
            ->where(BC::COL_PAY_ID, $invoiceId);
    }

    public function scopeForPos($query, string $posId)
    {
        return $query
            ->where(BC::COL_PAY_TP, TransactionType::Pos->value)
            ->where(BC::COL_PAY_ID, $posId);
    }

    public function scopeBills($query)
    {
        return $query->where(BC::COL_PAY_TP, TransactionType::Bill->value);
    }

    public function scopeInvoices($query)
    {
        return $query->where(BC::COL_PAY_TP, TransactionType::Invoice->value);
    }

    public function scopePosPayments($query)
    {
        return $query->where(BC::COL_PAY_TP, TransactionType::Pos->value);
    }

    public static function addTransaction(Request|Model $source): ?self
    {
        return DB::transaction(function () use ($source) {
            try {
                $trx = new self();
                foreach ($trx->getFillable() as $field) {
                    $value = $source instanceof Request
                        ? $source->input($field)
                        : ($source->getAttribute($field) ?? null);
                    if ($value !== null)
                        $trx->setAttribute($field, $value);
                }
                $trx->save();
                return $trx;
            } catch (\Throwable $e) {
                Log::error(
                    self::class . '::addTransaction failed: ' . $e->getMessage(),
                    ['exception' => $e]
                );
                return null;
            }
        });
    }

    public static function editTransaction(Request|Model $source): void
    {
        DB::transaction(function () use ($source) {
            try {
                $paymentId = $source instanceof Request
                    ? $source->input(BC::COL_PAY_ID)
                    : ($source->getAttribute(BC::COL_PAY_ID) ?? null);
                $paymentType = $source instanceof Request
                    ? $source->input(BC::COL_PAY_TP)
                    : ($source->getAttribute(BC::COL_PAY_TP) ?? null);
                if (!$paymentId || !$paymentType)
                    return;
                /** @var self|null $trx */
                $trx = self::query()
                    ->where(BC::COL_PAY_ID, $paymentId)
                    ->where(BC::COL_PAY_TP, $paymentType)
                    ->first();
                if (!$trx)
                    return;
                $editable = [
                    'account',
                    'amount',
                    'description',
                    'date',
                    'category',
                ];
                foreach ($editable as $field) {
                    $value = $source instanceof Request
                        ? $source->input($field)
                        : ($source->getAttribute($field) ?? $trx->getAttribute($field));
                    if ($value !== null)
                        $trx->setAttribute($field, $value);
                }
                $trx->save();
            } catch (\Throwable $e) {
                Log::error(
                    self::class . '::editTransaction failed: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
        });
    }

    public static function destroyTransaction(string $paymentId, string $paymentType, string $userType): void
    {
        try {
            self::query()
                ->where(BC::COL_PAY_ID, $paymentId)
                ->where(BC::COL_PAY_TP, $paymentType)
                ->where(UC::COL_U_TP, $userType)
                ->delete();
        } catch (\Throwable $e) {
            Log::error(
                self::class . '::destroyTransaction failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }

    public static function accounts(string $accountIds): string
    {
        $ids = array_values(array_filter(array_map(
            fn($v) => trim((string)$v),
            explode(',', $accountIds)
        ), fn($v) => $v !== ''));

        if (!$ids) return '';

        $rows = BankAccount::query()
            ->whereIn('id', $ids)
            ->get(['id', 'bank_name', 'holder_name']);

        return $rows
            ->map(fn($a) => trim(($a->bank_name ?? '') . ' ' . ($a->holder_name ?? '')))
            ->filter(fn($s) => $s !== '')
            ->values()
            ->implode(', ');
    }

    protected static function normalizePaymentType(self $transaction): void
    {
        $raw = $transaction->getAttribute(BC::COL_PAY_TP) ?? null;
        if ($raw === null || $raw === '') {
            $transaction->setAttribute(BC::COL_PAY_TP, TransactionType::Other->value);
            return;
        }
        $type = TransactionType::tryFrom((string)$raw);
        if (!$type) {
            Log::warning(
                self::class . '::normalizePaymentType invalid payment type',
                ['value' => $raw]
            );
            $type = TransactionType::Other;
        }
        $transaction->setAttribute(BC::COL_PAY_TP, $type->value);
        if (empty($transaction->category))
            $transaction->setAttribute('category', $type->value);
    }

    protected static function sanitizeNumericFields(self $transaction): void
    {
        $floatFields = [
            'amount',
            BC::COL_SVC_FEE,
            BC::COL_TXS_FEE,
        ];

        $intFields = [
            BC::COL_N_INTR,
            BC::COL_CURR_N_INTR,
            DC::COL_RTR_CT,
        ];

        $codeFields = [
            BC::COL_PPS_CD,
        ];

        foreach ($floatFields as $field) {
            if (!array_key_exists($field, $transaction->attributes))
                continue;
            $value = $transaction->getAttribute($field);
            if ($value === null || $value === '' || !is_numeric($value)) {
                $transaction->setAttribute($field, 0.0);
                continue;
            }
            $numeric = (float) $value;
            if ($numeric < 0)
                $numeric = 0.0;
            $transaction->setAttribute($field, $numeric);
        }
        foreach ($intFields as $field) {
            if (!array_key_exists($field, $transaction->attributes))
                continue;
            $value = $transaction->getAttribute($field);
            if ($value === null || $value === '' || !is_numeric($value)) {
                $transaction->setAttribute($field, 0);
                continue;
            }
            $numeric = (int) $value;
            if ($numeric < 0)
                $numeric = 0;
            $transaction->setAttribute($field, $numeric);
        }

        foreach ($codeFields as $field) {
            if (!array_key_exists($field, $transaction->attributes))
                continue;
            $value = $transaction->getAttribute($field);
            if ($value === null || $value === '') {
                $transaction->setAttribute($field, '0');
                continue;
            }
            if (is_numeric($value)) {
                $numeric = (int) $value;
                if ($numeric < 0)
                    $numeric = 0;
                $transaction->setAttribute($field, (string) $numeric);
            }
        }
    }

    protected static function normalizeDates(self $transaction): void
    {
        $now = now();

        if (empty($transaction->getAttribute('date')))
            $transaction->setAttribute('date', $now->format('Y-m-d'));

        $dateFields = [
            BC::COL_SCHD_TRF_TS, // scheduled: optionally constrain
            BC::COL_EXC_AT,      // executed: allow past
            BC::COL_CNC_AT,      // cancelled: allow past
            DC::COL_FL_AT,       // failed: allow past
            BC::COL_CMP_AT,      // completed: allow past
        ];

        foreach ($dateFields as $field) {
            if (!array_key_exists($field, $transaction->attributes)) continue;

            $value = $transaction->getAttribute($field);
            if (!$value) continue;

            try {
                $dt = $value instanceof Carbon ? $value : Carbon::parse($value);

                if ($field === BC::COL_SCHD_TRF_TS && $dt->lt($now))
                    $dt = $now;

                $transaction->setAttribute($field, $dt);
            } catch (\Throwable $e) {
                Log::warning(self::class . '::normalizeDates invalid datetime', [
                    'field' => $field,
                    'value' => $value,
                    'error' => $e->getMessage()
                ]);
                $transaction->setAttribute($field, null);
            }
        }
    }

    protected static function normalizePaymentMethodLabel(self $transaction): void
    {
        if (!array_key_exists(BC::COL_PAY_MTD_LB, $transaction->attributes)) return;

        $raw = $transaction->getAttribute(BC::COL_PAY_MTD_LB);
        $enum = PaymentMethod::normalize($raw !== null ? (string)$raw : null);
        $transaction->setAttribute(BC::COL_PAY_MTD_LB, $enum->value);
    }

    protected static function sanitizeAttachments(self $transaction): void
    {
        if (!array_key_exists('attachments', $transaction->attributes)) {
            return;
        }

        $raw = $transaction->attachments;

        if ($raw === null) {
            return;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items   = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $items = $raw;
        } else {
            $items = [];
        }

        $valid = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $path = $item['path'] ?? null;
            $ext  = $item['extension'] ?? null;

            if ($path === null && $ext === null) {
                Log::warning(
                    self::class . '::sanitizeAttachments missing path/extension, dropping item',
                    ['index' => $index]
                );
                continue;
            }

            if (!$ext && is_string($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
            }

            if (!is_string($ext) || $ext === '') {
                Log::warning(
                    self::class . '::sanitizeAttachments empty extension, dropping item',
                    ['index' => $index, 'path' => $path]
                );
                continue;
            }

            $mime = MimeType::fromExtension($ext);

            if ($mime === null) {
                Log::warning(
                    self::class . '::sanitizeAttachments unsupported extension, dropping item',
                    ['index' => $index, 'extension' => $ext]
                );
                continue;
            }

            $item['extension'] = strtolower(ltrim($ext, '.'));
            $item['mime']      = $mime->value;

            $valid[] = $item;
        }

        $transaction->attachments = $valid ?: null;
    }

    protected static function sanitizeTaxConfig(self $transaction): void
    {
        if (!array_key_exists(BC::COL_TC, $transaction->attributes)) {
            return;
        }

        $raw = $transaction->{BC::COL_TC};

        if ($raw === null) {
            return;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items   = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $items = $raw;
        } else {
            $items = [];
        }

        if ($items === []) {
            $transaction->{BC::COL_TC} = null;
            return;
        }

        $valid = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $tax = null;

            if (!empty($item['id'])) {
                $tax = Tax::find($item['id']);
            }

            if (!$tax && !empty($item['name'])) {
                $tax = Tax::where(BC::COL_TAX_NM, $item['name'])->first();
            }

            if (!$tax && !empty($item['label'])) {
                $tax = Tax::where(BC::COL_TAX_NM, $item['label'])->first();
            }

            if (!$tax) {
                Log::warning(
                    self::class . '::sanitizeTaxConfig could not resolve tax entry, dropping item',
                    ['index' => $index, 'item' => $item]
                );
                continue;
            }

            $item['id']   = $tax->id;
            $item['name'] = $tax->{BC::COL_TAX_NM} ?? $item['name'] ?? null;

            $valid[] = $item;
        }

        $transaction->{BC::COL_TC} = $valid ?: null;
    }

    protected static function ensureDefaults(self $transaction): void
    {
        if (empty($transaction->{BC::COL_CUR_ID})) {
            $transaction->{BC::COL_CUR_ID} = config('app.currency', 'BRL');
        }

        foreach ([BC::COL_IS_SCD, BC::COL_CAN_CHG_BK, BC::COL_AUTORCC] as $flag) {
            if (!array_key_exists($flag, $transaction->attributes)) {
                continue;
            }

            if ($transaction->{$flag} === null) {
                $transaction->{$flag} = false; // @phpstan-ignore assign.propertyType
            }
        }

        if (
            array_key_exists(BC::COL_AUTORCC, $transaction->attributes)
            && !$transaction->{BC::COL_AUTORCC}
        ) {
            foreach ([BC::COL_RCC_RL, BC::COL_RCC_AT, BC::COL_RCC_BY] as $field) {
                if (array_key_exists($field, $transaction->attributes)) {
                    $transaction->{$field} = null;
                }
            }
        }
    }

    public function billPayment(): BelongsTo
    {
        return $this->belongsTo(BillPayment::class, 'payment_id');
    }
}
