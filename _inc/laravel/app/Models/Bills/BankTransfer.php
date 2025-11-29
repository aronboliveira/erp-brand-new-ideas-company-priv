<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{MimeType, PaymentMethod};
use App\Traits\{HasAuditFields, UsesUuids};
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use Illuminate\Support\Facades\Log;

class BankTransfer extends Model
{
    use HasAuditFields, UsesUuids;

    public const TABLE = DC::TABLE_BNK_TRF;

    protected $table = self::TABLE;

    protected $fillable = [
        BC::COL_ACC_FROM,
        BC::COL_ACC_TO,
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
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        BC::COL_SVC_FEE          => 'decimal:2',
        BC::COL_TXS_FEE          => 'decimal:2',
        BC::COL_TXS_LST          => 'array',
        BC::COL_N_INTR           => 'integer',
        BC::COL_CURR_N_INTR      => 'integer',
        BC::COL_PAY_MTD          => 'integer',
        BC::COL_SCHD_TRF_TS      => 'datetime',
        BC::COL_EXC_AT           => 'datetime',
        BC::COL_CNC_AT           => 'datetime',
        DC::COL_FL_AT            => 'datetime',
        BC::COL_CMP_AT           => 'datetime',
        BC::COL_IS_SCD           => 'boolean',
        BC::COL_CAN_CHG_BK       => 'boolean',
        BC::COL_TC               => 'array',
        BC::COL_AUTORCC          => 'boolean',
        BC::COL_RCC_RL           => 'array',
        BC::COL_RCC_AT           => 'datetime',
        DC::COL_ER_LG            => 'array',
        DC::COL_RTR_CT           => 'integer',
        DC::COL_LST_RTR_AT       => 'datetime',
        'attachments'            => 'array',
    ];

    protected $with = [
        'fromBankAccount',
        'toBankAccount',
    ];

