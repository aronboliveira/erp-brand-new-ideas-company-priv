<?php

namespace Tests\Unit\Models;

use App\Models\Holiday;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class HolidayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure the $fillable list mirrors the
	 ** private constant so refactors won’t
	 ** silently break mass-assignment rules.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'code',
			'name',
			'date',
			'end_date',
			'occasion',
			'type',
			'observance',
			'recurring',
			'event',
			'award',
			'coupon',
			'project',
			'task',
			'meeting',
			'goal',
			'reduced_shift_by',
			'countries',
			'states',
			'designations',
			'departments',
			'branches',
			'companies',
			'vendors',
			'customers',
		];

		$this->assertSame($expected, (new Holiday)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The user() relation must be HasOne from
	 ** holidays.created_by → users.id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new Holiday)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by',          $rel->getForeignKeyName());
		$this->assertSame('id',  $rel->getOwnerKeyName());
	}
}
