<?php

namespace Tests\Unit\Models;

use App\Models\Termination;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class TerminationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Guard the fillable whitelist.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'employee_id',
			'notice_date',
			'termination_date',
			'termination_type',
			'description',
		];

		$this->assertSame($expected, (new Termination)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee() and terminationType()
	 ** relations must both be HasOne.
	 **/
	public function relations_are_has_one(): void
	{
		$term = new Termination;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$term->employee()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$term->terminationType()
		);
	}
}
