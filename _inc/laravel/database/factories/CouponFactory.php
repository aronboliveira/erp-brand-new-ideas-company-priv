<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        // coupons.code is UNIQUE NOT NULL — generate a fresh code per
        // factory call so concurrent factories (and leaked rows) don't
        // collide.
        return [
            'code'     => 'COUP_' . strtoupper(Str::random(8)),
            'discount' => 0.00,
        ];
    }
}
