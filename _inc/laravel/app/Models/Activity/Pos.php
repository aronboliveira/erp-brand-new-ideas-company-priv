<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Pos extends Model
{
    use HasFactory, UsesUuids, ChecksLogin;

    protected $fillable = [
        'pos_id',
        'customer_id',
        'warehouse_id',
        'pos_date',
        'category_id',
        'status',
        'shipping_display',
        'created_by',
        'tax'
    ];

    private const COL_CREATED_BY   = 'created_by';
    private const COL_CUSTOMER_ID  = 'customer_id';
    private const COL_POS_ID       = 'pos_id';
    private const COL_WAREHOUSE_ID = 'warehouse_id';

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, self::COL_CUSTOMER_ID, 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosProduct::class, self::COL_POS_ID, 'id');
    }

    public function posPayment(): HasOne
    {
        return $this->hasOne(PosPayment::class, self::COL_POS_ID, 'id');
    }

    public function taxes(): HasOne
    {
        return $this->hasOne(Tax::class, 'id', 'tax');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class, self::COL_WAREHOUSE_ID, 'id');
    }

    public function getSubTotal(): float
    {
        return $this->items->sum(fn($p) => $p->price * $p->quantity);
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum('discount');
    }

    public function getTotalTax(): float
    {
        return $this->items->sum(
            fn($p) => (Utility::totalTaxRate($p->tax) / 100) *
                ($p->price * $p->quantity)
        );
    }

    public function getTotal(): float
    {
        return $this->getSubTotal()
            - $this->getTotalDiscount()
            + $this->getTotalTax();
    }

    public static function totalPosAmount(bool $month = false): string
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $query = self::where(self::COL_CREATED_BY, $user?->creatorId());
        $month && $query->whereRaw('MONTH(created_at)=?', [date('m')]);
        $total = $query->get()->sum(fn($p) => $p->getTotal());
        return $user?->priceFormat($total);
    }

    public static function getPosReportChart(): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $grouped = self::whereDate(
            'created_at',
            '>',
            Carbon::now()->subDays(10)
        )->where(self::COL_CREATED_BY, $user?->creatorId())
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn($v) => Carbon::parse(
                $v->created_at
            )->format('dm'));

        $now = Carbon::now();
        for ($i = 0; $i <= 9; $i++) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $key = Carbon::parse($date)->format('dm');
            $posesArray['label'][] = $date;
            $posesArray['value'][] = $grouped[$key]
                ? $grouped[$key]->sum(fn($p) => $p->getTotal())
                : 0;
        }

        return $posesArray;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_id')
            ->where('payment_type', TransactionType::Pos);
    }
}
