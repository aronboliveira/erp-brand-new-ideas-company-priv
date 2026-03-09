<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{
	InvoicePayment,
	Invoice,
	Order,
	BankAccount
};

class InvoicePaymentTest extends TestCase
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
	 ** InvoicePayment is mass assignable for invoice_id, date, amount,
	 ** account_id, payment_method, order_id, currency, txn_id,
	 ** payment_type, receipt, add_receipt, reference, and description
	 **/
	public function invoice_payment_is_fillable()
	{
		$invoice    = Invoice::factory()->create();
		$order      = Order::factory()->create();
		$bankAccount = BankAccount::factory()->create();

		$data = [
			'invoice_id'     => $invoice->id,
			'date'           => '2025-05-30',
			'amount'         => 27.50,
			'account_id'     => $bankAccount->id,
			'payment_method' => 'credit_card',
			'order_id'       => $order->id,
			'currency'       => 'USD',
			'txn_id'         => 'TX1234',
			'payment_type'   => 'one_time',
			'receipt'        => 'receipt.png',
			'add_receipt'    => 'add_receipt.png',
			'reference'      => 'Ref001',
			'description'    => 'Payment received',
		];

		$ip = InvoicePayment::create($data);

		$this->assertFillableMatches($data, $ip);
	}

	/**
	 ** @test
	 **
	 ** InvoicePayment uses UUIDs for its primary key:
	 ** string, non-incrementing, valid UUID format
	 **/
	public function invoice_payment_uses_uuid_for_primary_key()
	{
		$ip = InvoicePayment::factory()->create();
		$key = $ip->getKey();

		$this->assertIsString($key);
		$this->assertFalse($ip->getIncrementing());
		$this->assertSame('string', $ip->getKeyType());
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
		$relation = (new InvoicePayment)->bankAccount();

		$this->assertInstanceOf(BelongsTo::class,        $relation);
		$this->assertSame(BankAccount::class,         get_class($relation->getRelated()));
		$this->assertSame('account_id',                       $relation->getForeignKeyName());
		$this->assertSame('id',               $relation->getOwnerKeyName());
	}
}
