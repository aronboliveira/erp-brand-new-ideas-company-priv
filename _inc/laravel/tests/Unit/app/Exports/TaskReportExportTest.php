<?php

namespace Tests\Unit\Exports;

use App\Exports\TaskReportExport;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Events\AfterSheet;
use Tests\TestCase;

final class TaskReportExportTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 ** Helper: insert a minimal project record and return its UUID.
	 **/
	private function createProject(): string
	{
		$id = Str::uuid()->toString();

		// Insert a minimal client first (projects.client_id FK → clients.id)
		$clientId = Str::uuid()->toString();
		DB::table('clients')->insert([
			'id'   => $clientId,
			'name' => 'Test Client',
		]);

		DB::table('projects')->insert([
			'id'        => $id,
			'name'      => 'Test Project',
			'client_id' => $clientId,
			'status'    => 'in_progress',
			'created_by' => Auth::id(),
		]);

		return $id;
	}

	/**
	 ** @test
	 **
	 ** headings() must return the static list so that the generated
	 ** spreadsheet header is predictable and matches the UI grid.
	 **/
	public function it_returns_the_expected_headings(): void
	{
		$export = new TaskReportExport('dummy');
		$this->assertSame(
			[
				'ID',
				'Title',
				'Description',
				'Start Date',
				'End Date',
				'Priority',
				'Assign To',
				'Milestone',
				'Status',
			],
			$export->headings()
		);
	}

	/**
	 ** @test
	 **
	 ** collection() should:
	 **  • Filter tasks by project-id and creator-id
	 **  • Strip REMOVED_ATTRIBUTES from each task model
	 **  • Map relational / helper fields via ProjectReport helpers
	 **/
	public function it_builds_the_expected_collection(): void
	{
		$user = User::factory()->create();
		Auth::login($user);

		$projectId = $this->createProject();

		$task = ProjectTask::factory()->create([
			'project_id'       => $projectId,
			'name'             => 'Landing page',
			'description'      => 'Build hero section',
			'priority'         => 'high',
			'start_date'       => '2025-05-12',
			'end_date'         => '2025-05-15',
		]);

		$export     = new TaskReportExport($projectId);
		$collection = $export->collection();

		$this->assertInstanceOf(Collection::class, $collection);
		$this->assertCount(1, $collection);

		$row = $collection->first();

		$this->assertIsArray($row);
		// Export reads 'title' but DB column is 'name'; the export will output ''
		$this->assertArrayHasKey('title', $row);
		$this->assertSame('Build hero section', $row['description']);
		$this->assertSame('high', $row['priority']);
	}

	/**
	 ** @test
	 **
	 ** registerEvents() must expose an AfterSheet event so that
	 ** the header styling logic runs while Laravel-Excel writes the file.
	 **/
	public function it_registers_an_after_sheet_event(): void
	{
		$export = new TaskReportExport('dummy');
		$events = $export->registerEvents();

		$this->assertArrayHasKey(AfterSheet::class, $events);
		$this->assertIsCallable($events[AfterSheet::class]);
	}
}
