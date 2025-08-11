<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{PurchasePayment, BankAccount};

class PurchasePaymentTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** PurchasePayment is mass assignable for purchase_id, date, account_id,
	 ** amount, payment_method, reference, description, and add_receipt
	 **/
	public function purchase_payment_is_fillable()
	{
		$account = BankAccount::factory()->create();

		$data = [
			'purchase_id'    => 'purchase-123',
			'date'           => '2025-05-30',
			'account_id'     => $account->id,
			'amount'         => 75.25,
			'payment_method' => 'wire',
			'reference'      => 'REF-789',
			'description'    => 'Partial payment',
			'add_receipt'    => '/receipts/partial.pdf',
		];

		$pp = PurchasePayment::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $pp->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** PurchasePayment uses UUID for its primary key:
	 ** string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$pp = PurchasePayment::factory()->create();
		$key = $pp->getKey();

		$this->assertIsString($key);
		$this->assertFalse($pp->getIncrementing());
		$this->assertSame('string', $pp->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** bankAccount() relation should point to BankAccount via account_id
	 **/
	public function bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new PurchasePayment)->bankAccount();

		$this->assertInstanceOf(HasOne::class,    $relation);
		$this->assertSame(BankAccount::class,     get_class($relation->getRelated()));
		$this->assertSame('id',                   $relation->getForeignKeyName());
		$this->assertSame('account_id',           $relation->getLocalKeyName());
	}
}
