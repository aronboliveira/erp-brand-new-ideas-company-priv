<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, TransportationMethod};
use App\Traits\{DefinesDates, HasAuditFields, HasProductSecurityCoverage, NormalizesArrays, PlansByHierarchy, StoresManyRefJson, UsesUuids, UsesCountryRegions};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class WarehouseTransfer extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesArrays, StoresManyRefJson, DefinesDates, PlansByHierarchy, HasProductSecurityCoverage, UsesCountryRegions;

    protected $table = DC::TABLE_WRH_TRF;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $fillable = [
        'code',
        BC::COL_PRD_ID,
        BC::COL_FROM_WRH,
        BC::COL_TO_WRH,

        BC::COL_SHIP_NAME,
        BC::COL_SHIP_CTR,
        BC::COL_SHIP_ZIP,
        BC::COL_SHIP_ADR,
        BC::COL_SHIP_ST,
        BC::COL_SHIP_CTY,
        BC::COL_SHIP_TEL,
        BC::COL_SHIP_EMAIL,
        BC::COL_SHIP_DTL,

        'carrier',
        BC::COL_SVC_FEE,
        BC::COL_TRP_MTD,
        BC::COL_TRP_CST,

        ...self::PRODUCT_SECURITY_COLUMNS,

        BC::COL_REQ_BY,
        BC::COL_REQ_AT,
        BC::COL_APV_BY,
        BC::COL_APV_AT,
        PJC::COL_REJ_BY,
        PJC::COL_REJ_AT,
        BC::COL_REJ_RS,

        BC::COL_IS_RTNABLE,
        'quantity',
        BC::COL_QTY_RCV,
        BC::COL_QTY_RTN,
        'status',

        BC::COL_SCHD_DT,
        BC::COL_SHIP_DT,
        BC::COL_RCV_DT,
        BC::COL_IS_URGENT,

        'notes',
        'date',
        'attachments',
        'steps',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        BC::COL_QTY_RCV     => 'integer',
        BC::COL_QTY_RTN     => 'integer',

        BC::COL_SVC_FEE     => 'float',
        BC::COL_TRP_CST     => 'float',

        BC::COL_IS_RTNABLE  => 'boolean',
        BC::COL_IS_URGENT   => 'boolean',

        BC::COL_REQ_AT      => 'datetime',
        BC::COL_APV_AT      => 'datetime',
        PJC::COL_REJ_AT     => 'datetime',
        BC::COL_SCHD_DT     => 'datetime',
        BC::COL_SHIP_DT     => 'datetime',
        BC::COL_RCV_DT      => 'datetime',
        'date'              => 'date',

        BC::COL_TRP_MTD     => TransportationMethod::class,
        'status'            => EvaluationStatus::class,

        'attachments'       => 'array',
        'steps'             => 'array',

        BC::COL_HAS_WRT     => 'boolean',
        BC::COL_HAS_INS     => 'boolean',
        BC::COL_HAS_EXT_SEC => 'boolean',
        BC::COL_WRT_CST     => 'float',
        BC::COL_INS_CST     => 'float',
        BC::COL_EXT_SEC_CST => 'float',
        BC::COL_WRT_DYS     => 'integer',
    ];

    protected $with = [
        'creator',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m): void {
            $m->assertDifferentWarehouses();
            $m->assignCodeIfMissing();
        });

        static::saving(function (self $m): void {
            $m->assertDifferentWarehouses();
            $m->normalizeCoreFields();
            $m->normalizeDatesAndStatus();
            $m->normalizeJsonFields();
            $m->enforceQuantities();
            $m->enforceRejectionReason();
            $m->mirrorDateFromShipScheduleOrNow();
        });
    }

    protected function normalizedWarehouseId(string $col): ?string
    {
        $v = trim((string) ($this->getAttribute($col) ?? ''));
        return $v !== '' ? $v : null;
    }

    protected function assertDifferentWarehouses(): void
    {
        $from = $this->normalizedWarehouseId(BC::COL_FROM_WRH);
        $to   = $this->normalizedWarehouseId(BC::COL_TO_WRH);

        if (!$from || !$to) return;
        if ($from !== $to) return;

        throw new \InvalidArgumentException('WarehouseTransfer requires different from_warehouse and to_warehouse.');
    }


    protected function assignCodeIfMissing(): void
    {
        $raw = trim((string) ($this->getAttribute('code') ?? ''));
        if ($raw !== '') return;

        $attempts = 0;
        $exists = true;

        do {
            $attempts++;
            $candidate = 'WRH-TRF-' . strtoupper((string) Str::uuid()) . '-' . now()->format('YmdHis');
            $exists = DB::table($this->getTable())->where('code', $candidate)->exists();
        } while ($exists && $attempts < 25);

        if ($exists) throw new \RuntimeException('WarehouseTransfer failed to generate a unique code after 25 attempts');

        $this->setAttribute('code', $candidate);
    }

    protected function normalizeCoreFields(): void
    {
        foreach ([BC::COL_PRD_ID, BC::COL_FROM_WRH, BC::COL_TO_WRH] as $k) {
            $v = trim((string) ($this->getAttribute($k) ?? ''));
            if ($v === '' || !Utility::looksLikeUuid($v))
                throw new \InvalidArgumentException("WarehouseTransfer requires a valid {$k}");
            $this->setAttribute($k, $v);
        }

        if ($this->getAttribute(BC::COL_FROM_WRH) === $this->getAttribute(BC::COL_TO_WRH))
            throw new \InvalidArgumentException('WarehouseTransfer requires from_warehouse and to_warehouse to be different');

        $qRaw = $this->getAttribute('quantity');
        $q = is_numeric($qRaw) ? (int) $qRaw : 0;
        $this->setAttribute('quantity', $q < 0 ? 0 : $q);

        foreach ([BC::COL_SVC_FEE, BC::COL_TRP_CST] as $k) {
            $v = $this->getAttribute($k);
            $f = is_numeric($v) ? (float) $v : 0.0;
            $this->setAttribute($k, $f < 0 ? 0.0 : $f);
        }

        $notes = trim((string) ($this->getAttribute('notes') ?? ''));
        $this->setAttribute('notes', $notes === '' ? null : $notes);

        $rej = trim((string) ($this->getAttribute(BC::COL_REJ_RS) ?? ''));
        $this->setAttribute(BC::COL_REJ_RS, $rej === '' ? null : $rej);
    }

    protected function normalizeDatesAndStatus(): void
    {
        $status = $this->getAttribute('status');
        if (is_string($status) || $status instanceof \BackedEnum || $status === null)
            $this->setAttribute('status', EvaluationStatus::normalize($status)?->value ?? EvaluationStatus::Pending->value);

        $ship = $this->getAttribute(BC::COL_SHIP_DT);
        $schd = $this->getAttribute(BC::COL_SCHD_DT);
        $rcv  = $this->getAttribute(BC::COL_RCV_DT);

        if ($ship && $schd && (string) $ship < (string) $schd)
            $this->setAttribute(BC::COL_SHIP_DT, $schd);

        if ($rcv && $ship && (string) $rcv < (string) $ship)
            $this->setAttribute(BC::COL_RCV_DT, $ship);
    }

    protected function normalizeJsonFields(): void
    {
        $this->ensureJsonAttributesAreEncoded(['attachments', 'steps']);

        foreach (['attachments', 'steps'] as $k) {
            $v = $this->getAttribute($k);
            if ($v === '' || $v === []) $this->setAttribute($k, null);
        }
    }

    protected function enforceQuantities(): void
    {
        $status = (string) ($this->getAttribute('status') ?? EvaluationStatus::Pending->value);
        $isCompleted = $status === EvaluationStatus::Completed->value;

        $qty = (int) ($this->getAttribute('quantity') ?? 0);

        $rcvRaw = $this->getAttribute(BC::COL_QTY_RCV);
        $rtnRaw = $this->getAttribute(BC::COL_QTY_RTN);

        $rtn = $rtnRaw === null ? 0 : (is_numeric($rtnRaw) ? (int) $rtnRaw : 0);
        $rcv = $rcvRaw === null ? ($isCompleted ? $qty : 0) : (is_numeric($rcvRaw) ? (int) $rcvRaw : 0);

        $rtn = max(0, min($rtn, $qty));
        $rcv = max(0, min($rcv, $qty));

        if (($rcv + $rtn) > $qty)
            $rcv = max(0, $qty - $rtn);

        $this->setAttribute(BC::COL_QTY_RTN, $rtn);
        $this->setAttribute(BC::COL_QTY_RCV, $rcv);
    }

    protected function enforceRejectionReason(): void
    {
        $rejBy = trim((string) ($this->getAttribute(PJC::COL_REJ_BY) ?? ''));
        if ($rejBy === '') $this->setAttribute(BC::COL_REJ_RS, null);
    }

    protected function mirrorDateFromShipScheduleOrNow(): void
    {
        $ship = $this->getAttribute(BC::COL_SHIP_DT);
        $schd = $this->getAttribute(BC::COL_SCHD_DT);

        if ($ship) {
            $this->setAttribute('date', \Illuminate\Support\Carbon::parse($ship)->toDateString());
            return;
        }

        if ($schd) {
            $this->setAttribute('date', \Illuminate\Support\Carbon::parse($schd)->toDateString());
            return;
        }

        $this->setAttribute('date', now()->toDateString());
    }

    public function productProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, BC::COL_PRD_ID);
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, BC::COL_PRD_ID);
    }

    public function product(): ?BelongsTo
    {
        return Utility::getProduct($this);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, BC::COL_FROM_WRH);
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, BC::COL_TO_WRH);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_REQ_BY);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, BC::COL_APV_BY);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, PJC::COL_REJ_BY);
    }

    public function scopeBetweenWarehouses(Builder $q, string $fromId, string $toId): Builder
    {
        $fromId = trim($fromId);
        $toId = trim($toId);

        return (!$fromId || !$toId) ? $q : $q->where(BC::COL_FROM_WRH, $fromId)->where(BC::COL_TO_WRH, $toId);
    }
}
