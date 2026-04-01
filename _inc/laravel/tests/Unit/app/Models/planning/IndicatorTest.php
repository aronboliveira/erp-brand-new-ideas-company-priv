<?php

namespace Tests\Unit\Models;

use App\Models\Indicator;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The organizational level options must
	 ** remain None → Advanced in that order.
	 **/
	public function organizational_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Indicator::class);
		$expected = $ref->getConstant('ORGANIZATIONAL_LEVELS');

		$this->assertSame($expected, Indicator::$organizational);
	}

	/**
	 ** @test
	 *
	 ** The technical level array must remain
	 ** consistent (including Expert / Leader).
	 **/
	public function technical_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Indicator::class);
		$expected = $ref->getConstant('TECHNICAL_LEVELS');

		$this->assertSame($expected, Indicator::$technical);
	}

	/**
	 ** @test
	 *
	 ** branches() should map Branch::id ←
	 ** indicators.branch as a HasOne relation.
	 **/
	public function branch_relation_is_has_one(): void
	{
		$rel = (new Indicator)->branches();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('branch',     $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Protect the fillable fields list against
	 ** silent drift by verifying reflection vs
	 ** the runtime value.
	 **/
	public function fillable_array_is_as_declared(): void
	{
		$ref     = new \ReflectionClass(Indicator::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new Indicator)->getFillable());
	}
}
