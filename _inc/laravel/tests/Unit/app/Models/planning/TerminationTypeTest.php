<?php

namespace Tests\Unit\Models;

use App\Models\TerminationType;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class TerminationTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Verify $fillable array stays intact.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'name',
			'description',
		];

		$this->assertSame($expected, (new TerminationType)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** createdBy() relation must be HasOne.
	 **/
	public function created_by_relation_is_has_one(): void
	{
		$rel = (new TerminationType)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
	}
}
