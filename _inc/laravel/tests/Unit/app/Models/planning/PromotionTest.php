<?php

namespace Tests\Unit\Models;

use App\Models\Promotion;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class PromotionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Guard the $fillable array against drift.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'employee_id',
			'designation_id',
			'promotion_title',
			'promotion_date',
			'description',
		];

		$this->assertSame($expected, (new Promotion)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** designation() must be HasOne via
	 ** designations.id ← promotions.designation_id.
	 **/
	public function designation_relation_is_has_one(): void
	{
		$rel = (new Promotion)->designation();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('designation_id',              $rel->getForeignKeyName());
		$this->assertSame('id',  $rel->getOwnerKeyName());
	}
}
