<?php

namespace Tests\Unit\Models;

use App\Models\ProjectTask;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class ProjectTaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	use SafeAliasMock;

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
		$this->aliasMock('App\Models\User')
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
		$this->aliasMock('Illuminate\Support\Facades\DB')
			->shouldReceive('transaction')
			->once()
			->andReturnUsing(fn ($closure) => $closure());

		$this->aliasMock('App\Models\ProjectTask')
			->shouldReceive('find')->andReturn(null); // each loop skip
		$this->aliasMock('App\Models\TaskFile')
			->shouldReceive('where')->andReturnSelf()
			->getMock()->shouldReceive('pluck')->andReturnSelf()
			->getMock()->shouldReceive('toArray')->andReturn([]);
		$this->aliasMock('App\Models\Utility')
			->shouldReceive('checkFileExistsnDelete');

		$this->assertTrue(ProjectTask::deleteTask([7, 8]));
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
