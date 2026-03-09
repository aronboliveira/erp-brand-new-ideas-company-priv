<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{CreditNote, Invoice, Customer};

class CreditNoteTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** CreditNote is mass assignable for invoice, customer, amount, date, and description
	 **/
	public function credit_note_is_fillable()
	{
		$invoice = Invoice::factory()->create();
		$customer = Customer::factory()->create();

		$data = [
			'invoice'     => $invoice->id,
			'customer_id' => $customer->id,
			'amount'      => 100.50,
			'date'        => '2025-05-27',
			'description' => 'Refund issued',
		];

		$note = CreditNote::create($data);

		$this->assertFillableMatches($data, $note);
	}

	/**
	 ** @test
	 **
	 ** CreditNote uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function credit_note_uses_uuid_for_primary_key()
	{
		$invoice = Invoice::factory()->create();
		$note = CreditNote::factory()->create(['invoice' => $invoice->id]);

		$key = $note->getKey();

		$this->assertIsString($key);
		$this->assertFalse($note->getIncrementing());
		$this->assertSame('string', $note->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** invoice() relation should point to App\Models\Invoice via invoice
	 **/
	public function invoice_relation_resolves_to_invoice_model()
	{
		$relation = (new CreditNote)->invoice();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Invoice::class,         get_class($relation->getRelated()));
		$this->assertSame('invoice',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** customer() relation should point to App\Models\Customer via customer
	 **/
	public function customer_relation_resolves_to_customer_model()
	{
		$relation = (new CreditNote)->customer();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(Customer::class,        get_class($relation->getRelated()));
		$this->assertSame('customer_id',               $relation->getForeignKeyName());
		$this->assertSame('id',             $relation->getOwnerKeyName());
	}
}
