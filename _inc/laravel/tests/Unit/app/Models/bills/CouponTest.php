<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasMany,
	Foundation\Testing\RefreshDatabase,
};
use App\Models\{Coupon, UserCoupon};

class CouponTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Coupon is mass assignable for name, code, discount, limit, description, and is_active
	 **/
	public function coupon_is_fillable()
	{
		// uniqid suffix — coupons.code is UNIQUE.
		$data = [
			'name'        => 'Spring Sale',
			'code'        => 'SPRING2025_' . uniqid(),
			'discount'    => 15.5,
			'limit'       => 100,
			'description' => '15.5% off spring items',
			'is_active'   => true,
		];

		$coupon = Coupon::create($data);

		$this->assertFillableMatches($data, $coupon);
	}

	/**
	 ** @test
	 **
	 ** Coupon uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function coupon_uses_uuid_for_primary_key()
	{
		$coupon = Coupon::factory()->create();

		$key = $coupon->getKey();

		$this->assertIsString($key);
		$this->assertFalse($coupon->getIncrementing());
		$this->assertSame('string', $coupon->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** userCoupons() relation should point to UserCoupon model
	 **/
	public function user_coupons_relation_resolves_correctly()
	{
		$relation = (new Coupon)->userCoupons();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(UserCoupon::class,     get_class($relation->getRelated()));
		$this->assertSame('coupon',             $relation->getForeignKeyName());
		$this->assertSame('id',                 $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** usedCoupon() returns the count of related UserCoupon records
	 **/
	public function usedCoupon_returns_correct_count()
	{
		$coupon = Coupon::factory()->create();
		UserCoupon::factory()->count(3)->create(['coupon' => $coupon->id]);

		$this->assertSame(3, $coupon->used_coupon());
	}
}
