<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Database\Eloquent\Relations\HasOne
};
use App\Models\{Loan, Employee, LoanOption};

class LoanTest extends TestCase
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
	 ** Loan is mass assignable for employee_id, loan_option, title, amount, start_date, end_date, reason, and created_by
	 **/
	public function loan_is_fillable()
	{
		$employee = Employee::factory()->create();
		$option  = LoanOption::factory()->create();

		$data = [
			'employee_id' => $employee->id,
			'loan_option' => $option->id,
			'title'       => 'Personal Loan',
			'amount'      => 5000.00,
			'start_date'  => '2025-06-01',
			'end_date'    => '2026-06-01',
			'reason'      => 'Home renovation',
			'created_by'  => $employee->id,
		];

		$loan = Loan::create($data);

		$this->assertFillableMatches($data, $loan);
	}

	/**
	 ** @test
	 **
	 ** Loan uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function loan_uses_uuid_for_primary_key()
	{
		$loan = Loan::factory()->create();

		$key = $loan->getKey();

		$this->assertIsString($key);
		$this->assertFalse($loan->getIncrementing());
		$this->assertSame('string', $loan->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static loanTypes property contains expected keys and labels
	 **/
	public function loan_types_static_property_is_correct()
	{
		$expected = [
			'fixed'      => 'Fixed',
			'percentage' => 'Percentage',
		];
		$this->assertSame($expected, Loan::$loanTypes);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Loan)->employee();

		$this->assertInstanceOf(BelongsTo::class,      $relation);
		$this->assertSame(Employee::class,          get_class($relation->getRelated()));
		$this->assertSame('employee_id',                     $relation->getForeignKeyName());
		$this->assertSame('id',            $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** loanOption() relation should point to App\Models\LoanOption via loan_option
	 **/
	public function loan_option_relation_resolves_to_loan_option_model()
	{
		$relation = (new Loan)->loanOption();

		$this->assertInstanceOf(BelongsTo::class,      $relation);
		$this->assertSame(LoanOption::class,        get_class($relation->getRelated()));
		$this->assertSame('loan_option',                     $relation->getForeignKeyName());
		$this->assertSame('id',            $relation->getOwnerKeyName());
	}
}