    protected $appends = [
        'status',
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (self $model): void {
            if (empty($model->{BC::COL_ACC_FROM}))
                throw new \InvalidArgumentException('account_from is required');
            if (empty($model->{BC::COL_ACC_TO}))
                throw new \InvalidArgumentException('account_to is required');
        });
        static::saving(function (self $transfer): void {
            self::normalizeNumericFields($transfer);
            self::normalizeTimestamps($transfer);
            self::enforcePaymentChannel($transfer);
            self::normalizeAttachments($transfer);
            self::normalizeTermsConditionsTaxes($transfer);
            self::enforceAccountCoherence($transfer);
        });
    }

    protected static function normalizeNumericFields(self $transfer): void
    {
        $decimalFields = [
            'amount',
            BC::COL_SVC_FEE,
            BC::COL_TXS_FEE,
        ];

        foreach ($decimalFields as $field) {
            $value = $transfer->{$field} ?? null;

            if ($value === null || (is_string($value) && trim($value) === '')) {
                $transfer->{$field} = 0.0;
                continue;
            }

            if (is_string($value)) {
                $value = str_replace(',', '.', trim($value));
            }

            if (!is_numeric($value)) {
                Log::warning("BankTransfer: non-numeric value for decimal field `{$field}` on transfer {$transfer->id}, forcing to 0.");
                $value = 0.0;
            }

            $floatVal = (float) $value;

            if ($floatVal < 0) {
                $floatVal = 0.0;
            }

            $transfer->{$field} = $floatVal;
        }

        $intFields = [
            BC::COL_N_INTR,
            BC::COL_CURR_N_INTR,
            BC::COL_PAY_MTD,
            DC::COL_RTR_CT,
        ];

        foreach ($intFields as $field) {
            $value = $transfer->{$field} ?? null;

            if ($value === null || (is_string($value) && trim($value) === '')) {
                $transfer->{$field} = 0;
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            if (!is_numeric($value)) {
                Log::warning("BankTransfer: non-numeric value for integer field `{$field}` on transfer {$transfer->id}, forcing to 0.");
                $value = 0;
            }

            $intVal = (int) $value;

            if ($intVal < 0) {
                $intVal = 0;
            }

            $transfer->{$field} = $intVal;
        }

        $total = (int) ($transfer->{BC::COL_N_INTR} ?? 1);
        $current = (int) ($transfer->{BC::COL_CURR_N_INTR} ?? 1);

        if ($total < 1) {
            $total = 1;
        }

        if ($current < 1) {
            $current = 1;
        }

        if ($current > $total) {
            $current = $total;
        }

        $transfer->{BC::COL_N_INTR} = $total;
        $transfer->{BC::COL_CURR_N_INTR} = $current;

        // Purpose code: não negativo, numérico-ish, default 300
        $code = $transfer->{BC::COL_PPS_CD} ?? null;

        if ($code === null || (is_string($code) && trim($code) === '')) {
            $transfer->{BC::COL_PPS_CD} = '300';
            return;
        }

        $codeString = (string) $code;
        $digits = preg_replace('/\D+/', '', $codeString);

        if ($digits === '') {
            Log::warning("BankTransfer: invalid purpose code `{$codeString}` on transfer {$transfer->id}, falling back to 300.");
            $digits = '300';
        }

        $intCode = (int) $digits;

        if ($intCode < 0) {
            $intCode = abs($intCode);
        }

        $transfer->{BC::COL_PPS_CD} = (string) $intCode;
    }

    protected static function normalizeTimestamps(self $transfer): void
    {
        $now = now();

        $fields = [
            BC::COL_SCHD_TRF_TS,
            BC::COL_EXC_AT,
            BC::COL_CNC_AT,
            DC::COL_FL_AT,
            BC::COL_CMP_AT,
        ];

        foreach ($fields as $field) {
            if (!$transfer->isDirty($field)) {
                continue;
            }

            $value = $transfer->{$field} ?? null;

            if (!$value) {
                continue;
            }

            $ts = $value instanceof Carbon ? $value : Carbon::parse($value);

            if ($ts->lessThan($now)) {
                $transfer->{$field} = $now;
            } else {
                $transfer->{$field} = $ts;
            }
        }
    }

    protected static function enforcePaymentChannel(self $transfer): void
    {
        $raw = $transfer->{BC::COL_PAY_MTD_LB} ?? null;

        if ($raw === null || $raw === '') {
            $transfer->{BC::COL_PAY_MTD_LB} = PaymentMethod::BankTransfer->value;
            return;
        }

        $value = strtolower(trim((string) $raw));

        $aliases = [
            'debit'  => PaymentMethod::CardDebit->value,
            'credit' => PaymentMethod::CardCredit->value,
            'wire'   => PaymentMethod::WireTransfer->value,
        ];

        $candidate = $aliases[$value] ?? $value;

        $channel = PaymentMethod::tryFrom($candidate);

        if (!$channel) {
            Log::warning("BankTransfer: invalid payment channel `{$raw}` on transfer {$transfer->id}, forcing to `other`.");
            $channel = PaymentMethod::Other;
        }

        $transfer->{BC::COL_PAY_MTD_LB} = $channel->value;

        if ($channel === PaymentMethod::CardDebit) {
            $transfer->{BC::COL_PAY_MTD} = 0;
        } elseif ($channel === PaymentMethod::CardCredit) {
            $transfer->{BC::COL_PAY_MTD} = 1;
        } elseif ($transfer->{BC::COL_PAY_MTD} === null) {
            $transfer->{BC::COL_PAY_MTD} = 0;
        }
    }

    protected static function normalizeAttachments(self $transfer): void
    {
        $attachments = $transfer->attachments ?? [];

        if (is_string($attachments)) {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($attachments)) {
            $attachments = (array) $attachments;
        }

        $normalized = [];

        foreach ($attachments as $idx => $item) {
            if (!is_array($item)) {
                Log::error("BankTransfer: rejected non-array attachment at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            $path = $item['path'] ?? null;
            $ext = $item['extension'] ?? null;

            if (!$path && !$ext) {
                Log::error("BankTransfer: rejected attachment without path or extension at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            if (!$ext && $path) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
            }

            if (!$ext) {
                Log::error("BankTransfer: rejected attachment without resolvable extension at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            $ext = strtolower(ltrim((string) $ext, '.'));

            $mime = MimeType::fromExtension($ext);

            if (!$mime) {
                Log::error("BankTransfer: rejected attachment with unsupported extension `{$ext}` at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            $item['extension'] = $ext;
            $item['mime_type'] = $mime->value;

            if ($path) {
                $item['path'] = $path;
            }

            $normalized[] = $item;
        }

        $transfer->attachments = $normalized;
    }

    protected static function normalizeTermsConditionsTaxes(self $transfer): void
    {
        $terms = $transfer->{BC::COL_TC} ?? [];

        if (is_string($terms)) {
            $decoded = json_decode($terms, true);
            $terms = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($terms)) {
            $terms = (array) $terms;
        }

        if ($terms === []) {
            $transfer->{BC::COL_TC} = [];
            return;
        }

        $normalized = [];

        foreach ($terms as $idx => $entry) {
            if (!is_array($entry)) {
                Log::error("BankTransfer: rejected non-array terms_and_conditions entry at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            $tax = null;

            if (!empty($entry['id'])) {
                $tax = Tax::query()->find($entry['id']);
            }

            if (!$tax) {
                $name = $entry['name'] ?? $entry['label'] ?? null;

                if (is_string($name) && $name !== '') {
                    $tax = Tax::query()->where('name', $name)->first()
                        ?? Tax::query()->where('label', $name)->first();
                }
            }

            if (!$tax) {
                Log::error("BankTransfer: rejected terms_and_conditions element without resolvable tax at index {$idx} on transfer {$transfer->id}.");
                continue;
            }

            $entry['id'] = $tax->id;

            if (!isset($entry['name'])) {
                $entry['name'] = $tax->name ?? null;
            }

            if (!isset($entry['label']) && isset($tax->label)) {
                $entry['label'] = $tax->label;
            }

            $normalized[] = $entry;
        }

        $transfer->{BC::COL_TC} = $normalized;
    }

    protected static function enforceAccountCoherence(self $transfer): void
    {
        $from = $transfer->{BC::COL_ACC_FROM} ?? null;
        $to = $transfer->{BC::COL_ACC_TO} ?? null;

        if ($from && $to && $from === $to) {
            throw new \InvalidArgumentException('Source and destination bank accounts must be different.');
        }
    }

    public function getStatusAttribute(): string
    {
        $cancelled = $this->{BC::COL_CNC_AT};
        $failed = $this->{DC::COL_FL_AT};
        $completed = $this->{BC::COL_CMP_AT};
        $executed = $this->{BC::COL_EXC_AT};
        $scheduled = $this->{BC::COL_SCHD_TRF_TS};

        // Convert any date format to Carbon for comparison
        $cancelledDate = $this->toCarbon($cancelled);
        $failedDate = $this->toCarbon($failed);
        $completedDate = $this->toCarbon($completed);
        $executedDate = $this->toCarbon($executed);
        $scheduledDate = $this->toCarbon($scheduled);

        if ($cancelledDate && !$cancelledDate->isFuture()) {
            return 'cancelled';
        }

        if ($failedDate && !$failedDate->isFuture()) {
            return 'failed';
        }

        if ($completedDate && !$completedDate->isFuture()) {
            return 'completed';
        }

        if ($executedDate && !$executedDate->isFuture()) {
            return 'executing';
        }

        if ($scheduledDate && $scheduledDate->isFuture()) {
            return 'scheduled';
        }

        return 'pending';
    }

    private function toCarbon($date): ?Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof DateTime) {
            return Carbon::instance($date);
        }

        if (is_numeric($date)) {
            return Carbon::createFromTimestamp($date);
        }

        if (is_string($date)) {
            return Carbon::parse($date);
        }

        return null;
    }

    public function fromBankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_ACC_FROM,
            'id'
        );
    }

    public function toBankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            BC::COL_ACC_TO,
            'id'
        );
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            BC::COL_RCC_BY,
            'id'
        );
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(
            Contract::class,
            'contract',
            'id'
        );
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(
            Loan::class,
            'loan',
            'id'
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class,
            'invoice',
            'id'
        );
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(
            Payslip::class,
            'payslip',
            'id'
        );
    }

    public function productServiceUnit(): BelongsTo
    {
        return $this->belongsTo(
            ProductServiceUnit::class,
            BC::COL_PRD_SV_UNT,
            'id'
        );
    }
}
