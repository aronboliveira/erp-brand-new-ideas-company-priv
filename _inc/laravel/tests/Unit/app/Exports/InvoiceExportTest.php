<?php

namespace Tests\Unit\Exports;

use App\Exports\InvoiceExport;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class InvoiceExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
	 ** headings() must return the static array defined in the export
	 ** so Excel receives the exact header row the UI expects.
	 **/
	public function it_returns_the_expected_headings(): void
	{
		$export = new InvoiceExport();
		$this->assertSame(
			[
				'Invoice Id',
				'Issue Date',
				'Due Date',
				'Send Date',
				'Category',
				'Ref Number',
				'Status',
			],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** collection() should:
	 **  * Load invoices filtered by the creator-id of the authenticated user
	 **  * Strip UNSET_FIELDS from each invoice
	 **  * Replace category_id with the first income-type category name
	 **  * Map the numeric status to its label via Invoice::$statuses
	 **/
	public function it_transforms_and_returns_the_invoice_collection(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		Invoice::factory()->create([
			'status' => 1,
		]);

		// Another user's invoice — must be excluded
		$other = User::factory()->create();
		Auth::login($other);
		Invoice::factory()->create();
		Auth::login($user);

		$export     = new InvoiceExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$inv = $collection->first();

		// UNSET_FIELDS removed
		$this->assertFalse(isset($inv->id));
		$this->assertFalse(isset($inv->customer_id));
		$this->assertFalse(isset($inv->created_at));

		// Status mapped to label
		$this->assertSame('Sent', $inv->status);
	}
}
