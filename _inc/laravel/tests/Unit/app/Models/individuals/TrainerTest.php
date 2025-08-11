<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Branch, Trainer};

class TrainerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** This function ensures the model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'branch', 'firstname', 'lastname',
			'contact', 'email', 'address',
			'expertise', 'created_by'
		];
		$this->assertEquals($expected, (new Trainer())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** branches() relation returns a HasOne to the Branch model.
	 **/
	public function branches_relation_returns_hasone()
	{
		$relation = (new Trainer())->branches();
		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertEquals('id', $relation->getForeignKeyName());
		$this->assertEquals('branch', $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** branches relation actually returns the related Branch instance.
	 **/
	public function it_resolves_branches_relation_to_branch_model()
	{
		$branch = Branch::factory()->create();
		$trainer = Trainer::factory()->create(['branch' => $branch->id]);

		$this->assertInstanceOf(Branch::class, $trainer->branches);
		$this->assertEquals($branch->id, $trainer->branches->id);
	}
}
