<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{Department, Branch};

class DepartmentTest extends TestCase
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
	 ** Department is mass assignable for branch_id, created_by, and name
	 **/
	public function department_is_fillable()
	{
		$branch = Branch::factory()->create();

		$data = [
			'branch_id'  => $branch->id,
			'name'       => 'HR',
		];

		$dept = Department::create($data);

		$this->assertFillableMatches($data, $dept);
	}

	/**
	 ** @test
	 **
	 ** Department uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function department_uses_uuid_for_primary_key()
	{
		$dept = Department::factory()->create();
		$key = $dept->getKey();

		$this->assertIsString($key);
		$this->assertFalse($dept->getIncrementing());
		$this->assertSame('string', $dept->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** branch() relation should point to Branch via branch_id
	 **/
	public function branch_relation_resolves_to_branch_model()
	{
		$relation = (new Department)->branch();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Branch::class,       get_class($relation->getRelated()));
		$this->assertSame('branch_id',                $relation->getForeignKeyName());
		$this->assertSame('id',         $relation->getOwnerKeyName());
	}
}
