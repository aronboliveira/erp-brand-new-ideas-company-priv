<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasOne};

class Order extends Model
{
    use HasFactory;
    use UsesUuids;

    protected $fillable = [
        'order_id', 'name', 'email', 'card_number', 'card_exp_month',
        'card_exp_year', 'plan_name', 'plan_id', 'price', 'price_currency',
        'txn_id', 'payment_status', 'payment_type', 'receipt', 'user_id'
    ];

    public static function totalOrders(): int
    {
        return self::count();
    }

    public static function totalOrdersPrice(): float
    {
        return self::sum('price');
    }

    private const FK_COUPON_ORDER  = 'order';
    private const LOCAL_ORDER_ID   = 'order_id';

    public function totalCouponUsed(): HasOne
    {
        return $this->hasOne(
            UserCoupon::class,
            self::FK_COUPON_ORDER,
            self::LOCAL_ORDER_ID
        );
    }
}
