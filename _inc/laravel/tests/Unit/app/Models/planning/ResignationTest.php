<?php

namespace Tests\Unit\Models;

use App\Models\Resignation;
use Tests\TestCase;

class ResignationTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Verify the mass-assignment whitelist
	 ** remains exactly as declared.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'employee_id',
			'notice_date',
			'resignation_date',
			'description',
			'created_by',
		];

		$this->assertSame($expected, (new Resignation)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The employee() relation must be HasOne
	 ** mapping Employee::id ← resignations.employee_id.
	 **/
	public function employee_relation_is_has_one(): void
	{
		$rel = (new Resignation)->employee();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',          $rel->getForeignKeyName());
		$this->assertSame('employee_id', $rel->getLocalKeyName());
	}
}
