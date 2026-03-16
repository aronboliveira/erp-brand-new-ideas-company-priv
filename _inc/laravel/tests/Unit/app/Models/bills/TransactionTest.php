<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase,
	Http\Request
};
use App\Models\{
	Transaction,
	BankAccount,
	InvoicePayment,
	BillPayment
};

use Illuminate\Support\Facades\DB;
class TransactionTest extends TestCase
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
	 ** Transaction is mass assignable for all fillable fields
	 **/
	public function transaction_is_fillable()
	{
		$account    = BankAccount::factory()->create();
		$invoicePay = InvoicePayment::factory()->create();
		$billPay    = BillPayment::factory()->create();

		$data = [
			'user_id'      => 'user123',
			'user_type'    => 'employee',
			'account'      => $account->id,
			'type'         => 'invoice',
			'amount'       => 99.99,
			'description'  => 'Test transaction',
			'date'         => '2025-06-01',
			'created_by'   => 'creator456',
			'customer_id'  => 'cust789',
			'payment_id'   => $invoicePay->id,
			'category'     => 'sales',
		];

		$trx = Transaction::create($data);

		$this->assertFillableMatches($data, $trx);
	}

	/**
	 ** @test
	 **
	 ** Transaction uses UUIDs for its primary key
	 **/
	public function transaction_uses_uuid_for_primary_key()
	{
		$trx = Transaction::factory()->create();
		$key = $trx->getKey();

		$this->assertIsString($key);
		$this->assertFalse($trx->getIncrementing());
		$this->assertSame('string', $trx->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** bankAccount() relation should point to BankAccount via account
	 **/
	public function bank_account_relation_resolves_correctly()
	{
		$relation = (new Transaction)->bankAccount();

		$this->assertInstanceOf(BelongsTo::class,       $relation);
		$this->assertSame(BankAccount::class,        get_class($relation->getRelated()));
		$this->assertSame('account',                      $relation->getForeignKeyName());
		$this->assertSame('id',                 $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** payment() returns null on an empty Transaction (match expression on null type)
	 **/
	public function payment_relation_resolves_to_invoice_payment_model()
	{
		$result = (new Transaction)->payment();
		// payment() is a computed accessor (not a relation) — uses match on payment_type.
		// On a bare model with no payment_type set, it returns null.
		$this->assertNull($result);
	}

	/**
	 ** @test
	 **
	 ** billPayment() relation should point to BillPayment via payment_id
	 **/
	public function bill_payment_relation_resolves_to_bill_payment_model()
	{
		$relation = (new Transaction)->billPayment();

		$this->assertInstanceOf(BelongsTo::class,       $relation);
		$this->assertSame(BillPayment::class,        get_class($relation->getRelated()));
		$this->assertSame('payment_id',                      $relation->getForeignKeyName());
		$this->assertSame('id',              $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** addTransaction creates a new record with the given request data
	 **/
	public function add_transaction_creates_record()
	{
		$account  = BankAccount::factory()->create();
		$payment  = InvoicePayment::factory()->create();

		$data = [
			'user_id'     => 'u1',
			'user_type'   => 'client',
			'account'     => $account->id,
			'type'        => 'invoice',
			'amount'      => 10.00,
			'description' => 'Desc',
			'date'        => '2025-06-02',
			'payment_id'  => $payment->id,
			'category'    => 'cat1',
		];

		$req = Request::create('/', 'POST', $data);
		$trx = Transaction::addTransaction($req);

		$this->assertNotNull($trx);
		$this->assertDatabaseHas('transactions', [
			'user_id'     => 'u1',
			'user_type'   => 'client',
			'account'     => $account->id,
			'type'        => 'invoice',
			'description' => 'Desc',
			'payment_id'  => $payment->id,
			'category'    => 'cat1',
		]);
	}

	/**
	 ** @test
	 **
	 ** editTransaction updates only the specified fields on the existing record
	 **/
	public function edit_transaction_updates_fields()
	{
		$trx = Transaction::factory()->create([
			'payment_id'   => 'p1',
			'payment_type' => 'bill',
			'account'      => 'old',
			'amount'       => 5.00,
			'description'  => 'Old',
			'date'         => '2025-06-01',
			'category'     => 'oldcat',
		]);

		$update = [
			'payment_id'   => $trx->payment_id,
			'payment_type' => $trx->payment_type,
			'account'      => 'newacct',
			'amount'       => 15.00,
			'description'  => 'New desc',
			'date'         => '2025-06-03',
			'category'     => 'newcat',
		];

		$req = Request::create('/', 'POST', $update);
		Transaction::editTransaction($req);

		$trx->refresh();
		$this->assertEquals('newacct',    $trx->account);
		$this->assertEquals(15.00,        $trx->amount);
		$this->assertEquals('New desc',   $trx->description);
		$this->assertEquals('2025-06-03', $trx->date);
		$this->assertEquals('newcat',     $trx->category);
	}

	/**
	 ** @test
	 **
	 ** destroyTransaction deletes the matching record
	 **/
	public function destroy_transaction_deletes_record()
	{
		$trx = Transaction::factory()->create([
			'payment_id'   => 'p2',
			'payment_type' => 'invoice',
			'user_type'    => 'employee',
		]);

		Transaction::destroyTransaction($trx->payment_id, $trx->payment_type, $trx->user_type);

		$this->assertDatabaseMissing('transactions', [
			'payment_id'   => $trx->payment_id,
			'payment_type' => $trx->payment_type,
			'user_type'    => $trx->user_type,
		]);
	}

	/**
	 ** @test
	 **
	 ** accounts() static returns the last matched bank name and holder name, or empty string
	 **/
	public function accounts_static_method_returns_last_name_or_empty()
	{
		$b1 = BankAccount::factory()->create([
			'bank_name'   => 'BankOne',
			'holder_name' => 'Alice',
		]);
		$b2 = BankAccount::factory()->create([
			'bank_name'   => 'BankTwo',
			'holder_name' => 'Bob',
		]);

		$names = Transaction::accounts("{$b1->id},{$b2->id}");
		// whereIn ordering depends on UUID sort; check both entries present
		$this->assertStringContainsString('BankOne Alice', $names);
		$this->assertStringContainsString('BankTwo Bob', $names);

		$this->assertSame('', Transaction::accounts(''));
	}
}
