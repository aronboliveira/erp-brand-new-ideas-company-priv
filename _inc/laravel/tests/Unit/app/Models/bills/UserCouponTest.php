<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{UserCoupon, User, Coupon, Order};

class UserCouponTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** UserCoupon is mass assignable for user, coupon, and order
	 **/
	public function user_coupon_is_fillable()
	{
		$user  = User::factory()->create();
		$coupon = Coupon::factory()->create();
		$order = Order::factory()->create();

		$data = [
			'user'   => $user?->id,
			'coupon' => $coupon->id,
			'order'  => $order->id,
		];

		$uc = UserCoupon::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $uc->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** UserCoupon uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$uc = UserCoupon::factory()->create();
		$key = $uc->getKey();

		$this->assertIsString($key);
		$this->assertFalse($uc->getIncrementing());
		$this->assertSame('string', $uc->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** userDetail() relation should point to App\Models\User via user column
	 **/
	public function user_detail_relation_resolves_to_user_model()
	{
		$relation = (new UserCoupon)->userDetail();

		$this->assertInstanceOf(HasOne::class,   $relation);
		$this->assertSame(User::class,           get_class($relation->getRelated()));
		$this->assertSame('id',                  $relation->getForeignKeyName());
		$this->assertSame('user',                $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** couponDetail() relation should point to App\Models\Coupon via coupon column
	 **/
	public function couponDetail_relation_resolves_to_coupon_model()
	{
		$relation = (new UserCoupon)->couponDetail();

		$this->assertInstanceOf(HasOne::class,   $relation);
		$this->assertSame(Coupon::class,         get_class($relation->getRelated()));
		$this->assertSame('id',                  $relation->getForeignKeyName());
		$this->assertSame('coupon',              $relation->getLocalKeyName());
	}
}
