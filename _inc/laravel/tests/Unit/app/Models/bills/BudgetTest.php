<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase,
	Support\Carbon
};
use App\Models\{Budget, User};

class BudgetTest extends TestCase
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
	 ** Budget is mass assignable for name, period, and created_by
	 **/
	public function budget_is_fillable()
	{
		$user = User::factory()->create();

		$budget = Budget::create([
			'name'        => 'Q1 Budget',
			'period'      => 'quarterly',
			'created_by'  => $user?->id,
		]);

		$this->assertEquals('Q1 Budget',    $budget->name);
		$this->assertEquals('quarterly',    $budget->period);
		$this->assertEquals($user?->id,      $budget->created_by);
	}

	/**
	 ** @test
	 **
	 ** start_date and end_date are cast to Carbon instances
	 **/
	public function date_attributes_are_cast_to_carbon()
	{
		$start = '2025-01-01';
		$end  = '2025-03-31';
		$budget = Budget::create([
			'name'        => 'Test',
			'period'      => 'monthly',
			'start_date'  => $start,
			'end_date'    => $end,
			'created_by'  => User::factory()->create()->id,
		]);

		$this->assertInstanceOf(Carbon::class, $budget->start_date);
		$this->assertInstanceOf(Carbon::class, $budget->end_date);
		$this->assertSame($start, $budget->start_date->toDateString());
		$this->assertSame($end,   $budget->end_date->toDateString());
	}

	/**
	 ** @test
	 **
	 ** income_data and expense_data are cast to arrays
	 **/
	public function data_attributes_are_cast_to_array()
	{
		$income = ['sales' => 1000];
		$expense = ['rent' => 300];
		$budget = Budget::create([
			'name'         => 'Test',
			'period'       => 'monthly',
			'income_data'  => $income,
			'expense_data' => $expense,
			'created_by'   => User::factory()->create()->id,
		]);

		$this->assertIsArray($budget->income_data);
		$this->assertSame($income,  $budget->income_data);
		$this->assertIsArray($budget->expense_data);
		$this->assertSame($expense, $budget->expense_data);
	}

	/**
	 ** @test
	 **
	 ** $period static property contains the expected keys and labels
	 **/
	public function period_static_property_is_correct()
	{
		$expected = [
			'code',
			'name',
			'type',
			'period',
			'frequency',
			'from',
			'start_date',
			'to',
			'end_date',
			'amount',
			'currency',
			'exchange_rate',
			'warn_threshold',
			'critical_warning_threshold',
			'status',
			'submitted_by',
			'submitted_at',
			'approved_by',
			'approved_at',
			'rejected_by',
			'rejected_at',
			'description',
			'notes',
			'income_data',
			'expense_data',
			'project_id',
			'contract_id',
			'company',
			'branch',
			'department',
			'bank_transfers',
			'transactions',
			'card_notes',
			'receipts',
			'attachments',
			'metadata',
		];
		$this->assertSame($expected, (new Budget())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** getAvailabilityDate returns "M-Y - M-Y" when both dates present
	 **/
	public function availability_date_shows_both_dates()
	{
		$budget = new Budget([
			'start_date' => '2025-01-01',
			'end_date'   => '2025-03-01',
		]);

		$this->assertSame('Jan-2025 - Mar-2025', $budget->availability_date);
	}

	/**
	 ** @test
	 **
	 ** getAvailabilityDate shows only start date if end_date is null
	 **/
	public function availability_date_shows_only_start_date()
	{
		$budget = new Budget([
			'start_date' => '2025-02-15',
		]);

		$this->assertSame('Feb-2025', $budget->availability_date);
	}

	/**
	 ** @test
	 **
	 ** getAvailabilityDate shows only end date if start_date is null
	 **/
	public function availability_date_shows_only_end_date()
	{
		$budget = new Budget([
			'end_date' => '2025-04-20',
		]);

		$this->assertSame('Apr-2025', $budget->availability_date);
	}

	/**
	 ** @test
	 **
	 ** getAvailabilityDate returns empty string if no dates set
	 **/
	public function availability_date_is_empty_when_no_dates()
	{
		$budget = new Budget();
		$this->assertSame('', $budget->availability_date);
	}

	/**
	 ** @test
	 **
	 ** percentage returns formatted budget/actual percentage when actual > 0
	 **/
	public function percentage_returns_correct_value_for_positive_actual()
	{
		$this->assertSame('25.00', Budget::percentage(200.0, 50.0));
	}

	/**
	 ** @test
	 **
	 ** percentage returns "0.00" when actual is zero or negative
	 **/
	public function percentage_returns_zero_when_actual_not_positive()
	{
		$this->assertSame('0.00', Budget::percentage(0.0, 100.0));
		$this->assertSame('0.00', Budget::percentage(-50.0, 25.0));
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new Budget)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
