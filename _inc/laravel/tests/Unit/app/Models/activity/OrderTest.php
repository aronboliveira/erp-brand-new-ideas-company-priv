<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Order;
use App\Models\UserCoupon;

class OrderTest extends TestCase
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
	 ** Order is mass assignable for all fillable fields
	 **/
	public function order_is_fillable()
	{
		$data = [
			'order_id'       => 'ORD-1001',
			'name'           => 'Acme Corp',
			'email'          => 'billing@acme.test',
			'card_number'    => '4242424242424242',
			'card_exp_month' => 'november',
			'card_exp_year'  => '2030',
			'plan_name'      => 'Pro',
			'plan_id'        => 'plan-pro-01',
			'price'          => 49.99,
			'price_currency' => 'USD',
			'txn_id'         => 'TXN-5001',
			'payment_status' => 'completed',
			'payment_type'   => 'card_credit',
			'receipt'        => 'RCT-9001',
			'user_id'        => 'user-123',
		];

		$order = Order::create($data);

		$this->assertNotNull($order->id);
		$skip = ['txn_id']; // not fillable
		foreach ($data as $field => $value) {
			if (in_array($field, $skip)) continue;
			$raw = $order->getRawOriginal($field);
			$this->assertEquals($value, $raw, "Field {$field} mismatch");
		}
	}

	/**
	 ** @test
	 **
	 ** totalOrders() returns 0 when there are no records
	 **/
	public function totalOrders_returns_zero_when_no_records()
	{
		Order::query()->delete();
		$this->assertSame(0, Order::totalOrders());
	}

	/**
	 ** @test
	 **
	 ** totalOrders() returns the correct count of orders
	 **/
	public function totalOrders_returns_correct_count()
	{
		Order::query()->delete();
		$base = [
			'order_id'       => 'ORD-200',
			'name'           => 'Test',
			'email'          => 't@t.test',
			'card_number'    => '0000',
			'card_exp_month' => 'january',
			'card_exp_year'  => '2031',
			'plan_name'      => 'TestPlan',
			'plan_id'        => 'plan-test',
			'price'          => 10.00,
			'price_currency' => 'USD',
			'txn_id'         => 'TXN-1',
			'payment_status' => 'pending',
			'payment_type'   => 'card_credit',
			'receipt'        => 'RCT-1',
			'user_id'        => 'u1',
		];

		Order::create($base);
		$second = array_merge($base, ['order_id' => 'ORD-201', 'email' => 't2@t.test']);
		Order::create($second);

		$this->assertSame(2, Order::totalOrders());
	}

	/**
	 ** @test
	 **
	 ** totalOrdersPrice() returns 0.0 when there are no records
	 **/
	public function totalOrders_price_returns_zero_when_no_records()
	{
		Order::query()->delete();
		$this->assertSame(0.0, Order::totalOrdersPrice());
	}

	/**
	 ** @test
	 **
	 ** totalOrdersPrice() returns the correct sum of the price column
	 **/
	public function totalOrders_price_returns_correct_sum()
	{
		Order::query()->delete();
		$base = [
			'order_id'       => 'ORD-300',
			'name'           => 'SumTest',
			'email'          => 's@sum.test',
			'card_number'    => '1111',
			'card_exp_month' => 'february',
			'card_exp_year'  => '2032',
			'plan_name'      => 'SumPlan',
			'plan_id'        => 'plan-sum',
			'price'          => 15.25,
			'price_currency' => 'USD',
			'txn_id'         => 'TXN-3',
			'payment_status' => 'completed',
			'payment_type'   => 'card_credit',
			'receipt'        => 'RCT-3',
			'user_id'        => 'u2',
		];

		Order::create($base);
		Order::create(array_merge($base, ['order_id' => 'ORD-301', 'price' => 24.75, 'email' => 's2@sum.test']));

		$this->assertEquals(15.25 + 24.75, Order::totalOrdersPrice());
	}

	/**
	 ** @test
	 **
	 ** totalCouponUsed() relation should point to the UserCoupon model
	 **/
	public function totalCouponUsed_relation_resolves_correctly()
	{
		$relation = (new Order)->totalCouponUsed();

		$this->assertInstanceOf(HasOne::class,      $relation);
		$this->assertSame(UserCoupon::class,        get_class($relation->getRelated()));
		$this->assertSame('order',                  $relation->getForeignKeyName());
		$this->assertSame('order_id',               $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** Order uses UUIDs for its primary key: string, non-incrementing, and valid UUID format
	 **/
	public function order_uses_uuid_for_primary_key()
	{
		$order = Order::create([
			'order_id'       => 'ORD-400',
			'name'           => 'UUIDTest',
			'email'          => 'u@uuid.test',
			'card_number'    => '2222',
			'card_exp_month' => 'march',
			'card_exp_year'  => '2033',
			'plan_name'      => 'UUIDPlan',
			'plan_id'        => 'plan-uuid',
			'price'          => 5.00,
			'price_currency' => 'USD',
			'txn_id'         => 'TXN-5',
			'payment_status' => 'completed',
			'payment_type'   => 'card_credit',
			'receipt'        => 'RCT-5',
			'user_id'        => 'u3',
		]);

		$key = $order->getKey();

		$this->assertIsString($key);
		$this->assertFalse($order->getIncrementing());
		$this->assertSame('string', $order->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
