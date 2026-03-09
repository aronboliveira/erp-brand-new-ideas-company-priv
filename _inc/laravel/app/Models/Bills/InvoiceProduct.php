<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{ExtendsProductServiceTable, FiltersSecureAttachments, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};

/**
 * @property float|int|string|null $discount
 * @property float|int|string|null $price
 * @property float|int|string|null $quantity
 * @property bool|null $can_be_charged_back
 * @property bool|null $is_secured
 */
class InvoiceProduct extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use HasFactory;
    use ExtendsProductServiceTable;
    use FiltersSecureAttachments;

    protected $table = DC::TABLE_INV_PRD;

    protected $fillable = [
        BC::COL_INV_ID,
        BC::COL_PRD_ID,
        'quantity',
        'tax',
        'price',
        BC::COL_CUR_ID,
        'discount',
        BC::COL_SVC_FEE,
        BC::COL_IS_SCD,
        BC::COL_CAN_CHG_BK,
        'reference',
        'description',
        'notes',
        BC::COL_TXS_LST,
        'attachments',
        'contract',
        'loan',
        BC::COL_WRH_ID,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $with = [
        'productService'
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'price'             => 'decimal:2',
        'discount'          => 'decimal:2',
        BC::COL_SVC_FEE     => 'decimal:2',
        BC::COL_IS_SCD      => 'boolean',
        BC::COL_CAN_CHG_BK  => 'boolean',
        BC::COL_TXS_LST     => 'array',
        'attachments'       => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->quantity) || $model->quantity < 1)
                $model->quantity = 1;
            if ($model->discount === null)
                $model->discount = 0.0;
        });
        static::saving(function (self $model): void {
            if ($model->quantity < 1)
                $model->quantity = 1;
            if ($model->discount === null || $model->discount < 0)
                $model->discount = 0.0;
            $maxDiscount = $model->getLineSubtotal();
            if ($model->discount > $maxDiscount)
                $model->discount = $maxDiscount;
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, BC::COL_INV_ID);
    }

    public function productProduct(): ?BelongsTo
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

    public function getLineSubtotal(): float
    {
        return (float) $this->quantity * (float) $this->price;
    }

    public function getLineDiscountAmount(): float
    {
        return (float) $this->discount;
    }

    public function getLineTotal(): float
    {
        $total = $this->getLineSubtotal() - $this->getLineDiscountAmount();

        return $total > 0 ? $total : 0.0;
    }

    public function hasDiscount(): bool
    {
        return (float) $this->discount > 0;
    }

    public function isSecured(): bool
    {
        return (bool) $this->{BC::COL_IS_SCD};
    }

    public function canBeChargedBack(): bool
    {
        return (bool) $this->{BC::COL_CAN_CHG_BK};
    }
}
