<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{PosPayment, Pos, BankAccount};

use Illuminate\Support\Facades\DB;
class PosPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** PosPayment is mass assignable for pos_id, date, amount, discount, discount_amount, account_id, and created_by
	 **/
	public function pos_payment_is_fillable()
	{
		$pos    = Pos::factory()->create();
		$account = BankAccount::factory()->create();

		$data = [
			'pos_id'           => $pos->id,
			'date'             => '2025-05-29',
			'amount'           => 100.00,
			'discount'         => 10.00,
			'discount_amount'  => 5.00,
			'account_id'       => $account->id,
			'created_by'       => 'user123',
		];

		$pp = PosPayment::create($data);

		$this->assertFillableMatches($data, $pp);
	}

	/**
	 ** @test
	 **
	 ** Primary key is a non-incrementing UUID string
	 **/
	public function primary_key_is_uuid()
	{
		$pp = PosPayment::factory()->create();
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
	 ** bankAccount() relation should point to App\Models\BankAccount via account_id
	 **/
	public function bank_account_relation_resolves_to_bank_account_model()
	{
		$relation = (new PosPayment)->bankAccount();

		$this->assertInstanceOf(BelongsTo::class,          $relation);
		$this->assertSame(BankAccount::class,           get_class($relation->getRelated()));
		$this->assertSame('account_id',                         $relation->getForeignKeyName());
		$this->assertSame('id',                 $relation->getOwnerKeyName());
	}
}
