<?php

namespace App\Models;

use App\Config\Constants\{
    BanksConstants as BKC,
    BillsConstants as BC,
    DatabaseConstants as DC
};
use App\Models\Bill;
use App\Models\ChartOfAccount;
use App\Models\ProductService;
use App\Models\Tax;
use App\Models\User;
use Database\Factories\BillProductFactory;
use App\Traits\{
    ExtendsProductServiceTable,
    FiltersSecureAttachments,
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

final class BillProduct extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use ExtendsProductServiceTable;
    use NormalizesArrays;
    use FiltersSecureAttachments;

    protected static function newFactory(): BillProductFactory
    {
        return BillProductFactory::new();
    }

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
                    if (!empty($m->getAttribute($field)) && is_string($m->getAttribute($field)))
                        $m->setAttribute($field, trim($m->getAttribute($field)));
                if (!is_numeric($m->getAttribute('quantity')) || (int) $m->getAttribute('quantity') < 1)
                    $m->setAttribute('quantity', 1);
                else
                    $m->setAttribute('quantity', (int) $m->getAttribute('quantity'));
                if (!is_numeric($m->getAttribute('discount')) || $m->getAttribute('discount') < 0)
                    $m->setAttribute('discount', 0.0);
                if (!is_numeric($m->getAttribute('total')) || $m->getAttribute('total') < 0)
                    $m->setAttribute('total', 0.0000);
                $m->setAttribute(BC::COL_OT_TX, self::normalizeArrayField($m->getAttribute(BC::COL_OT_TX) ?? null));
                $m->setAttribute('attachments', self::normalizeArrayField($m->getAttribute('attachments') ?? null));
                $m->setAttribute('metadata', self::normalizeArrayField($m->getAttribute('metadata') ?? null));
                if ($m->getAttribute(BC::COL_BL_ID))
                    $m->setAttribute(BC::COL_OT_TX, self::filterOtherTaxesAgainstBill(
                        $m->getAttribute(BC::COL_OT_TX),
                        $m->getAttribute(BC::COL_BL_ID)
                    ));
            } catch (\Throwable $e) {
                Log::warning(self::class . '::saving normalization failed', [
                    'id'    => $m->id ?? null,
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
                if (!is_array($m->getAttribute(BC::COL_OT_TX) ?? null))
                    $m->setAttribute(BC::COL_OT_TX, []);
                if (!is_array($m->getAttribute('attachments') ?? null))
                    $m->setAttribute('attachments', []);
                if (!is_array($m->getAttribute('metadata') ?? null))
                    $m->setAttribute('metadata', []);
            }
        });
    }

    protected static function filterOtherTaxesAgainstBill(array $otherTaxes, string $billId): array
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error(static::class . '::filterOtherTaxesAgainstBill — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
        // * considerar belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id')
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'product_id', 'id');
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_account_id', 'id');
    }
}
