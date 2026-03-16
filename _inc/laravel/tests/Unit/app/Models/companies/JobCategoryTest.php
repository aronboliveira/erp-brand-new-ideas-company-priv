<?php
// tests/Unit/Models/JobCategoryTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{JobCategory, User};

use Illuminate\Support\Facades\DB;
class JobCategoryTest extends TestCase
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
	 ** JobCategory is mass assignable for title and created_by
	 **/
	public function job_category_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'title'      => 'Engineering',
			'created_by' => $user?->id,
		];

		$category = JobCategory::create($data);

		$this->assertFillableMatches($data, $category);
	}

	/**
	 ** @test
	 **
	 ** JobCategory uses UUID for primary key
	 **/
	public function job_category_uses_uuid_for_primary_key()
	{
		$category = JobCategory::factory()->create();

		$key = $category->getKey();

		$this->assertIsString($key);
		$this->assertFalse($category->getIncrementing());
		$this->assertSame('string', $category->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** creator() relation should point to User via created_by
	 **/
	public function creator_relation_resolves_to_user_model()
	{
		$relation = (new JobCategory)->creator();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
