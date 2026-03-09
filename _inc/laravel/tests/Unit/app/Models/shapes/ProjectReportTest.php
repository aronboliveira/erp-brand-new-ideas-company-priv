<?php

namespace Tests\Unit\Models;

use App\Models\ProjectReport;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class ProjectReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	use SafeAliasMock;

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** assignUser() must collect names for valid user IDs,
	 ** concatenated with commas, and ignore missing users.
	 **/
	public function assign_user_returns_concatenated_names(): void
	{
		$user1 = (object)['name' => 'Alice'];
		$user2 = (object)['name' => 'Bob'];

		$this->aliasMock('App\Models\User')
			->shouldReceive('find')
			->with('u1')
			->andReturn($user1)
			->getMock()
			->shouldReceive('find')
			->with('u2')
			->andReturn($user2)
			->getMock()
			->shouldReceive('find')
			->with('u3')
			->andReturnNull();

		$result = ProjectReport::assignUser('u1,u2,u3');
		$this->assertSame('Alice,Bob,', $result);
	}

	/**
	 ** @test
	 **
	 ** milestone() must return the milestone title or empty string.
	 **/
	public function milestone_returns_title_or_empty(): void
	{
		$m = (object)['title' => 'Phase 1'];
		$this->aliasMock('App\Models\Milestone')
			->shouldReceive('find')
			->with(10)
			->andReturn($m)
			->getMock()
			->shouldReceive('find')
			->with(99)
			->andReturnNull();

		$this->assertSame('Phase 1', ProjectReport::milestone(10));
		$this->assertSame('',       ProjectReport::milestone(99));
	}

	/**
	 ** @test
	 **
	 ** status() must return the task stage name or empty string.
	 **/
	public function status_returns_name_or_empty(): void
	{
		$s = (object)['name' => 'Done'];
		$this->aliasMock('App\Models\TaskStage')
			->shouldReceive('find')
			->with(5)
			->andReturn($s)
			->getMock()
			->shouldReceive('find')
			->with(0)
			->andReturnNull();

		$this->assertSame('Done', ProjectReport::status(5));
		$this->assertSame('',     ProjectReport::status(0));
	}
}
