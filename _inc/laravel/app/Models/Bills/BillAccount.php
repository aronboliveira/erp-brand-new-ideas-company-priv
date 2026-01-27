<?php

namespace App\Models;

use App\Config\Constants\{BanksConstants as BKC, BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\BillReferenceType;
use App\Models\Utility;
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{
    Builder,
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Support\Facades\{DB, Log, Schema};

class BillAccount extends Model
{
    use UsesUuids, HasAuditFields, HasFactory, NormalizesArrays, FiltersSecureAttachments;

    protected $table = DC::TABLE_BL_ACC;
    protected $primaryKey = 'id';

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $fillable = [
        BKC::COL_COA,
        BC::COL_REF_ID,
        'type',
        'price',
        'description',
        'notes',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'type' => BillReferenceType::class,
        'price' => 'decimal:2',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'attachments_count',
        'has_attachments',
        'metadata_keys',
    ];

    protected static array $billAmountCache = [];
    protected static array $colHasCache = [];

    protected static function booted(): void
    {
        static::addGlobalScope(DC::ORDER_NEW, function (Builder $builder) {
            $table = (new static)->getTable();
            $col = Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id';
            $builder->orderBy($col, 'desc');
        });

        static::saving(function (self $m): void {
            try {
                $m->normalizeCoreFields();
                $m->ensureJsonAttributesAreEncoded(['attachments', 'metadata']);
                $m->overwritePriceFromBillAmountIfPresent();
            } catch (\Throwable $e) {
                Log::error(static::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'table' => $m->getTable(),
                    'model_id' => $m->getKey(),
                ]);
            }
        });
    }

    public function bill(): BelongsTo
    {
        $cls = self::resolveBillModelClass();
        return $this->belongsTo($cls, BC::COL_REF_ID, 'id');
    }

    public function chartAccount(): BelongsTo
    {
        $cls = self::resolveChartAccountModelClass();
        return $this->belongsTo($cls, BKC::COL_COA, 'id');
    }

    public function setAttachmentsAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('attachments', $value);
    }

    public function setMetadataAttribute(mixed $value): void
    {
        $this->encodeJsonAttribute('metadata', $value);
    }

    public function getAttachmentsCountAttribute(): int
    {
        return count(self::normalizeArrayField($this->getAttribute('attachments')));
    }

    public function getHasAttachmentsAttribute(): bool
    {
        return $this->getAttachmentsCountAttribute() > 0;
    }

    public function getMetadataKeysAttribute(): array
    {
        $meta = $this->getAttribute('metadata');
        $arr = is_array($meta) ? $meta : (array) $meta;
        $keys = array_keys($arr);
        $keys = array_values(array_filter($keys, fn($k) => is_string($k) && trim($k) !== ''));
        return $keys;
    }

    public function scopeForBill(Builder $q, string $billId): Builder
    {
        return $q->where(BC::COL_REF_ID, $billId);
    }

    public function scopeForChartAccount(Builder $q, string $coaId): Builder
    {
        return $q->where(BKC::COL_COA, $coaId);
    }

    public static function sumForBill(string $billId): string
    {
        try {
            return (string) DB::table(DC::TABLE_BL_ACC)->where(BC::COL_REF_ID, $billId)->sum('price');
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed summing bill accounts for bill', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'bill_id' => $billId,
            ]);
            return '0.00';
        }
    }

    protected function normalizeCoreFields(): void
    {
        $desc = $this->getAttribute('description');
        if (is_string($desc)) {
            $desc = trim($desc);
            $this->setAttribute('description', $desc !== '' ? $desc : null);
        }

        $notes = $this->getAttribute('notes');
        if (is_string($notes)) {
            $notes = trim($notes);
            $this->setAttribute('notes', $notes !== '' ? $notes : null);
        }

        $rawType = $this->getAttribute('type');
        $enum = BillReferenceType::normalize($rawType) ?? BillReferenceType::Bill;
        $this->setAttribute('type', $enum->value);

        $rawCoa = $this->getAttribute(BKC::COL_COA);
        if (is_scalar($rawCoa)) $this->setAttribute(BKC::COL_COA, trim((string) $rawCoa));

        $rawRef = $this->getAttribute(BC::COL_REF_ID);
        if (is_scalar($rawRef)) $this->setAttribute(BC::COL_REF_ID, trim((string) $rawRef));

        $rawPrice = $this->getAttribute('price');
        $normalized = self::normalizeNumeric($rawPrice);
        if ($normalized !== null) $this->setAttribute('price', $normalized);
    }

    protected function overwritePriceFromBillAmountIfPresent(): void
    {
        if (!self::hasColumnCached(DC::TABLE_BILLS, 'amount')) return;

        $billIdRaw = $this->getAttribute(BC::COL_REF_ID);
        $billId = is_scalar($billIdRaw) ? trim((string) $billIdRaw) : '';
        if ($billId === '' || !Utility::looksLikeUuid($billId)) return;

        $amount = self::billAmountCached($billId);
        $normalized = self::normalizeNumeric($amount);
        if ($normalized === null) return;

        $this->setAttribute('price', $normalized);
    }

    protected static function billAmountCached(string $billId): mixed
    {
        if (array_key_exists($billId, self::$billAmountCache)) return self::$billAmountCache[$billId];

        try {
            return self::$billAmountCache[$billId] = DB::table(DC::TABLE_BILLS)->where('id', $billId)->value('amount');
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed fetching bill amount for overwrite', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'bill_id' => $billId,
            ]);
            return self::$billAmountCache[$billId] = null;
        }
    }

    protected static function normalizeNumeric(mixed $value): ?string
    {
        if ($value === null) return null;

        if (is_int($value) || is_float($value)) return number_format((float) $value, 2, '.', '');

        if (!is_string($value)) return null;

        $v = trim($value);
        if ($v === '') return null;

        $vv = str_replace([' ', "\t", "\n", "\r"], '', $v);
        $vv = str_replace(',', '.', $vv);

        if (is_numeric($vv)) return number_format((float) $vv, 2, '.', '');

        $vv = preg_replace('/[^0-9\.\-]/', '', $vv);
        return is_numeric($vv) ? number_format((float) $vv, 2, '.', '') : null;
    }

    protected static function hasColumnCached(string $table, string $col): bool
    {
        $k = $table . '::' . $col;
        if (array_key_exists($k, self::$colHasCache)) return self::$colHasCache[$k];

        try {
            return self::$colHasCache[$k] = Schema::hasColumn($table, $col);
        } catch (\Throwable) {
            return self::$colHasCache[$k] = false;
        }
    }

    protected static function resolveBillModelClass(): string
    {
        foreach (
            [
                Bill::class,
                Bill::class,
            ] as $cls
        ) if (class_exists($cls)) return $cls;

        return Model::class;
    }

    protected static function resolveChartAccountModelClass(): string
    {
        foreach (
            [
                ChartOfAccount::class,
            ] as $cls
        ) if (class_exists($cls)) return $cls;

        return Model::class;
    }
}
