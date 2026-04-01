<?php

/**
 * tests/Unit/Models/SetSalaryTest.php
 *
 * Unit-tests for App\Models\SetSalary
 */

namespace Tests\Unit\Models;

use App\Models\SetSalary;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetSalaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must match the
	 ** private constant list exactly, so we
	 ** guard against silent drift.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'employee_id',
			'salary_type',
			'salary',
			'frequency',
			'month_day_limit',
		];

		$this->assertSame($expected, (new SetSalary)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee() must be a BelongsTo relation
	 ** using employees.id ← set_salaries.employee_id.
	 **/
	public function employee_relation_is_belongs_to(): void
	{
		$rel = (new SetSalary)->employee();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('employee_id', $rel->getForeignKeyName());
		$this->assertSame('id',          $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** salaryType() must be a BelongsTo link
	 ** payslip_types.id ← set_salaries.salary_type.
	 **/
	public function salary_type_relation_is_belongs_to(): void
	{
		$rel = (new SetSalary)->salaryType();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('salary_type', $rel->getForeignKeyName());
		$this->assertSame('id',          $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** creator() must map users.id ←
	 ** set_salaries.created_by.
	 **/
	public function creator_relation_is_belongs_to(): void
	{
		$rel = (new SetSalary)->creator();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by', $rel->getForeignKeyName());
		$this->assertSame('id',         $rel->getOwnerKeyName());
	}
}
