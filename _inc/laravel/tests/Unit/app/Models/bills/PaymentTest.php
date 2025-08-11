<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{Payment, ProductServiceCategory, Vendor, BankAccount, ChartOfAccount};

class PaymentTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Payment is mass assignable for date, amount, account_id, chart_account_id,
	 ** vendor_id, description, category_id, payment_method, reference, and created_by
	 **/
	public function payment_is_fillable()
	{
		$category     = ProductServiceCategory::factory()->create();
		$vendor       = Vendor::factory()->create();
		$bankAccount  = BankAccount::factory()->create();
		$chartAccount = ChartOfAccount::factory()->create();

		$data = [
			'date'              => '2025-05-30',
			'amount'            => 100.50,
			'account_id'        => $bankAccount->id,
			'chart_account_id'  => $chartAccount->id,
			'vendor_id'         => $vendor->id,
			'description'       => 'Payment description',
			'category_id'       => $category->id,
			'payment_method'    => 'credit_card',
			'reference'         => 'REF123',
			'created_by'        => 'user1',
		];

		$payment = Payment::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $payment->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Payment uses UUIDs for its primary key:
	 ** string, non-incrementing, valid UUID format
	 **/
	public function payment_uses_uuid_for_primary_key()
	{
		$payment = Payment::factory()->create();
		$key    = $payment->getKey();

		$this->assertIsString($key);
		$this->assertFalse($payment->getIncrementing());
		$this->assertSame('string', $payment->getKeyType());
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
		$relation = (new Payment)->category();

		$this->assertInstanceOf(HasOne::class,                          $relation);
		$this->assertSame(ProductServiceCategory::class,                get_class($relation->getRelated()));
		$this->assertSame('id',                                         $relation->getForeignKeyName());
		$this->assertSame('category_id',                                $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** vendor() relation should point to Vendor via vendor_id
	 **/
	public function vendor_relation_resolves_to_vendor_model()
	{
		$relation = (new Payment)->vendor();

		$this->assertInstanceOf(HasOne::class,                          $relation);
		$this->assertSame(Vendor::class,                                get_class($relation->getRelated()));
		$this->assertSame('id',                                         $relation->getForeignKeyName());
		$this->assertSame('vendor_id',                                  $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** bankAccount() relation should point to BankAccount via account_id
	 **/
	public function bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new Payment)->bankAccount();

		$this->assertInstanceOf(HasOne::class,                          $relation);
		$this->assertSame(BankAccount::class,                           get_class($relation->getRelated()));
		$this->assertSame('id',                                         $relation->getForeignKeyName());
		$this->assertSame('account_id',                                 $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** chartAccount() relation should point to ChartOfAccount via chart_account_id
	 **/
	public function chart_account_relation_resolves_to_chart_of_account_model()
	{
		$relation = (new Payment)->chartAccount();

		$this->assertInstanceOf(HasOne::class,                          $relation);
		$this->assertSame(ChartOfAccount::class,                        get_class($relation->getRelated()));
		$this->assertSame('id',                                         $relation->getForeignKeyName());
		$this->assertSame('chart_account_id',                           $relation->getLocalKeyName());
	}
}
