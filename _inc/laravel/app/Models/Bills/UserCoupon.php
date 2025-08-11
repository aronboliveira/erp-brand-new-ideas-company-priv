<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

class UserCoupon extends Model
{
    use UsesUuids;

    private const COL_COUPON = 'coupon';
    private const COL_ORDER = 'order';
    private const COL_USER  = 'user';

    protected $fillable = [
        self::COL_USER,
        self::COL_COUPON,
        self::COL_ORDER,
    ];

    public function userDetail(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_USER);
        // * consider using belongsTo(User::class, self::COL_USER)
    }

    public function couponDetail(): HasOne
    {
        return $this
            ->hasOne(Coupon::class, 'id', self::COL_COUPON);
        // * consider using belongsTo(Coupon::class, self::COL_COUPON)
    }
}
