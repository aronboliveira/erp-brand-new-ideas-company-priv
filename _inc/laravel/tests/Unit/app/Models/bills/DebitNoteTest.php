<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{DebitNote, Bill, Vendor};

use Illuminate\Support\Facades\DB;
class DebitNoteTest extends TestCase
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

		$this->assertFillableMatches($data, $note);
	}

	/**
	 ** @test
	 **
	 ** DebitNote uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function debit_note_uses_uuid_for_primary_key()
	{
		$bill = Bill::factory()->create();
		$note = DebitNote::factory()->create(['bill' => $bill->id]);

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

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Bill::class,        get_class($relation->getRelated()));
		$this->assertSame('bill',               $relation->getForeignKeyName());
		$this->assertSame('id',             $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** vendor() relation should point to App\Models\Vendor via vendor
	 **/
	public function vendor_relation_resolves_to_vendor_model()
	{
		$relation = (new DebitNote)->vendor();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Vendor::class,      get_class($relation->getRelated()));
		$this->assertSame('vendor_id',            $relation->getForeignKeyName());
		$this->assertSame('id',           $relation->getOwnerKeyName());
	}
}
