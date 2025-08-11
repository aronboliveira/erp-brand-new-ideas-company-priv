<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{Revenue, ProductServiceCategory, Customer, BankAccount};

class RevenueTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Revenue is mass assignable for date, amount, account_id, customer_id,
	 ** category_id, recurring, payment_method, reference, description, and created_by
	 **/
	public function revenue_is_fillable()
	{
		$category   = ProductServiceCategory::factory()->create();
		$customer   = Customer::factory()->create();
		$bankAccount = BankAccount::factory()->create();

		$data = [
			'date'            => '2025-05-31',
			'amount'          => 250.75,
			'account_id'      => $bankAccount->id,
			'customer_id'     => $customer->id,
			'category_id'     => $category->id,
			'recurring'       => true,
			'payment_method'  => 'wire',
			'reference'       => 'REF-2025',
			'description'     => 'Monthly revenue',
			'created_by'      => 'user_xyz',
		];

		$revenue = Revenue::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $revenue->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Revenue uses UUID for its primary key:
	 ** string, non-incrementing, valid UUID format
	 **/
	public function revenue_uses_uuid_for_primary_key()
	{
		$revenue = Revenue::factory()->create();
		$key    = $revenue->getKey();

		$this->assertIsString($key);
		$this->assertFalse($revenue->getIncrementing());
		$this->assertSame('string', $revenue->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** category() relation should point to ProductServiceCategory via category_id
	 **/
	public function category_relation_resolves_to_product_service_category_model()
	{
		$relation = (new Revenue)->category();

		$this->assertInstanceOf(HasOne::class,                   $relation);
		$this->assertSame(ProductServiceCategory::class,         get_class($relation->getRelated()));
		$this->assertSame('id',                                  $relation->getForeignKeyName());
		$this->assertSame('category_id',                         $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** customer() relation should point to Customer via customer_id
	 **/
	public function customer_relation_resolves_to_customer_model()
	{
		$relation = (new Revenue)->customer();

		$this->assertInstanceOf(HasOne::class,                   $relation);
		$this->assertSame(Customer::class,                       get_class($relation->getRelated()));
		$this->assertSame('id',                                  $relation->getForeignKeyName());
		$this->assertSame('customer_id',                         $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** bankAccount() relation should point to BankAccount via account_id
	 **/
	public function bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new Revenue)->bankAccount();

		$this->assertInstanceOf(HasOne::class,                   $relation);
		$this->assertSame(BankAccount::class,                    get_class($relation->getRelated()));
		$this->assertSame('id',                                  $relation->getForeignKeyName());
		$this->assertSame('account_id',                          $relation->getLocalKeyName());
	}
}
