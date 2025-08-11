<?php
// File: tests/Unit/Exports/InvoiceExportTest.php

namespace Tests\Unit\Exports;

use App\Exports\InvoiceExport;
use App\Models\{Invoice, ProductServiceCategory};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery as m;
use Tests\TestCase;

final class InvoiceExportTest extends TestCase
{
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
				'Invoice Id', 'Issue Date', 'Due Date', 'Send Date',
				'Category', 'Ref Number', 'Status',
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
	 **  * Format invoice_id using User::invoiceNumberFormat
	 **  * Replace category_id with the first income-type category name
	 **  * Map the numeric status to its label via Invoice::$statuses
	 **/
	public function it_transforms_and_returns_the_invoice_collection(): void
	{
		// ── Arrange ────────────────────────────────────────────────────────────
		// Fake auth-user
		$user = new class
		{
			public int $id = 1;
			public function creatorId(): string|int
			{
				return $this->id;
			}
			public function invoiceNumberFormat($raw): string
			{
				return "INV-{$raw}";
			}
		};

		Auth::shouldReceive('check')->andReturnTrue();
		Auth::shouldReceive('user')->andReturn($user);

		// Fake ProductServiceCategory lookup
		ProductServiceCategory::shouldReceive('where')
			->once()->with('type', 'income')->andReturnSelf();
		ProductServiceCategory::shouldReceive('first')
			->once()->andReturn((object) ['name' => 'Sales']);

		// Patch the public static $statuses map on the Invoice model
		Invoice::$statuses = [0 => 'Draft', 1 => 'Sent'];

		// Build a stub invoice (stdClass is enough for the export logic)
		$stubInvoice             = new \stdClass();
		$stubInvoice->id         = 99;
		$stubInvoice->invoice_id = 123;
		$stubInvoice->customer_id = 2;
		$stubInvoice->created_by = $user?->id;
		$stubInvoice->shipping_display = 0;
		$stubInvoice->discount_apply = 0;
		$stubInvoice->created_at     = now();
		$stubInvoice->updated_at     = now();
		$stubInvoice->issue_date     = '2025-05-01';
		$stubInvoice->due_date       = '2025-05-15';
		$stubInvoice->send_date      = '2025-05-02';
		$stubInvoice->category_id    = 1;
		$stubInvoice->ref_number     = 'REF-42';
		$stubInvoice->status         = 1;

		// Fake DB query chain
		Invoice::shouldReceive('where')
			->once()->with('created_by', $user?->creatorId())->andReturnSelf();
		Invoice::shouldReceive('get')
			->once()->andReturn(collect([$stubInvoice]));

		// ── Act ───────────────────────────────────────────────────────────────
		$export    = new InvoiceExport();
		$collection = $export->collection();

		// ── Assert ────────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$inv = $collection->first();

		// UNSET_FIELDS removed
		$this->assertFalse(isset($inv->id));
		$this->assertFalse(isset($inv->customer_id));
		$this->assertFalse(isset($inv->created_at));

		// Formatted & mapped fields
		$this->assertSame('INV-123', $inv->invoice_id);
		$this->assertSame('Sales',   $inv->category_id);
		$this->assertSame('Sent',    $inv->status);
	}

	/**
	 ** Tear-down Mockery expectations.
	 */
	protected function tearDown(): void
	{
		m::close();
		parent::tearDown();
	}
}
