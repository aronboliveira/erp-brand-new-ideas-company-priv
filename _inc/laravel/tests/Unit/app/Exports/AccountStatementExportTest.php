<?php

namespace Tests\Unit\Exports;

use App\Exports\AccountStatementExport;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class AccountStatementExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** It returns a Collection containing only the Revenue records
	 ** that belong to the currently-authenticated user and **removes**
	 ** every attribute listed in `REMOVED_ATTRIBUTES`.
	 **/
	public function collection_returns_sanitized_revenue_data(): void
	{
		$user = User::factory()->create();
		$other = User::factory()->create();

		// Create statements for $user (Auth must be active for HasAuditFields)
		Auth::login($user);
		Revenue::factory()->count(2)->create();

		// Create statements for other user
		Auth::login($other);
		Revenue::factory()->count(1)->create();

		Auth::login($user);

		$export    = new AccountStatementExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(2, $collection); // only current user’s records

		// Each record must exclude the removed attributes
		$collection->each(function ($statement) {
			foreach (
				[
					'account_id',
					'add_receipt',
					'category_id',
					'created_at',
					'created_by',
					'customer_id',
					'payment_method',
					'reference',
					'updated_at'
				] as $attr
			) {
				$this->assertFalse(isset($statement->{$attr}), "Attribute {$attr} still present.");
			}
		});
	}

	/**
	 ** @test
	 **
	 ** It returns the exact heading labels expected by the Excel
	 ** template (order **must** match).
	 **/
	public function headings_returns_expected_array(): void
	{
		$export  = new AccountStatementExport();
		$expected = ['Statement Id', 'Date', 'Amount', 'Description'];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** The `registerEvents()` method registers a single **AfterSheet**
	 ** event and the associated handler is a valid `callable`.
	 **/
	public function register_events_registers_aftersheet_handler(): void
	{
		$export = new AccountStatementExport();
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
