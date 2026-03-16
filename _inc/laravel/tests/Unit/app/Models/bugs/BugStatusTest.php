<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Support\Facades\Auth
};
use App\Models\{BugStatus, Bug, Project, User};

use Illuminate\Support\Facades\DB;
class BugStatusTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** BugStatus is mass assignable for created_by, order, and title
	 **/
	public function bug_status_is_fillable()
	{
		$data = [
			'order'      => 1,
			'title'      => 'Open',
		];

		$bs = BugStatus::create($data);

		$this->assertFillableMatches($data, $bs);
	}

	/**
	 ** @test
	 **
	 ** primary key uses UUID: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$bs = BugStatus::factory()->create();

		$this->assertIsString($bs->getKey());
		$this->assertFalse($bs->getIncrementing());
		$this->assertSame('string', $bs->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$bs->getKey()
		);
	}

	/**
	 ** @test
	 **
	 ** bugs() returns all bugs for project for company/client users
	 **/
	public function bugs_method_returns_all_bugs_for_company_or_client()
	{
		$project = Project::factory()->create();
		$status = BugStatus::factory()->create();
		$uCompany = User::factory()->create(['type' => 'company']);
		Auth::login($uCompany);

		Bug::factory()->count(2)->create([
			'status'     => $status->id,
			'project_id' => $project->id,
		]);
		Bug::factory()->create([
			'status'     => $status->id,
			'project_id' => 'other',
		]);

		$bugs = $status->bugs($project->id);
		$this->assertCount(2, $bugs);
	}

	/**
	 ** @test
	 **
	 ** bugs() returns only assigned bugs for non-company users
	 **/
	public function bugs_method_returns_assigned_bugs_for_non_company_user()
	{
		$project = Project::factory()->create();
		$status = BugStatus::factory()->create();
		$uEmp = User::factory()->create(['type' => 'employee']);
		Auth::login($uEmp);

		$b1 = Bug::factory()->create([
			'status'     => $status->id,
			'project_id' => $project->id,
			'assign_to'  => $uEmp->id,
		]);
		Bug::factory()->create([
			'status'     => $status->id,
			'project_id' => $project->id,
			'assign_to'  => 'other',
		]);

		$bugs = $status->bugs($project->id);
		$this->assertCount(1, $bugs);
		$this->assertTrue($bugs->first()->is($b1));
	}

	/**
	 ** @test
	 **
	 ** assignBugs() returns only bugs assigned to current user
	 **/
	public function assign_bugs_method_returns_user_assigned_bugs()
	{
		$project = Project::factory()->create();
		$status = BugStatus::factory()->create();
		$uEmp = User::factory()->create(['type' => 'employee']);
		Auth::login($uEmp);

		$b1 = Bug::factory()->create([
			'status'     => $status->id,
			'project_id' => $project->id,
			'assign_to'  => $uEmp->id,
		]);
		Bug::factory()->create([
			'status'     => $status->id,
			'project_id' => $project->id,
			'assign_to'  => 'other',
		]);

		$assigned = $status->assignBugs($project->id);
		$this->assertCount(1, $assigned);
		$this->assertTrue($assigned->first()->is($b1));
	}
}
