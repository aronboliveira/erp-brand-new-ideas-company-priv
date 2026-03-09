<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{BankAccount, ChartOfAccount};

class BankAccountTest extends TestCase
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
	 ** BankAccount is mass assignable for holder_name, bank_name, account_number,
	 ** chart_account_id, opening_balance, contact_number, and bank_address
	 **/
	public function bank_account_is_fillable()
	{
		$coa = ChartOfAccount::factory()->create();

		$data = [
			'holder_name'      => 'John Doe',
			'bank_name'        => 'Acme Bank',
			'account_number'   => '123456789',
			'chart_account_id' => $coa->id,
			'opening_balance'  => 500.00,
			'contact_number'   => '555-1234',
			'bank_address'     => '123 Main St',
		];

		$acct = BankAccount::create($data);

		$this->assertFillableMatches($data, $acct);
	}

	/**
	 ** @test
	 **
	 ** BankAccount uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function bank_account_uses_uuid_for_primary_key()
	{
		$acct = BankAccount::factory()->create();
		$key = $acct->getKey();

		$this->assertIsString($key);
		$this->assertFalse($acct->getIncrementing());
		$this->assertSame('string', $acct->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** chartAccount() relation should point to ChartOfAccount via chart_account_id
	 **/
	public function chart_account_relation_resolves_to_chart_of_account_model()
	{
		$relation = (new BankAccount)->chartAccount();

		$this->assertInstanceOf(BelongsTo::class,                $relation);
		$this->assertSame(ChartOfAccount::class,              get_class($relation->getRelated()));
		$this->assertSame('chart_account_id',                               $relation->getForeignKeyName());
		$this->assertSame('id',                 $relation->getOwnerKeyName());
	}
}
