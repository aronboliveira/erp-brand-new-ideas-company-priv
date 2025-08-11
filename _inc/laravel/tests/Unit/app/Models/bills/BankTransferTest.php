<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{BankAccount, BankTransfer, User};

class BankTransferTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** BankTransfer is mass assignable for from_account, to_account, amount, date, payment_method, reference, description, and created_by
	 **/
	public function bank_transfer_is_fillable()
	{
		$from = BankAccount::factory()->create();
		$to  = BankAccount::factory()->create();
		$user = User::factory()->create();

		$data = [
			'from_account'   => $from->id,
			'to_account'     => $to->id,
			'amount'         => 250.75,
			'date'           => '2025-05-26',
			'payment_method' => 'wire',
			'reference'      => 'REF123',
			'description'    => 'Monthly transfer',
			'created_by'     => $user?->id,
		];

		$transfer = BankTransfer::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $transfer->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** BankTransfer uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function bank_transfer_uses_uuid_for_primary_key()
	{
		$transfer = BankTransfer::factory()->create();

		$key = $transfer->getKey();

		$this->assertIsString($key);
		$this->assertFalse($transfer->getIncrementing());
		$this->assertSame('string', $transfer->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** fromBankAccount() relation should point to App\Models\BankAccount via from_account
	 **/
	public function from_bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new BankTransfer)->fromBankAccount();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(BankAccount::class,     get_class($relation->getRelated()));
		$this->assertSame('from_account',         $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** toBankAccount() relation should point to App\Models\BankAccount via to_account
	 **/
	public function to_bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new BankTransfer)->toBankAccount();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(BankAccount::class,     get_class($relation->getRelated()));
		$this->assertSame('to_account',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new BankTransfer)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
