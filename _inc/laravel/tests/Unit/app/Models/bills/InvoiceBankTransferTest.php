<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\BelongsTo,
	Support\Carbon
};
use App\Models\{InvoiceBankTransfer, Invoice, Order, User};

use Illuminate\Support\Facades\DB;
class InvoiceBankTransferTest extends TestCase
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
	 ** InvoiceBankTransfer is mass assignable for invoice_id, order_id, amount, status, date, receipt, and created_by
	 **/
	public function invoice_bank_transfer_is_fillable()
	{
		$invoice = Invoice::factory()->create();
		$order  = Order::factory()->create();
		$user   = User::factory()->create();

		$data = [
			'invoice_id' => $invoice->id,
			'order_id'   => $order->id,
			'amount'     => 123.456,       // will be cast to decimal:2
			'status'     => 'Pending',
			'date'       => '2025-05-29',
			'receipt'    => 'receipt.pdf',
			'created_by' => $user?->id,
		];

		$ibt = InvoiceBankTransfer::create($data);

		$this->assertFillableMatches($data, $ibt);
	}

	/**
	 ** @test
	 **
	 ** primary key is auto-incrementing integer
	 **/
	public function primary_key_is_incrementing_integer()
	{
		$ibt = InvoiceBankTransfer::factory()->create();

		$this->assertFalse($ibt->getIncrementing());
		$this->assertSame('string', $ibt->getKeyType());
		$this->assertIsString($ibt->getKey());
	}

	/**
	 ** @test
	 **
	 ** amount is cast to decimal with 2 places, and date is cast to Carbon date
	 **/
	public function casts_are_respected()
	{
		$ibt = InvoiceBankTransfer::factory()->create([
			'amount' => 50.5,
			'date'   => '2025-06-01',
		]);

		$this->assertSame('50.50', (string) $ibt->amount);
		$this->assertInstanceOf(Carbon::class, $ibt->date);
		$this->assertSame('2025-06-01', $ibt->date->toDateString());
	}

	/**
	 ** @test
	 **
	 ** invoice() relation should point to App\Models\Invoice via invoice_id
	 **/
	public function invoice_relation_resolves_to_invoice_model()
	{
		$relation = (new InvoiceBankTransfer)->invoice();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Invoice::class,         get_class($relation->getRelated()));
		$this->assertSame('invoice_id',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** order() relation should point to App\Models\Order via order_id
	 **/
	public function order_relation_resolves_to_order_model()
	{
		$relation = (new InvoiceBankTransfer)->order();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Order::class,           get_class($relation->getRelated()));
		$this->assertSame('order_id',             $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new InvoiceBankTransfer)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
