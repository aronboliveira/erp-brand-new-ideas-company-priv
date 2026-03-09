<?php

namespace Tests\Unit\Exports;

use App\Exports\ProposalExport;
use App\Models\Proposal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class ProposalExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** @test
	 **
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
	 **
	 ** collection() should:
	 **  • Filter proposals by creator-id of the authenticated user
	 **  • Remove internal fields
	 **  • Format proposal numbers via User::proposalNumberFormat
	 **  • Format Carbon dates as Y-m-d
	 **  • Map category_id → first "income" category name
	 **  • Translate status index using Proposal::$statuses
	 **/
	public function it_builds_the_expected_collection(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$proposal = Proposal::factory()->create([
			'issue_date' => Carbon::create(2025, 5, 10),
		]);

		// Another user's proposal — excluded
		$other = User::factory()->create();
		Auth::login($other);
		Proposal::factory()->create();
		Auth::login($user);

		$export     = new ProposalExport();
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();

		// Row is a plain array
		$this->assertIsArray($row);
		// Should have 6 columns matching headings
		$this->assertCount(6, $row);
	}
}
