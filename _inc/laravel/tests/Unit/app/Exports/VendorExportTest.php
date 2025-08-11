<?php
// tests/Unit/Exports/VendorExportTest.php

namespace Tests\Unit\Exports;

use App\Exports\VendorExport;
use App\Models\{User, Vendor};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class VendorExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** The `collection()` method must return a `Collection`
	 ** containing only vendors created by the logged-in user.
	 ** Each row should:
	 **   • contain every column label returned by `headings()`  
	 **   • format `id` via `vendorNumberFormat()`  
	 **   • format `balance` via `priceFormat()`  
	 **/
	public function collection_returns_only_users_vendors_in_expected_shape(): void
	{
		$user = User::factory()->create();
		$other = User::factory()->create();

		// 2 vendors for $user, 1 for $other
		Vendor::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		Vendor::factory()->create(['created_by' => $other->creatorId()]);

		Auth::login($user);

		$export    = new VendorExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(2, $collection);           // only current user’s vendors

		// Expected column labels (public helper)
		$labels = $export->headings();

		// Helper to convert heading ⇒ array key (spaces → camelCase-ish)
		$headingToKey = static fn (string $h) => str($h)
			->camel()
			->replaceFirst('iD', 'id')   // keep “ID” uppercase
			->value();

		$collection->each(function (array $row) use ($labels, $headingToKey) {
			foreach ($labels as $label) {
				$key = $headingToKey($label);
				$this->assertArrayHasKey($key, $row, "Missing {$key} column.");
			}

			$this->assertStringStartsWith('VEN-', $row['id']);       // formatted ID
			$this->assertStringContainsString('$',   $row['balance']); // formatted money
		});
	}

	/**
	 ** @test
	 **
	 ** `headings()` must return the constant header list verbatim.
	 **/
	public function headings_return_expected_labels(): void
	{
		$export  = new VendorExport();
		$expected = [
			'ID', 'Name', 'Email', 'Contact',
			'Billing Name', 'Billing Country', 'Billing State',
			'Billing City', 'Billing Phone',  'Billing Zip',
			'Billing Address',
			'Shipping Name', 'Shipping Country', 'Shipping State',
			'Shipping City', 'Shipping Phone',   'Shipping Zip',
			'Shipping Address', 'Balance'
		];

		$this->assertSame($expected, $export->headings());
	}

	/**
	 ** @test
	 **
	 ** `registerEvents()` must register exactly one **AfterSheet**
	 ** listener mapped to a callable.
	 **/
	public function register_events_contains_callable_aftersheet(): void
	{
		$events = (new VendorExport())->registerEvents();

		$this->assertCount(1, $events);
		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
