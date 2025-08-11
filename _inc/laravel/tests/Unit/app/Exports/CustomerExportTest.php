<?php

namespace Tests\Unit\Exports;

use App\Exports\CustomerExport;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CustomerExportTest extends TestCase
{
	use RefreshDatabase;

	/** Local copy so we do not reach into a private const */
	private const EXPECTED_HEADINGS = [
		'Customer No', 'Name', 'Email', 'Contact', 'Billing Name',
		'Billing Country', 'Billing State', 'Billing City', 'Billing Phone',
		'Billing Zip', 'Billing Address', 'Shipping Name', 'Shipping Country',
		'Shipping State', 'Shipping City', 'Shipping Phone', 'Shipping Zip',
		'Shipping Address', 'Balance'
	];

	/**
	 ** @test
	 **
	 ** When **no** user is authenticated the export **must** return an
	 ** empty `Collection`, because `_checkLogin()` returns a
	 ** `RedirectResponse` and the method short-circuits.
	 **/
	public function collection_returns_empty_when_user_is_not_authenticated(): void
	{
		$export    = new CustomerExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertTrue($collection->isEmpty(), 'Unauthenticated export should be empty.');
	}

	/**
	 ** @test
	 **
	 ** With an authenticated user the export **must** gather every
	 ** customer **created by that user**, apply the formatter helpers,
	 ** and return a populated `Collection`.
	 **/
	public function collection_returns_formatted_rows_for_authenticated_user(): void
	{
		// Arrange – authenticated creator with two customers
		$user = User::factory()->create();
		$this->actingAs($user);

		Customer::factory()->count(2)->create([
			'created_by' => $user?->creatorId() ?? $user?->id,
			'balance'    => 10,
		]);

		// Act
		$export    = new CustomerExport();
		$collection = $export->collection();

		// Assert – row count
		$this->assertCount(2, $collection);

		// Assert – first row formatting
		$firstCustomer = Customer::first();
		$firstRow     = $collection->first();

		$this->assertEquals(
			$user?->customerNumberFormat($firstCustomer->customer_id),
			$firstRow[0]
		);

		$this->assertEquals(
			$user?->priceFormat($firstCustomer->balance),
			$firstRow[array_key_last($firstRow)]
		);
	}

	/**
	 ** @test
	 **
	 ** `headings()` **must** return the exact list we expect so spreadsheet
	 ** columns line up with exported rows.
	 **/
	public function headings_match_expected_definition(): void
	{
		$export = new CustomerExport();

		$this->assertSame(
			self::EXPECTED_HEADINGS,
			$export->headings(),
			'Headings returned by export are not as expected.'
		);
	}
}
