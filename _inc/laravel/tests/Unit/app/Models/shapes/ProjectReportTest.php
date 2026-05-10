<?php

namespace Tests\Unit\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	/**
	 ** @test
	 **
	 ** assignUser() must collect names for valid user IDs,
	 ** concatenated with commas, and ignore missing users.
	 **/
	public function assign_user_returns_concatenated_names(): void
	{
		$user1 = User::create([
			'name' => 'Alice',
			'email' => 'project-report-alice-' . Str::uuid() . '@test.local',
			'password' => bcrypt('secret'),
			'lang' => 'en',
		]);
		$user2 = User::create([
			'name' => 'Bob',
			'email' => 'project-report-bob-' . Str::uuid() . '@test.local',
			'password' => bcrypt('secret'),
			'lang' => 'en',
		]);
		$missingId = (string) Str::uuid();

		try {
			// DB-backed fixture avoids Mockery alias order dependence once User is autoloaded.
			$result = ProjectReport::assignUser("{$user1->id},{$user2->id},{$missingId}");
			$this->assertSame('Alice,Bob,', $result);
		} finally {
			DB::table('users')->whereIn('id', [$user1->id, $user2->id])->delete();
		}
	}

	/**
	 ** @test
	 **
	 ** milestone() must return the milestone title or empty string.
	 **/
	public function milestone_returns_title_or_empty(): void
	{
		$milestoneId = (string) Str::uuid();
		$missingId = (string) Str::uuid();
		$now = now();

		DB::table('milestones')->insert([
			'id' => $milestoneId,
			'title' => 'Phase 1',
			'created_by' => DC::DEFAULT_UUID,
			'updated_by' => DC::DEFAULT_UUID,
			'created_at' => $now,
			'updated_at' => $now,
		]);

		try {
			$this->assertSame('Phase 1', ProjectReport::milestone($milestoneId));
			$this->assertSame('', ProjectReport::milestone($missingId));
		} finally {
			DB::table('milestones')->where('id', $milestoneId)->delete();
		}
	}

	/**
	 ** @test
	 **
	 ** status() must return the task stage name or empty string.
	 **/
	public function status_returns_name_or_empty(): void
	{
		$stageId = (string) Str::uuid();
		$missingId = (string) Str::uuid();
		$now = now();

		DB::table('task_stages')->insert([
			'id' => $stageId,
			'name' => 'Done',
			'created_by' => DC::DEFAULT_UUID,
			'updated_by' => DC::DEFAULT_UUID,
			'created_at' => $now,
			'updated_at' => $now,
		]);

		try {
			$this->assertSame('Done', ProjectReport::status($stageId));
			$this->assertSame('', ProjectReport::status($missingId));
		} finally {
			DB::table('task_stages')->where('id', $stageId)->delete();
		}
	}
}
