<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Branch, Trainer};

class TrainerTest extends TestCase
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
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'user_id',
			'branch',
			'employee_id',
			'firstname',
			'lastname',
			'contact',
			'email',
			'address',
			'presentation',
			'expertise',
			'registration',
			'qualifications',
			'certificates',
		];
		$this->assertEquals($expected, (new Trainer())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** branch() relation returns a BelongsTo to the Branch model.
	 **/
	public function branch_relation_returns_belongsto()
	{
		$relation = (new Trainer())->branch();
		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertEquals('branch', $relation->getForeignKeyName());
		$this->assertEquals('id', $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** branch relation actually returns the related Branch instance.
	 **/
	public function it_resolves_branch_relation_to_branch_model()
	{
		$branch = Branch::factory()->create();
		$trainer = Trainer::factory()->create(['branch' => $branch->id]);

		$resolved = $trainer->branch()->first();
		$this->assertInstanceOf(Branch::class, $resolved);
		$this->assertEquals($branch->id, $resolved->id);
	}
}
