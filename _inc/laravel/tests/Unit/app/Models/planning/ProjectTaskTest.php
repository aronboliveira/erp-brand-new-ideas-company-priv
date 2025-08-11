<?php

namespace Tests\Unit\Models;

use App\Models\ProjectTask;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ProjectTaskTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** countTaskChecklist() must return the
	 ** “done/total” string based on the
	 ** loaded checklist relation.
	 **/
	public function checklist_counter_formats_string(): void
	{
		$task = new ProjectTask;

		// Inject fake checklist collection.
		$task->setRelation('checklist', collect([
			(object) ['status' => 1],
			(object) ['status' => 0],
			(object) ['status' => 1],
		]));

		$this->assertSame('2/3', $task->countTaskChecklist());
	}

	/**
	 ** @test
	 *
	 ** users() must explode assign_to list and
	 ** fetch corresponding users via WHERE IN.
	 **/
	public function users_method_builds_collection(): void
	{
		$task = new ProjectTask;
		$task->assign_to = '1,2,3';

		// Fake User::whereIn() chain
		$fakeUsers = new Collection([(object)['id' => 1]]);
		Mockery::mock('alias:App\Models\User')
			->shouldReceive('whereIn')
			->once()
			->with('id', ['1', '2', '3'])
			->andReturnSelf()
			->getMock()
			->shouldReceive('get')
			->once()
			->andReturn($fakeUsers);

		$this->assertSame($fakeUsers, $task->users());
	}

	/**
	 ** @test
	 *
	 ** deleteTask() should wrap calls inside
	 ** DB::transaction and return true when
	 ** no exception is thrown.
	 **/
	public function delete_task_returns_true_on_success(): void
	{
		// Stub many collaborators to bypass DB.
		Mockery::mock('alias:Illuminate\Support\Facades\DB')
			->shouldReceive('transaction')
			->once()
			->andReturnUsing(fn ($closure) => $closure());

		Mockery::mock('alias:App\Models\ProjectTask')
			->shouldReceive('find')->andReturn(null); // each loop skip
		Mockery::mock('alias:App\Models\TaskFile')
			->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('pluck')->andReturnSelf()
			->getMock()->shouldReceive('toArray')->andReturn([]);
		Mockery::mock('alias:App\Models\Utility')
			->shouldReceive('checkFileExistsnDelete');

		$this->assertTrue(ProjectTask::deleteTask([7, 8]));
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
