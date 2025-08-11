<?php

namespace Tests\Unit\Exports;

use App\Exports\ProposalExport;
use App\Models\{Proposal, ProductServiceCategory};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery as m;
use Tests\TestCase;

final class ProposalExportTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** headings() must echo the static list so the spreadsheet header
	 ** is predictable and matches UI expectations.
	 **/
	public function it_returns_the_correct_headings(): void
	{
		$export = new ProposalExport();
		$this->assertSame(
			['ID', 'Proposal No', 'Issue Date', 'Send Date', 'Category', 'Status'],
			$export->headings()
		);
	}

	/**
	 ** @test
	 *
	 ** collection() should:
	 **  • Filter proposals by creator-id of the authenticated user
	 **  • Remove internal fields (created_by, customer_id, …)
	 **  • Format proposal numbers via User::proposalNumberFormat
	 **  • Format Carbon dates as Y-m-d
	 **  • Map category_id → first “income” category name
	 **  • Translate status index using Proposal::$statuses
	 **/
	public function it_builds_the_expected_collection(): void
	{
		// ── Arrange ───────────────────────────────────────────────────────────
		/** Fake authenticated user */
		$user = new class
		{
			public int $id = 1;
			public function creatorId(): string|int
			{
				return $this->id;
			}
			public function proposalNumberFormat($raw): string
			{
				return "PRP-{$raw}";
			}
		};

		Auth::shouldReceive('check')->andReturnTrue();
		Auth::shouldReceive('user')->andReturn($user);

		// Fake category lookup
		ProductServiceCategory::shouldReceive('where')
			->once()->with('type', 'income')->andReturnSelf();
		ProductServiceCategory::shouldReceive('first')
			->once()->andReturn((object) ['name' => 'Consulting']);

		// Patch statuses map
		Proposal::$statuses = [0 => 'Draft', 1 => 'Sent'];

		// Build a stub proposal (stdClass is enough)
		$stub = (object) [
			'id'                => 77,
			'proposal_id'       => 500,
			'issue_date'        => Carbon::create(2025, 5, 10),
			'send_date'         => Carbon::create(2025, 5, 11),
			'status'            => 1,
			'customer_id'       => 2,
			'created_by'        => $user?->id,
			'converted_invoice_id' => null,
			'discount_apply'    => 0,
			'is_convert'        => 0,
			'created_at'        => now(),
			'updated_at'        => now(),
		];

		// Fake DB query
		Proposal::shouldReceive('where')
			->once()->with('created_by', $user?->creatorId())->andReturnSelf();
		Proposal::shouldReceive('get')
			->once()->andReturn(collect([$stub]));

		// ── Act ───────────────────────────────────────────────────────────────
		$export    = new ProposalExport();
		$collection = $export->collection();

		// ── Assert ────────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();         // row is an indexed array

		$this->assertSame(77,        $row[0]);                 // ID
		$this->assertSame('PRP-500', $row[1]);                 // formatted proposal no
		$this->assertSame('2025-05-10', $row[2]);              // issue date
		$this->assertSame('2025-05-11', $row[3]);              // send date
		$this->assertSame('Consulting', $row[4]);              // category
		$this->assertSame('Sent',       $row[5]);              // status label
	}

	/** Clean up Mockery expectations. */
	protected function tearDown(): void
	{
		m::close();
		parent::tearDown();
	}
}
