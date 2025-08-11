<?php
// File: tests/Unit/Exports/TaskReportExportTest.php

namespace Tests\Unit\Exports;

use App\Exports\TaskReportExport;
use App\Models\{ProjectReport, ProjectTask};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery as m;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

final class TaskReportExportTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** headings() must return the static list so that the generated
	 ** spreadsheet header is predictable and matches the UI grid.
	 **/
	public function it_returns_the_expected_headings(): void
	{
		$export = new TaskReportExport(1);
		$this->assertSame(
			[
				'ID', 'Title', 'Description', 'Start Date', 'End Date',
				'Priority', 'Assign To', 'Milestone', 'Status',
			],
			$export->headings()
		);
	}

	/**
	 ** @test
	 *
	 ** collection() should:
	 **  • Filter tasks by project-id and creator-id
	 **  • Strip REMOVED_ATTRIBUTES from each task model
	 **  • Map relational / helper fields via ProjectReport::assignUser,
	 **    ::milestone and ::status
	 **/
	public function it_builds_the_expected_collection(): void
	{
		// ── Arrange ───────────────────────────────────────────────────────────
		/** Fake authenticated user */
		$user = new class
		{
			public int $id = 42;
			public function creatorId(): string|int
			{
				return $this->id;
			}
		};

		Auth::shouldReceive('check')->andReturnTrue();
		Auth::shouldReceive('user')->andReturn($user);

		// Fake ProjectReport helpers
		ProjectReport::shouldReceive('assignUser')->once()->with('1,2')->andReturn('Alice, Bob');
		ProjectReport::shouldReceive('milestone')->once()->with(7)->andReturn('MVP');
		ProjectReport::shouldReceive('status')->once()->with(3)->andReturn('In Progress');

		// Build a stub ProjectTask record
		$stub = (object) [
			'id'             => 10,
			'title'          => 'Landing page',
			'description'    => 'Build hero section',
			'start_date'     => '2025-05-12',
			'end_date'       => '2025-05-15',
			'priority'       => 'High',
			'assign_to'      => '1,2',
			'milestone_id'   => 7,
			'stage_id'       => 3,
			// — attributes slated for removal —
			'estimated_hrs'  => 20,
			'priority_color' => '#ff0000',
			'project_id'     => 1,
			'order'          => 1,
			'created_by'     => $user?->id,
			'is_favourite'   => 0,
			'is_complete'    => 0,
			'marked_at'      => null,
			'progress'       => 50,
			'created_at'     => now(),
			'updated_at'     => now(),
		];

		// Mock the query chain
		ProjectTask::shouldReceive('where')
			->once()->with('project_id', 1)->andReturnSelf();
		ProjectTask::shouldReceive('where')
			->once()->with('created_by', $user?->creatorId())->andReturnSelf();
		ProjectTask::shouldReceive('get')
			->once()->andReturn(collect([$stub]));

		// ── Act ───────────────────────────────────────────────────────────────
		$export    = new TaskReportExport(1);
		$collection = $export->collection();

		// ── Assert ────────────────────────────────────────────────────────────
		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first(); // mapped associative array

		$this->assertSame(10,              $row['id']);
		$this->assertSame('Landing page',  $row['title']);
		$this->assertSame('Build hero section', $row['description']);
		$this->assertSame('2025-05-12',    $row['startDate']);
		$this->assertSame('2025-05-15',    $row['endDate']);
		$this->assertSame('High',          $row['priority']);
		$this->assertSame('Alice, Bob',    $row['assignTo']);
		$this->assertSame('MVP',           $row['milestone']);
		$this->assertSame('In Progress',   $row['status']);
	}

	/**
	 ** @test
	 *
	 ** registerEvents() must expose an AfterSheet event so that
	 ** the header styling logic runs while Laravel-Excel writes the file.
	 **/
	public function it_registers_an_after_sheet_event(): void
	{
		$export = new TaskReportExport(1);
		$this->assertArrayHasKey(AfterSheet::class, $export->registerEvents());
	}

	/** Close Mockery expectations. */
	protected function tearDown(): void
	{
		m::close();
		parent::tearDown();
	}
}
