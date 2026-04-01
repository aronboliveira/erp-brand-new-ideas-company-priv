<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{SaturationDeduction, DeductionOption, Employee};

class SaturationDeductionTest extends TestCase
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
	 ** SaturationDeduction is mass assignable for employee_id, deduction_option, title, and amount
	 **/
	public function saturation_deduction_is_fillable()
	{
		$employee = Employee::factory()->create();
		$option  = DeductionOption::factory()->create();

		$data = [
			'employee_id'      => $employee->id,
			'deduction_option' => $option->id,
			'title'            => 'Health Deduction',
			'amount'           => 123.45,
		];

		$sd = SaturationDeduction::create($data);

		$this->assertFillableMatches($data, $sd);
	}

	/**
	 ** @test
	 **
	 ** SaturationDeduction uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$sd = SaturationDeduction::factory()->create();
		$key = $sd->getKey();

		$this->assertIsString($key);
		$this->assertFalse($sd->getIncrementing());
		$this->assertSame('string', $sd->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static $saturationDeductionType property contains expected keys and labels
	 **/
	public function saturation_deduction_type_static_property_is_correct()
	{
		$expected = [
			'fixed'      => 'Fixed',
			'percentage' => 'Percentage',
		];
		$this->assertSame($expected, SaturationDeduction::$saturationDeductionType);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new SaturationDeduction)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class,     get_class($relation->getRelated()));
		$this->assertSame('employee_id',                $relation->getForeignKeyName());
		$this->assertSame('id',       $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** deductionOption() relation should point to App\Models\DeductionOption via deduction_option
	 **/
	public function deduction_option_relation_resolves_to_deduction_option_model()
	{
		$relation = (new SaturationDeduction)->deductionOption();

		$this->assertInstanceOf(HasOne::class,        $relation);
		$this->assertSame(DeductionOption::class,  get_class($relation->getRelated()));
		$this->assertSame('id',                    $relation->getForeignKeyName());
		$this->assertSame('deduction_option',      $relation->getLocalKeyName());
	}
}
