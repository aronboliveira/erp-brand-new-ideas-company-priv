<?php

namespace Tests\Unit\Exports;

use App\Exports\ProfitLossExport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\{AfterSheet, BeforeWriting};
use Tests\TestCase;

class ProfitLossExportTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Given a minimal set of **Income**, **Costs of Goods Sold**,
	 ** and **Expenses** rows, the export **must**:
	 **   • build the correct internal data array
	 **   • calculate **Gross Profit** (= Income − COGS)
	 **   • calculate **Net Profit/Loss** (= Gross Profit − Expenses)
	 **   • keep “bold-type” labels (e.g. *Income*, *Expenses*) in
	 **     separate rows exactly as specified by the implementation.
	 **/
	public function array_returns_expected_formatted_rows(): void
	{
		Carbon::setTestNow('2025-05-15');          // deterministic “Print Out Date”

		$user = User::factory()->create();
		Auth::login($user);

		$rows = [
			[
				'Type'    => 'Income',
				'account' => [
					['account_name' => 'Sales',           'account_code' => '400', 'netAmount' => 1000],
					['account_name' => 'Total Income',    'account_code' => '',    'netAmount' => 1000],
				]
			],
			[
				'Type'    => 'Costs of Goods Sold',
				'account' => [
					['account_name' => 'COGS',                        'account_code' => '500', 'netAmount' => 200],
					['account_name' => 'Total Costs of Goods Sold',   'account_code' => '',    'netAmount' => 200],
				]
			],
			[
				'Type'    => 'Expenses',
				'account' => [
					['account_name' => 'Rent',           'account_code' => '600', 'netAmount' => 100],
					['account_name' => 'Total Expenses', 'account_code' => '',    'netAmount' => 100],
				]
			],
		];

		$export = new ProfitLossExport($rows, '2025-05-01', '2025-05-31', 'Demo Inc.');
		$data  = $export->array();       // call the public wrapper

		// ——— Basic structure checks ———
		$this->assertNotEmpty($data);
		$titles = array_column($data, 'Account Name');

		$this->assertContains('Income',              $titles);
		$this->assertContains('Costs of Goods Sold', $titles);
		$this->assertContains('Expenses',            $titles);
		$this->assertContains('Gross Profit',        $titles);
		$this->assertContains('Net Profit/Loss',     $titles);

		// ——— Business-logic checks ———
		$grossRow = collect($data)->firstWhere('Account Name', 'Gross Profit');
		$netRow  = collect($data)->firstWhere('Account Name', 'Net Profit/Loss');

		// Gross Profit = 1 000 – 200 = 800
		$this->assertSame(800, $grossRow['Total']);

		// Net Profit  = 800 – 100 = 700
		$this->assertSame(700, $netRow['Total']);
	}

	/**
	 ** @test
	 **
	 ** The export defines fixed **column widths**, a custom **start
	 ** cell** and static **headings**. These helpers **must** return
	 ** those exact constants.
	 **/
	public function helpers_return_expected_values(): void
	{
		$export = new ProfitLossExport([], '2025-05-01', '2025-05-31', 'Demo');

		$this->assertSame('A5', $export->startCell());
		$this->assertSame(['A' => 30, 'B' => 15, 'C' => 15], $export->columnWidths());
		$this->assertSame(['Account', 'Account No', 'Total'], $export->headings());
	}

	/**
	 ** @test
	 **
	 ** The export **must** register two events:
	 **   • **BeforeWriting**
	 **   • **AfterSheet**
	 ** Both array entries should be valid callables.
	 **/
	public function register_events_contains_callable_handlers(): void
	{
		$export = new ProfitLossExport([], '2025-05-01', '2025-05-31', 'Demo');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(BeforeWriting::class, $events);
		$this->assertArrayHasKey(AfterSheet::class,    $events);

		$this->assertIsCallable($events[BeforeWriting::class]);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
