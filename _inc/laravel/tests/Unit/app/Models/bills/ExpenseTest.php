<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{HasOne, BelongsTo};
use App\Models\{Expense, Project, ProjectTask, User};

class ExpenseTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Expense is mass assignable for name, date, description, amount, attachment, project_id, task_id, and created_by
	 **/
	public function expense_is_fillable()
	{
		$project = Project::factory()->create();
		$task   = ProjectTask::factory()->create();
		$user   = User::factory()->create();

		$data = [
			'name'        => 'Office Supplies',
			'date'        => '2025-05-28',
			'description' => 'Purchased pens and notebooks',
			'amount'      => 45.75,
			'attachment'  => '/receipts/supplies.pdf',
			'project_id'  => $project->id,
			'task_id'     => $task->id,
			'created_by'  => $user?->id,
		];

		$expense = Expense::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $expense->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Expense uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function expense_uses_uuid_for_primary_key()
	{
		$expense = Expense::factory()->create();

		$key = $expense->getKey();

		$this->assertIsString($key);
		$this->assertFalse($expense->getIncrementing());
		$this->assertSame('string', $expense->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** project() relation should point to App\Models\Project via project_id
	 **/
	public function project_relation_resolves_to_project_model()
	{
		$relation = (new Expense)->project();

		$this->assertInstanceOf(HasOne::class,    $relation);
		$this->assertSame(Project::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                    $relation->getForeignKeyName());
		$this->assertSame('project_id',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** task() relation should point to App\Models\ProjectTask via task_id
	 **/
	public function task_relation_resolves_to_project_task_model()
	{
		$relation = (new Expense)->task();

		$this->assertInstanceOf(HasOne::class,       $relation);
		$this->assertSame(ProjectTask::class,        get_class($relation->getRelated()));
		$this->assertSame('id',                      $relation->getForeignKeyName());
		$this->assertSame('task_id',                 $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new Expense)->createdBy();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(User::class,               get_class($relation->getRelated()));
		$this->assertSame('created_by',              $relation->getForeignKeyName());
		$this->assertSame('id',                      $relation->getOwnerKeyName());
	}
}
