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
    HasAuditFields,
    UsesUuids
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Model,
    Relations\BelongsTo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Log
};

class Transaction extends Model
{
    use UsesUuids, HasAuditFields;

    public const TABLE = DC::TABLE_TRS;

    protected $table = self::TABLE;

    protected $fillable = [
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
        DC::COL_FL_AT,
        BC::COL_CMP_AT,
        DC::COL_FLD_RS,
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
        DC::COL_ER_LG,
        DC::COL_RTR_CT,
        DC::COL_LST_RTR_AT,
        'type',
        'date',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
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
        $names = '';
        foreach (explode(',', $accountIds) as $acctId) {
            $acctId = trim($acctId);
            if ($acctId === '')
                continue;
            /** @var BankAccount|null $acct */
            $acct = BankAccount::find($acctId);
            if (!$acct)
                continue;
            $names = ($acct->bank_name ?? '') . '  ' . ($acct->holder_name ?? '');
        }
        return $names;
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
            BC::COL_SCHD_TRF_TS,
            BC::COL_EXC_AT,
            BC::COL_CNC_AT,
            DC::COL_FL_AT,
            BC::COL_CMP_AT,
        ];
        foreach ($dateFields as $field) {
            if (!array_key_exists($field, $transaction->attributes))
                continue;
            $value = $transaction->getAttribute($field);
            if (!$value)
                continue;
            try {
                $dt = $value instanceof Carbon ? $value : Carbon::parse($value);
                if ($dt->lt($now))
                    $transaction->setAttribute($field, $now);
            } catch (\Throwable $e) {
                Log::warning(
                    self::class . '::normalizeDates invalid datetime',
                    ['field' => $field, 'value' => $value, 'error' => $e->getMessage()]
                );
                $transaction->setAttribute($field, null);
            }
        }
    }

    protected static function normalizePaymentMethodLabel(self $transaction): void
    {
        if (!array_key_exists(BC::COL_PAY_MTD_LB, $transaction->attributes))
            return;
        $raw = $transaction->getAttribute(BC::COL_PAY_MTD_LB);
        if ($raw === null || $raw === '') {
            $transaction->setAttribute(BC::COL_PAY_MTD_LB, PaymentMethod::Other->value);
            return;
        }
        $normalized = strtolower(trim((string) $raw));
        $channel = match ($normalized) {
            'debit', 'card_debit', 'debit_card'       => PaymentMethod::CardDebit,
            'credit', 'card_credit', 'credit_card'    => PaymentMethod::CardCredit,
            'pix'                                     => PaymentMethod::Pix,
            'ted'                                     => PaymentMethod::Ted,
            'doc'                                     => PaymentMethod::Doc,
            'wire', 'wire_transfer', 'bank_transfer'  => PaymentMethod::WireTransfer,
            'cash', 'dinheiro'                        => PaymentMethod::Cash,
            default                                   => PaymentMethod::Other,
        };
        $labelMap = [
            PaymentMethod::CardDebit->value   => 'debit',
            PaymentMethod::CardCredit->value  => 'credit',
            PaymentMethod::Pix->value         => 'pix',
            PaymentMethod::Ted->value         => 'ted',
            PaymentMethod::Doc->value         => 'doc',
            PaymentMethod::WireTransfer->value => BC::VL_WR_TRF,
            PaymentMethod::Cash->value        => 'cash',
            PaymentMethod::Other->value       => 'other',
        ];
        $enumValue = $channel->value;
        $dbLabel   = $labelMap[$enumValue] ?? 'other';
        if (!in_array(
            $dbLabel,
            ['debit', 'credit', 'pix', 'ted', 'doc', BC::VL_WR_TRF, 'cash', 'other'],
            true
        )) {
            Log::warning(
                self::class . '::normalizePaymentMethodLabel produced invalid label',
                ['raw' => $raw, 'normalized' => $dbLabel]
            );
            $dbLabel = 'other';
        }
        $transaction->setAttribute(BC::COL_PAY_MTD_LB, $dbLabel);
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
                $transaction->{$flag} = false;
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
}
