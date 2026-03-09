<?php

namespace Tests\Unit\Exports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Exports\BillExport;
use App\Models\{User, Bill, ProductServiceCategory};

/**
 ** Test-suite for the `BillExport` class.
 **
 ** These tests validate that:
 **   • The header row is correct.  
 **   • The `collection()` method filters & formats the data that belongs to the
 **     currently authenticated user.  
 **   • An `AfterSheet` listener is always registered so that styling is applied.  
 **/
class BillExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** It should return the exact column headings defined in the export,
	 ** guaranteeing that downstream spreadsheets receive the expected header row.
	 **/
	public function headings_are_correct(): void
	{
		$export = new BillExport();

		$this->assertSame(
			['Bill No', 'Bill Date', 'Due Date', 'Order No', 'Status', 'Send Date', 'Category'],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** When the user is authenticated and has bills recorded,
	 ** the `collection()` method must return *only* that user’s bills,
	 ** formatted exactly as the export requires.
	 **/
	public function collection_returns_only_user_bills_formatted(): void
	{
		// ── Arrange ──────────────────────────────────────────────────────────────
		$user = User::factory()->create();
		Auth::login($user);

		$bill = Bill::factory()->create([
			'bill_id'    => 1,
			'bill_date'  => Carbon::parse('2025-05-01'),
			'due_date'   => Carbon::parse('2025-05-10'),
			'send_date'  => Carbon::parse('2025-05-02'),
			'status'     => array_key_first(Bill::$statuses), // first valid status key
		]);

		// A bill from a different creator that must be ignored
		$otherUser = User::factory()->create();
		Auth::login($otherUser);
		Bill::factory()->create();
		Auth::login($user);

		// ── Act ─────────────────────────────────────────────────────────────────
		$export    = new BillExport();
		$collection = $export->collection();

		// ── Assert ──────────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();
		$this->assertIsArray($row);
		$this->assertCount(7, $row);

		// First element is the formatted bill number
		$this->assertSame($user?->billNumberFormat($bill->bill_id), $row[0]);
		// Dates
		$this->assertSame('2025-05-01', $row[1]);
		$this->assertSame('2025-05-10', $row[2]);
		// Status
		$this->assertSame(Bill::$statuses[$bill->status], $row[4]);
		$this->assertSame('2025-05-02', $row[5]);
	}

	/**
	 ** @test
	 **
	 ** The `registerEvents()` method must always register an `AfterSheet`
	 ** listener so that styling logic is executed after the sheet is built.
	 **/
	public function register_events_contains_after_sheet_listener(): void
	{
		$export = new BillExport();
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
