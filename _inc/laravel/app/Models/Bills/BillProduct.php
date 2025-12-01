<?php

namespace App\Models;

use App\Config\Constants\{
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Models\User;
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasOne
};
use Illuminate\Support\Facades\Log;

class BillProduct extends Model
{
    use HasAuditFields;
    use HasFactory;
    use NormalizesArrays;
    use UsesUuids;

    protected $fillable = [
        BC::COL_BL_ID,
        BC::COL_PRD_ID,
        BKC::COL_COA,
        'quantity',
        'discount',
        'total',
        'tax',
        BC::COL_TAX_ID,
        BC::COL_OT_TX,
        'description',
        'attachments',
        'metadata',
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        BC::COL_BL_ID  => 'string',
        BC::COL_PRD_ID => 'string',
        BKC::COL_COA   => 'string',
        'quantity'     => 'integer',
        'discount'     => 'float',
        'total'        => 'decimal:4',
        BC::COL_TAX_ID => 'string',
        BC::COL_OT_TX  => 'array',
        'attachments'  => 'array',
        'metadata'     => 'array',
    ];

    protected $with = [
        'user',
        'bill',
        'productService',
        'chartOfAccount',
        'tax',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            try {
                foreach (['tax', 'description'] as $field)
                    if (isset($m->{$field}) && is_string($m->{$field}))
                        $m->{$field} = trim($m->{$field});

                if (!is_numeric($m->quantity) || (int) $m->quantity < 1)
                    $m->quantity = 1;
                else
                    $m->quantity = (int) $m->quantity;

                if (!is_numeric($m->discount) || $m->discount < 0)
                    $m->discount = 0.0;

                if (!is_numeric($m->total) || $m->total < 0)
                    $m->total = 0.0000;

                $m->{BC::COL_OT_TX} = self::normalizeArrayField($m->{BC::COL_OT_TX} ?? null);
                $m->attachments     = self::normalizeArrayField($m->attachments ?? null);
                $m->metadata        = self::normalizeArrayField($m->metadata ?? null);

                if ($m->{BC::COL_BL_ID})
                    $m->{BC::COL_OT_TX} = self::filterOtherTaxesAgainstBill(
                        $m->{BC::COL_OT_TX},
                        $m->{BC::COL_BL_ID}
                    );
            } catch (\Throwable $e) {
                Log::warning(self::class . '::saving normalization failed', [
                    'id'    => $m->id ?? null,
                    'error' => $e->getMessage(),
                ]);

                if (!is_array($m->{BC::COL_OT_TX} ?? null))
                    $m->{BC::COL_OT_TX} = [];

                if (!is_array($m->attachments ?? null))
                    $m->attachments = [];

                if (!is_array($m->metadata ?? null))
                    $m->metadata = [];
            }
        });
    }

    protected static function filterOtherTaxesAgainstBill(array $otherTaxes, string $billId): array
    {
        if (!$otherTaxes)
            return [];

        try {
            /** @var \App\Models\Bill|null $bill */
            $bill = Bill::find($billId);
            if (!$bill)
                return [];

            $billTaxes = is_array($bill->taxes) ? $bill->taxes : [];
            $allowed   = [];

            foreach ($billTaxes as $t) {
                if (!is_array($t))
                    continue;

                $value = $t['id']
                    ?? $t['tax_id']
                    ?? $t['key']
                    ?? null;

                if (is_string($value))
                    $value = trim($value);

                if ($value === null || $value === '')
                    continue;

                $allowed[(string) $value] = true;
            }

            if (!$allowed)
                return [];

            $valid = [];

            foreach ($otherTaxes as $item) {
                if (!is_array($item))
                    continue;

                $value = $item['id']
                    ?? $item['tax_id']
                    ?? $item['key']
                    ?? null;

                if (is_string($value))
                    $value = trim($value);

                if ($value === null || $value === '')
                    continue;

                if (!isset($allowed[(string) $value]))
                    continue;

                $valid[] = $item;
            }

            return $valid;
        } catch (\Throwable $e) {
            Log::warning(self::class . '::filterOtherTaxesAgainstBill failed', [
                'bill_id' => $billId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, BC::COL_BL_ID, 'id');
    }

    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, BC::COL_PRD_ID, 'id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, BKC::COL_COA, 'id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::COL_TABLE_CREATOR);
        // * considerar belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id')
    }
}
