<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Exports\TransactionExport;
use App\Models\{User, Transaction};

/**
 ** Test-suite for the `TransactionExport` class.
 **
 ** Verifies that:
 **   • The column headings remain unchanged.  
 **   • The `collection()` method returns *only* the authenticated user’s
 **     transactions, strips internal fields, and converts the account column
 **     via `Transaction::accounts()`.  
 **/
class TransactionExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** It should expose the exact heading labels and order required by
	 ** consumer spreadsheets.
	 **/
	public function headings_are_correct(): void
	{
		$export = new TransactionExport();

		$expected = [
			'Transaction Id', 'Account', 'Type',
			'Amount', 'Description', 'Date', 'Category',
		];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** With an authenticated user, the collection must:
	 **   1. Contain only that user’s transactions;  
	 **   2. Remove every field listed in `REMOVED_FIELDS`;  
	 **   3. Replace the raw account ID with its human-readable label via
	 **      `Transaction::accounts()`.  
	 **/
	public function collection_filters_and_formats_transactions(): void
	{
		// ── Arrange ──────────────────────────────────────────────────────────
		$user = User::factory()->create();
		Auth::login($user);

		// Example account label mapping
		$label = Transaction::accounts(1);

		$tx = Transaction::factory()->create([
			'transaction_id' => 'TX-001',
			'account'        => 1,          // will be mapped to $label
			'type'           => 'credit',
			'amount'         => 100,
			'description'    => 'Payment',
			'date'           => now(),
			'category'       => 'Sales',
			'created_by'     => $user?->id,
		]);

		// Foreign user transaction – must be excluded
		Transaction::factory()->create();

		// ── Act ──────────────────────────────────────────────────────────────
		$export     = new TransactionExport();
		$collection = $export->collection();

		// ── Assert ───────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first()->toArray();

		// 1️⃣ must not include removed fields
		$removed = [
			'created_by', 'created_at', 'updated_at',
			'user_type', 'user_id', 'payment_id',
		];
		foreach ($removed as $field) {
			$this->assertArrayNotHasKey($field, $row);
		}

		// 2️⃣ account label mapping applied
		$this->assertSame($label, $row['account']);

		// 3️⃣ remaining columns keep their original data
		$this->assertSame('TX-001', $row['transaction_id']);
		$this->assertSame('credit', $row['type']);
		$this->assertSame(100, $row['amount']);
		$this->assertSame('Payment', $row['description']);
		$this->assertSame('Sales', $row['category']);
	}
}
