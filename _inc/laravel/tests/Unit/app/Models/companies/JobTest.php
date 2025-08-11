<?php
// tests/Unit/Models/JobTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase,
	Support\Carbon
};
use App\Models\{Job, Branch, JobCategory, User};

class JobTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Job is mass assignable for all fillable fields
	 **/
	public function job_is_fillable()
	{
		$branch  = Branch::factory()->create();
		$category = JobCategory::factory()->create();
		$user    = User::factory()->create();

		$data = [
			'title'           => 'Dev Position',
			'description'     => 'Job Desc',
			'requirement'     => 'Reqs',
			'branch'          => $branch->id,
			'category'        => $category->id,
			'skill'           => 'PHP',
			'position'        => 2,
			'start_date'      => '2025-06-01',
			'end_date'        => '2025-06-30',
			'status'          => 'Open',
			'applicant'       => 'John Doe',
			'visibility'      => 'public',
			'code'            => 'JOB123',
			'custom_question' => 'Why?',
			'created_by'      => $user?->id,
		];

		$job = Job::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $job->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Job uses UUID for primary key: string, non-incrementing, valid UUID format
	 **/
	public function job_uses_uuid_for_primary_key()
	{
		$job = Job::factory()->create();

		$key = $job->getKey();

		$this->assertIsString($key);
		$this->assertFalse($job->getIncrementing());
		$this->assertSame('string', $job->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** start_date and end_date are cast to Carbon instances
	 **/
	public function date_attributes_are_cast_to_carbon_instances()
	{
		$branch  = Branch::factory()->create();
		$category = JobCategory::factory()->create();
		$user    = User::factory()->create();

		$job = Job::create([
			'title'      => 'Test',
			'branch'     => $branch->id,
			'category'   => $category->id,
			'start_date' => '2025-07-01',
			'end_date'   => '2025-07-31',
			'created_by' => $user?->id,
		]);

		$this->assertInstanceOf(Carbon::class, $job->start_date);
		$this->assertInstanceOf(Carbon::class, $job->end_date);
		$this->assertSame('2025-07-01', $job->start_date->toDateString());
		$this->assertSame('2025-07-31', $job->end_date->toDateString());
	}

	/**
	 ** @test
	 **
	 ** branch(), category(), and creator() relations resolve correctly
	 **/
	public function relations_resolve_to_models()
	{
		$relationBranch  = (new Job)->branch();
		$relationCategory = (new Job)->category();
		$relationCreator = (new Job)->creator();

		$this->assertInstanceOf(BelongsTo::class, $relationBranch);
		$this->assertSame(Branch::class,          get_class($relationBranch->getRelated()));
		$this->assertSame('branch',               $relationBranch->getForeignKeyName());
		$this->assertSame('id',                   $relationBranch->getOwnerKeyName());

		$this->assertInstanceOf(BelongsTo::class, $relationCategory);
		$this->assertSame(JobCategory::class,     get_class($relationCategory->getRelated()));
		$this->assertSame('category',             $relationCategory->getForeignKeyName());
		$this->assertSame('id',                   $relationCategory->getOwnerKeyName());

		$this->assertInstanceOf(BelongsTo::class, $relationCreator);
		$this->assertSame(User::class,            get_class($relationCreator->getRelated()));
		$this->assertSame('created_by',           $relationCreator->getForeignKeyName());
		$this->assertSame('id',                   $relationCreator->getOwnerKeyName());
	}
}
