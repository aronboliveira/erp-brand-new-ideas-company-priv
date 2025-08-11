<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\TrainingType;

class TrainingTypeTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** TrainingType is mass assignable for name and created_by
	 **/
	public function training_type_is_fillable()
	{
		$data = [
			'name'       => 'Orientation',
			'created_by' => 42,
		];

		$type = TrainingType::create($data);

		$this->assertEquals('Orientation', $type->name);
		$this->assertEquals(42,            $type->created_by);
	}

	/**
	 ** @test
	 **
	 ** TrainingType uses auto-incrementing integer primary key
	 **/
	public function training_type_primary_key_is_incrementing_int()
	{
		$type = TrainingType::create([
			'name'       => 'Skill Development',
			'created_by' => 99,
		]);

		$key = $type->getKey();

		$this->assertIsInt($key);
		$this->assertTrue($type->getIncrementing());
		$this->assertSame('int', $type->getKeyType());
	}

	/**
	 ** @test
	 **
	 ** TrainingType has `created_at` and `updated_at` timestamp fields
	 **/
	public function training_type_has_timestamps()
	{
		$type = TrainingType::create([
			'name'       => 'Safety Training',
			'created_by' => 7,
		]);

		$this->assertNotNull($type->created_at);
		$this->assertNotNull($type->updated_at);
		$this->assertInstanceOf(Carbon::class, $type->created_at);
		$this->assertInstanceOf(Carbon::class, $type->updated_at);
	}
}
