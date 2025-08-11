<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use Illuminate\Support\Facades\{Auth, DB};

class ProductService extends Model
{
    use ChecksLogin, UsesUuids;

    private const COL_CATEGORY_ID            = 'category_id';
    private const COL_CREATED_BY             = DatabaseConstants::TABLE_CREATOR;
    private const COL_EXPENSE_CHARTACCOUNT_ID = 'expense_chartaccount_id';
    private const COL_NAME                   = 'name';
    private const COL_PURCHASE_PRICE         = 'purchase_price';
    private const COL_SALE_CHARTACCOUNT_ID   = 'sale_chartaccount_id';
    private const COL_SALE_PRICE             = 'sale_price';
    private const COL_SKU                    = 'sku';
    private const COL_TAX_ID                 = 'tax_id';
    private const COL_TYPE                   = 'type';
    private const COL_UNIT_ID                = 'unit_id';

    private const FILLABLE = [
        self::COL_NAME,
        self::COL_SKU,
        self::COL_SALE_PRICE,
        self::COL_PURCHASE_PRICE,
        self::COL_TAX_ID,
        self::COL_CATEGORY_ID,
        self::COL_UNIT_ID,
        self::COL_TYPE,
        self::COL_SALE_CHARTACCOUNT_ID,
        self::COL_EXPENSE_CHARTACCOUNT_ID,
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public function taxes(): HasOne
    {
        return $this->hasOne(Tax::class, 'id', self::COL_TAX_ID);
        // * consider belongsTo(Tax::class, self::COL_TAX_ID, 'id')
    }

    public function unit(): HasOne
    {
        return $this->hasOne(ProductServiceUnit::class, 'id', self::COL_UNIT_ID);
        // * consider belongsTo(ProductServiceUnit::class, self::COL_UNIT_ID, 'id')
    }

    public function category(): HasOne
    {
        return $this->hasOne(ProductServiceCategory::class, 'id', self::COL_CATEGORY_ID);
        // * consider belongsTo(ProductServiceCategory::class, self::COL_CATEGORY_ID, 'id')
    }

    public function tax(string $taxes): array
    {
        $ids = explode(',', $taxes);
        return array_map(fn ($id) => Tax::find($id), $ids);
    }

    public function taxRate(string $taxes): float
    {
        return array_reduce(
            explode(',', $taxes),
            fn ($sum, $id) => $sum + optional(Tax::find($id))->rate,
            0.0
        );
    }

    public static function taxData(string $taxes): string
    {
        $names = array_map(
            fn ($id) => optional(Tax::find($id))->name ?: '',
            explode(',', $taxes)
        );
        return implode(',', $names);
    }

    public static function getAllProducts()
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return self::select(DatabaseConstantS::TABLE_PROD_SERVS . '.*', 'c.name as categoryname')
            ->where(DatabaseConstantS::TABLE_PROD_SERVS . '.type', 'product')
            ->leftJoin(
                'product_service_categories as c',
                'c.id',
                DatabaseConstantS::TABLE_PROD_SERVS . '.category_id'
            )
            ->where(DatabaseConstantS::TABLE_PROD_SERVS . '.' . DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->orderByDesc(DatabaseConstantS::TABLE_PROD_SERVS . '.id');
    }

    public function getTotalProductQuantity(): float
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $userId   = $user?->creatorId();
        $pid      = $this->id;
        $purchases = \App\Models\Purchase::where(DatabaseConstants::TABLE_CREATOR, $userId);
        if ($user?->isUser())
            $purchases->where('warehouse_id', $user?->warehouse_id);
        $purchasedQty = $purchases->get()->sum(fn ($p) => optional(
            \App\Models\PurchaseProduct::where('purchase_id', $p->id)
                ->where('product_id', $pid)
                ->first()
        )->quantity ?: 0);
        $poses = \App\Models\Pos::where(DatabaseConstants::TABLE_CREATOR, $userId);
        if ($user?->isUser())
            $poses->where('warehouse_id', $user?->warehouse_id);
        $posQty = $poses->get()->sum(fn ($p) => optional(
            \App\Models\PosProduct::where('pos_id', $p->id)
                ->where('product_id', $pid)
                ->first()
        )->quantity ?: 0);
        return $purchasedQty - $posQty;
        // * consider caching or eager loading for performance
    }

    public static function taxId(int $productId): int
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return DB::table('product_services')
            ->where('id', $productId)
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->value('tax_id') ?: 0;
    }

    public function warehouseProduct(string $productId, string $warehouseId): float
    {
        $wp = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        return $wp->quantity ?? 0;
    }
}
