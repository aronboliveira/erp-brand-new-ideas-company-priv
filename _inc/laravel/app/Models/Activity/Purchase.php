<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Http\RedirectResponse;

class Purchase extends Model
{
    use HasFactory, UsesUuids, ChecksLogin;

    protected $fillable = [
        'purchase_id',
        'vendor_id',
        'warehouse_id',
        'purchase_date',
        'purchase_number',
        'discount_apply',
        'category_id',
        DatabaseConstants::COL_TABLE_CREATOR,
        'status',
        'shipping_display',
        'send_date',
        'tax_id'
    ];

    public static array $statuses = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partially Paid',
        'Paid'
    ];

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    public function vender(): HasOne // * KEPT FOR COMPATIBILITY, DO NOT USE IN ENDPOINTS
    {
        return $this->vendor();
    }

    public function tax(): HasOne
    {
        return $this->hasOne(Tax::class, 'id', 'tax_id');
    }

    public function taxes(): HasOne
    {
        return $this->hasOne('App\Models\Tax', 'id', 'tax');
    }


    public function items(): HasMany
    {
        return $this->hasMany(PurchaseProduct::class, 'purchase_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id', 'id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(
            ProductServiceCategory::class,
            'id',
            'category_id'
        );
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(fn($p) => $p->price * $p->quantity);
    }

    public function getTotal(): float
    {
        return $this->getSubTotal()
            - $this->getTotalDiscount()
            + $this->getTotalTax();
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn($p) => (Utility::totalTaxRate($p->tax) / 100) *
                ($p->price * $p->quantity - $p->discount)
        );
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum('discount');
    }

    public function getDue(): float
    {
        return $this->getTotal() - $this->payments->sum('amount');
    }

    public function lastPayments(): HasOne
    {
        return $this->hasOne(
            PurchasePayment::class,
            'id',
            'purchase_id'
        );
    }

    public static function totalPurchaseAmount(
        bool $month = false
    ): string {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $query = self::where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId());
        $month && $query->whereRaw('MONTH(created_at)=?', [date('m')]);
        $total = $query->get()->sum(fn($p) => $p->getTotal());
        return $user?->priceFormat($total);
    }

    public static function getPurchaseReportChart(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        $grouped = self::whereDate(
            'created_at',
            '>',
            Carbon::now()->subDays(10)
        )->where(DatabaseConstants::COL_TABLE_CREATOR, $user?->creatorId())
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn($v) => Carbon::parse(
                $v->created_at
            )->format('dm'));
        $now = Carbon::now();
        for ($i = 0; $i <= 9; $i++) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $key = Carbon::parse($date)->format('dm');
            $purchasesArray['label'][] = $date;
            $purchasesArray['value'][] = $grouped[$key]
                ? $grouped[$key]->sum(fn($p) => $p->getTotal())
                : 0;
        }
        return $purchasesArray;
    }
}
