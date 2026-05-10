<?php

namespace Tests\Unit\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ProjectTaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

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
		$first = User::create([
			'name' => 'Project Task User A',
			'email' => 'project-task-user-a-' . Str::uuid() . '@test.local',
			'password' => bcrypt('secret'),
			'lang' => 'en',
		]);
		$second = User::create([
			'name' => 'Project Task User B',
			'email' => 'project-task-user-b-' . Str::uuid() . '@test.local',
			'password' => bcrypt('secret'),
			'lang' => 'en',
		]);

		try {
			// DB-backed fixture avoids Mockery alias order dependence once User is autoloaded.
			$task->assign_to = "not-a-uuid, {$first->id}, {$second->id}, {$first->id}";

			$result = $task->users();

			$this->assertEqualsCanonicalizing([$first->id, $second->id], $result->pluck('id')->all());
		} finally {
			DB::table('users')->whereIn('id', [$first->id, $second->id])->delete();
		}
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
		$taskId = (string) Str::uuid();
		$now = now();

		DB::table('project_tasks')->insert([
			'id' => $taskId,
			'code' => 'PT-' . Str::upper(Str::random(12)),
			'name' => 'Delete task fixture',
			'created_by' => DC::DEFAULT_UUID,
			'updated_by' => DC::DEFAULT_UUID,
			'created_at' => $now,
			'updated_at' => $now,
		]);

		try {
			// DB-backed fixture keeps deleteTask() inside its real transaction path.
			$this->assertTrue(ProjectTask::deleteTask([$taskId]));
			$this->assertDatabaseMissing('project_tasks', ['id' => $taskId]);
		} finally {
			DB::table('project_tasks')->where('id', $taskId)->delete();
		}
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
