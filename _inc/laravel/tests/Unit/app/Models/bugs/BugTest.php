<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsTo};
use App\Models\{Bug, User, BugStatus, BugComment, BugFile, Project};

class BugTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Bug is mass assignable for all fillable fields
	 **/
	public function bug_is_fillable()
	{
		$project = Project::factory()->create();
		$user   = User::factory()->create();

		$data = [
			'bug_id'      => 'BUG-001',
			'project_id'  => $project->id,
			'title'       => 'Sample Bug',
			'priority'    => 'high',
			'start_date'  => '2025-06-01',
			'due_date'    => '2025-06-10',
			'description' => 'Bug description',
			'status'      => 'open',
			'assign_to'   => $user?->id,
			'order'       => 5,
		];

		$bug = Bug::create($data);

		$this->assertFillableMatches($data, $bug);
	}

	/**
	 ** @test
	 **
	 ** static priority property contains expected values
	 **/
	public function priority_static_property_is_correct()
	{
		$expected = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];
		$this->assertSame($expected, Bug::$priority);
	}

	/**
	 ** @test
	 **
	 ** bugStatus() relation should point to BugStatus via status
	 **/
	public function bug_status_relation_resolves_to_bug_status_model()
	{
		$relation = (new Bug)->bugStatus();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(BugStatus::class,    get_class($relation->getRelated()));
		$this->assertSame('status',                $relation->getForeignKeyName());
		$this->assertSame('id',            $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** assignTo() relation should point to User via assign_to
	 **/
	public function assign_to_relation_resolves_to_user_model()
	{
		$relation = (new Bug)->assignTo();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('assign_to',                $relation->getForeignKeyName());
		$this->assertSame('id',         $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new Bug)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('created_by',                $relation->getForeignKeyName());
		$this->assertSame('id',        $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** comments() hasMany relation should point to BugComment via bug_id ordered descending
	 **/
	public function comments_relation_resolves_to_bug_comment_model()
	{
		$relation = (new Bug)->comments();

		$this->assertInstanceOf(HasMany::class,    $relation);
		$this->assertSame(BugComment::class,       get_class($relation->getRelated()));
		$this->assertSame('bug_id',                $relation->getForeignKeyName());
		$this->assertSame('id',                    $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** bugFiles() hasMany relation should point to BugFile via bug_id ordered descending
	 **/
	public function bug_files_relation_resolves_to_bug_file_model()
	{
		$relation = (new Bug)->bugFiles();

		$this->assertInstanceOf(HasMany::class,    $relation);
		$this->assertSame(BugFile::class,          get_class($relation->getRelated()));
		$this->assertSame('bug_id',                $relation->getForeignKeyName());
		$this->assertSame('id',                    $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** project() relation should point to Project via project_id
	 **/
	public function project_relation_resolves_to_project_model()
	{
		$relation = (new Bug)->project();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Project::class,      get_class($relation->getRelated()));
		$this->assertSame('project_id',                $relation->getForeignKeyName());
		$this->assertSame('id',        $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** projectBug() relation should point to User via project_id
	 **/
	public function project_bug_relation_resolves_to_user_model()
	{
		$relation = (new Bug)->projectBug();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('project_id',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** users() returns a collection of User models for given assign_to CSV
	 **/
	public function users_method_returns_collection_of_users()
	{
		$u1 = User::factory()->create();
		$u2 = User::factory()->create();

		$bug = Bug::create([
			'bug_id'      => 'B1',
			'project_id'  => Project::factory()->create()->id,
			'title'       => 'T',
			'priority'    => 'low',
			'start_date'  => '2025-06-01',
			'due_date'    => '2025-06-02',
			'description' => 'D',
			'status'      => 'open',
			'assign_to'   => "{$u1->id},{$u2->id}",
			'created_by'  => $u1->id,
			'order'       => 1,
		]);

		$users = $bug->users();
		$this->assertCount(2, $users);
		$this->assertTrue($users->contains('id', $u1->id));
		$this->assertTrue($users->contains('id', $u2->id));
	}
}
