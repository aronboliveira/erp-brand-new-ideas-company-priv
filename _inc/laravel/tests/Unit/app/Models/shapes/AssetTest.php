<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use Mockery;
use Tests\TestCase;

class AssetTest extends TestCase
{
	protected function tearDown(): void
	{
		// Reset private static cache
		$prop = (new \ReflectionClass(Asset::class))->getProperty('usersData');
		$prop->setAccessible(true);
		$prop->setValue(null);
		Mockery::close();
		parent::tearDown();
	}

	/**
	 ** @test
	 *
	 ** The $fillable array must match the private
	 ** constant so that no fields are added/removed.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Asset::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Asset)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employees() must return a BelongsToMany relation.
	 **/
	public function employees_relation_is_belongs_to_many(): void
	{
		$rel = (new Asset)->employees();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
			$rel
		);
	}

	/**
	 ** @test
	 *
	 ** users() should cache and return an array of
	 ** Employee->user objects given a CSV of IDs.
	 **/
	public function users_method_returns_array_of_users_and_caches(): void
	{
		// Stub first call: Employee::where('user_id',1)->first()->user
		$employee1 = new class
		{
			public $user;
		};
		$employee1->user = (object)['id' => 1];
		$employee2 = new class
		{
			public $user;
		};
		$employee2->user = (object)['id' => 2];

		Mockery::mock('alias:App\Models\Employee')
			->shouldReceive('where')
			->once()
			->with('user_id', '1')
			->andReturnSelf()
			->getMock()
			->shouldReceive('first')
			->once()
			->andReturn($employee1);

		Mockery::mock('alias:App\Models\Employee')
			->shouldReceive('where')
			->once()
			->with('user_id', '2')
			->andReturnSelf()
			->getMock()
			->shouldReceive('first')
			->once()
			->andReturn($employee2);

		$asset = new Asset;
		$result1 = $asset->users('1,2');
		$this->assertIsArray($result1);
		$this->assertCount(2, $result1);
		$this->assertSame(1, $result1[0]->id);
		$this->assertSame(2, $result1[1]->id);
		$result2 = $asset->users('1,2');
		$this->assertSame($result1, $result2);
	}
}
