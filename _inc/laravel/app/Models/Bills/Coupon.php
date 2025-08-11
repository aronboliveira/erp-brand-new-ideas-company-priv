<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use UsesUuids;

    private const FK_COUPON = 'coupon';

    protected $fillable = [
        'name',
        'code',
        'discount',
        'limit',
        'description',
        'is_active', // * ADDED
    ];

    public function usedCoupon(): int
    {
        return $this
            ->hasMany(UserCoupon::class, self::FK_COUPON, 'id')
            ->count();
    }
}
