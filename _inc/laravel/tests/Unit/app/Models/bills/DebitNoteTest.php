<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{DebitNote, Bill, Vendor};

class DebitNoteTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** DebitNote is mass assignable for bill, vendor, amount, date, and description
	 **/
	public function debit_note_is_fillable()
	{
		$bill  = Bill::factory()->create();
		$vendor = Vendor::factory()->create();

		$data = [
			'bill'        => $bill->id,
			'vendor'      => $vendor->id,
			'amount'      => 200.00,
			'date'        => '2025-05-28',
			'description' => 'Adjustment credit',
		];

		$note = DebitNote::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $note->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** DebitNote uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function debit_note_uses_uuid_for_primary_key()
	{
		$note = DebitNote::factory()->create();

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
	 ** bill() relation should point to App\Models\Bill via bill
	 **/
	public function bill_relation_resolves_to_bill_model()
	{
		$relation = (new DebitNote)->bill();

		$this->assertInstanceOf(HasOne::class, get_class($relation));
		$this->assertSame(Bill::class,        get_class($relation->getRelated()));
		$this->assertSame('id',               $relation->getForeignKeyName());
		$this->assertSame('bill',             $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** vendor() relation should point to App\Models\Vendor via vendor
	 **/
	public function vendor_relation_resolves_to_vendor_model()
	{
		$relation = (new DebitNote)->vendor();

		$this->assertInstanceOf(HasOne::class, get_class($relation));
		$this->assertSame(Vendor::class,      get_class($relation->getRelated()));
		$this->assertSame('id',               $relation->getForeignKeyName());
		$this->assertSame('vendor',           $relation->getLocalKeyName());
	}
}
