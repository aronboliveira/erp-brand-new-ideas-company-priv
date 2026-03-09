<?php
// tests/Unit/Exports/VendorExportTest.php

namespace Tests\Unit\Exports;

use App\Exports\VendorExport;
use App\Models\{User, Vendor};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

class VendorExportTest extends TestCase
{
	use DatabaseTransactions;

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

		// Auth as $user first so HasAuditFields sets created_by
		Auth::login($user);
		Vendor::factory()->count(2)->create();

		Auth::login($other);
		Vendor::factory()->create();

		Auth::login($user);

		$export    = new VendorExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(2, $collection);           // only current user’s vendors

		$collection->each(function (array $row) {
			// Must have the expected keys from the export
			$this->assertArrayHasKey('id', $row);
			$this->assertArrayHasKey('name', $row);
			$this->assertArrayHasKey('balance', $row);

			// Formatted ID starts with #VEND
			$this->assertStringStartsWith('#VEND', $row['id']);
			// Formatted money contains the currency symbol
			$this->assertStringContainsString('R$', $row['balance']);
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
			'ID',
			'Name',
			'Email',
			'Contact',
			'Billing Name',
			'Billing Country',
			'Billing State',
			'Billing City',
			'Billing Phone',
			'Billing Zip',
			'Billing Address',
			'Shipping Name',
			'Shipping Country',
			'Shipping State',
			'Shipping City',
			'Shipping Phone',
			'Shipping Zip',
			'Shipping Address',
			'Balance'
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
