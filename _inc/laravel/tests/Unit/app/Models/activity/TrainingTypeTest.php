<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\TrainingType;

use Illuminate\Support\Facades\DB;
class TrainingTypeTest extends TestCase
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
	 ** TrainingType is mass assignable for name and created_by
	 **/
	public function training_type_is_fillable()
	{
		$data = [
			'name' => 'Orientation',
		];

		$type = TrainingType::create($data);

		$this->assertEquals('Orientation', $type->getAttributes()['name']);
	}

	/**
	 ** @test
	 **
	 ** TrainingType uses UUID primary key: string, non-incrementing
	 **/
	public function training_type_primary_key_is_incrementing_int()
	{
		$type = TrainingType::create([
			'name' => 'Skill Development',
		]);

		$key = $type->getKey();

		$this->assertIsString($key);
		$this->assertFalse($type->getIncrementing());
		$this->assertSame('string', $type->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** TrainingType has `created_at` and `updated_at` timestamp fields
	 **/
	public function training_type_has_timestamps()
	{
		$type = TrainingType::create([
			'name' => 'Safety Training',
		]);

		$this->assertNotNull($type->created_at);
		$this->assertNotNull($type->updated_at);
		$this->assertInstanceOf(Carbon::class, $type->created_at);
		$this->assertInstanceOf(Carbon::class, $type->updated_at);
	}
}
