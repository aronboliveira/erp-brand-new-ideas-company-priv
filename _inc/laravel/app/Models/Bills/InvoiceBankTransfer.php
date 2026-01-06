<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\PaymentStatus;
use App\Traits\{HasAuditFields, TracksFailures, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo, SoftDeletes};
use Illuminate\Support\Facades\Log;

class InvoiceBankTransfer extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, SoftDeletes, TracksFailures;

    protected $table = DC::TABLE_INV_BANK_TRANSFERS;

    protected $fillable = [
        BC::COL_BNK_TRF_ID,
        BC::COL_INV_ID,
        BC::COL_OD_ID,
        'amount',
        'status',
        'date',
        'receipt',
        ...TracksFailures::FAILURE_TRACKING_COLS,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'date'             => 'date',
        'status'           => 'string',
        DC::COL_FL_AT       => 'datetime',
        DC::COL_RTR_CT      => 'integer',
        DC::COL_LST_RTR_AT  => 'datetime',
        DC::COL_ER_LG       => 'array',
    ];

    protected $with = [
        'bankTransfer',
        'invoice',
        'order',
    ];

    protected $appends = [
        'effective_amount',
        'effective_status',
        'effective_date',
        'is_bridge_only',
    ];

    protected array $effectiveCache = [];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                self::normalizeStatus($m);
                self::normalizeAmount($m);
                self::normalizeReceipt($m);
                self::hydrateFromBankTransferIfMissing($m);
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving() failed', [
                    'table' => $m->getTable(),
                    'id' => $m->getAttribute('id'),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    protected static function normalizeStatus(self $m): void
    {
        $raw = $m->getAttribute('status');
        $enum = $raw instanceof PaymentStatus
            ? $raw
            : PaymentStatus::tryFrom(is_string($raw) ? $raw : '');

        $m->setAttribute('status', ($enum ?? PaymentStatus::Pending)->value);
    }

    protected static function normalizeAmount(self $m): void
    {
        $raw = $m->getAttribute('amount');

        if ($raw === null || (is_string($raw) && trim($raw) === ''))
            return;

        $val = $raw;
        if (is_string($val))
            $val = str_replace(',', '.', trim($val));

        if (!is_numeric($val)) {
            Log::warning(static::class . ' invalid amount, forcing null', [
                'id' => $m->getAttribute('id'),
                'amount' => $raw,
            ]);
            $m->setAttribute('amount', null);
            return;
        }

        $f = (float) $val;
        $f < 0 && $f = 0.0;

        $m->setAttribute('amount', $f);
    }

    protected static function normalizeReceipt(self $m): void
    {
        $raw = $m->getAttribute('receipt');
        if ($raw === null)
            return;

        if (!is_string($raw)) {
            Log::warning(static::class . ' receipt is non-string, forcing null', [
                'id' => $m->getAttribute('id'),
                'type' => gettype($raw),
            ]);
            $m->setAttribute('receipt', null);
            return;
        }

        $trim = trim($raw);
        $m->setAttribute('receipt', $trim === '' ? null : $trim);
    }

    protected static function hydrateFromBankTransferIfMissing(self $m): void
    {
        $btId = $m->getAttribute(BC::COL_BNK_TRF_ID);
        if (!is_string($btId) || trim($btId) === '')
            return;

        try {
            $bt = $m->relationLoaded('bankTransfer')
                ? $m->getRelation('bankTransfer')
                : BankTransfer::query()->find($btId);

            if (!$bt)
                return;

            $m->getAttribute('amount') === null && $m->setAttribute('amount', $bt->getAttribute('amount'));
            ($m->getAttribute('status') === null || trim((string) $m->getAttribute('status')) === '')
                && $m->setAttribute('status', (string) ($bt->getAttribute('status') ?? PaymentStatus::Pending->value));

            if ($m->getAttribute('date') === null) {
                $paidAt = $bt->getAttribute(BC::COL_PD_AT);
                $m->setAttribute('date', $paidAt ? Carbon::parse($paidAt)->toDateString() : null);
            }
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed hydrating from BankTransfer', [
                'id' => $m->getAttribute('id'),
                'bank_transfer_id' => $btId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function bankTransfer(): BelongsTo
    {
        return $this->belongsTo(BankTransfer::class, BC::COL_BNK_TRF_ID, 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_INV_ID, 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, BC::COL_OD_ID, 'id');
    }

    public function getEffectiveAmountAttribute(): ?string
    {
        return $this->effective()['amount'];
    }

    public function getEffectiveStatusAttribute(): string
    {
        return $this->effective()['status'];
    }

    public function getEffectiveDateAttribute(): ?string
    {
        return $this->effective()['date'];
    }

    public function getIsBridgeOnlyAttribute(): bool
    {
        $e = $this->effective();
        $localAmount = $this->getAttribute('amount');
        $localStatus = $this->getAttribute('status');
        $localDate = $this->getAttribute('date');

        return $this->getAttribute(BC::COL_BNK_TRF_ID) !== null
            && ($localAmount === null || (string) $localAmount === (string) $e['amount'])
            && ($localStatus === null || (string) $localStatus === (string) $e['status'])
            && ($localDate === null || (string) $localDate === (string) $e['date']);
    }

    public function effective(bool $refresh = false): array
    {
        if (!$refresh && $this->effectiveCache !== [])
            return $this->effectiveCache;

        $amount = $this->getAttribute('amount');
        $status = $this->getAttribute('status');
        $date = $this->getAttribute('date');

        $bt = null;
        $btId = $this->getAttribute(BC::COL_BNK_TRF_ID);

        try {
            $bt = $this->relationLoaded('bankTransfer')
                ? $this->getRelation('bankTransfer')
                : (is_string($btId) && trim($btId) !== '' ? BankTransfer::query()->find($btId) : null);
        } catch (\Throwable $e) {
            Log::error(static::class . ' effective() failed loading BankTransfer', [
                'id' => $this->getAttribute('id'),
                'bank_transfer_id' => $btId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);
        }

        if ($bt) {
            $amount === null && $amount = $bt->getAttribute('amount');
            $status = (is_string($status) && trim($status) !== '') ? $status : (string) ($bt->getAttribute('status') ?? PaymentStatus::Pending->value);

            if ($date === null) {
                $paidAt = $bt->getAttribute(BC::COL_PD_AT);
                $date = $paidAt ? Carbon::parse($paidAt)->toDateString() : null;
            }
        }

        $enum = $status instanceof PaymentStatus
            ? $status
            : PaymentStatus::tryFrom(is_string($status) ? $status : '');
        $status = ($enum ?? PaymentStatus::Pending)->value;

        $this->effectiveCache = [
            'amount' => $amount === null ? null : (string) $amount,
            'status' => $status,
            'date' => $date ? (string) (is_string($date) ? $date : Carbon::parse($date)->toDateString()) : null,
        ];

        return $this->effectiveCache;
    }

    public function invalidateEffectiveCache(): void
    {
        $this->effectiveCache = [];
    }
}
