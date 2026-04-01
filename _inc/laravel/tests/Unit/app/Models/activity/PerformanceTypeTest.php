<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PerformanceType;
use App\Models\Competencies;

class PerformanceTypeTest extends TestCase
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
	 ** PerformanceType is mass assignable for name and created_by
	 **/
	public function performance_type_is_fillable()
	{
		$data = [
			'name'       => 'Technical Skills',
		];

		$pt = PerformanceType::create($data);

		$this->assertEquals('Technical Skills', $pt->name);
	}

	/**
	 ** @test
	 **
	 ** PerformanceType uses UUIDs for its primary key:
	 ** string type, non-incrementing, valid UUID format
	 **/
	public function performance_type_uses_uuid_for_primary_key()
	{
		$pt = PerformanceType::create([
			'name'       => 'Soft Skills',
		]);

		$key = $pt->getKey();

		$this->assertIsString($key);
		$this->assertFalse($pt->getIncrementing());
		$this->assertSame('string', $pt->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** types() relation should point to Competencies model
	 **/
	public function types_relation_resolves_to_competencies_model()
	{
		$relation = (new PerformanceType)->types();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(
			Competencies::class,
			get_class($relation->getRelated())
		);
		$this->assertSame('type', $relation->getForeignKeyName());
		$this->assertSame('id',   $relation->getLocalKeyName());
	}
}
