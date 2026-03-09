<?php

namespace Tests\Unit\Models;

use App\Models\Travel;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Fillable list must match constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'employee_id',
			'start_date',
			'end_date',
			'purpose_of_visit',
			'place_of_visit',
			'description',
		];

		$this->assertSame($expected, (new Travel)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee() is a HasOne relation.
	 **/
	public function employee_relation_is_has_one(): void
	{
		$rel = (new Travel)->employee();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
	}
}
