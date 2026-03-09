<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Allowance, AllowanceOption, Employee};

class AllowanceTest extends TestCase
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
	 ** Allowance is mass assignable for employee_id, allowance_option, title, amount, type, created_by
	 **/
	public function allowance_is_fillable()
	{
		$employee = Employee::factory()->create();
		$option  = AllowanceOption::factory()->create();

		$data = [
			'employee_id'      => $employee->id,
			'allowance_option' => $option->id,
			'title'            => 'Travel Allowance',
			'amount'           => 123.456,
			'type'             => 'fixed',
			'created_by'       => 'admin_user',
		];

		$allowance = Allowance::create($data);

		$this->assertFillableMatches($data, $allowance);
	}

	/**
	 ** @test
	 **
	 ** Allowance uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function allowance_uses_uuid_for_primary_key()
	{
		$allowance = Allowance::factory()->create();

		$key = $allowance->getKey();

		$this->assertIsString($key);
		$this->assertFalse($allowance->getIncrementing());
		$this->assertSame('string', $allowance->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static allowanceType property contains expected keys and labels
	 **/
	public function allowance_type_static_property_is_correct()
	{
		$types = Allowance::$allowanceType;

		$this->assertArrayHasKey('fixed',      $types);
		$this->assertSame('Fixed',      $types['fixed']);

		$this->assertArrayHasKey('percentage', $types);
		$this->assertSame('Percentage', $types['percentage']);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Allowance)->employee();

		$this->assertInstanceOf(BelongsTo::class,    $relation);
		$this->assertSame(Employee::class,        get_class($relation->getRelated()));
		$this->assertSame('employee_id',                   $relation->getForeignKeyName());
		$this->assertSame('id',          $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** allowanceOption() relation should point to App\Models\AllowanceOption via allowance_option
	 **/
	public function allowance_option_relation_resolves_to_allowance_option_model()
	{
		$relation = (new Allowance)->allowanceOption();

		$this->assertInstanceOf(BelongsTo::class,            $relation);
		$this->assertSame(AllowanceOption::class,         get_class($relation->getRelated()));
		$this->assertSame('allowance_option',                            $relation->getForeignKeyName());
		$this->assertSame('id',              $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** amount is cast to decimal with 2 decimal places
	 **/
	public function amount_is_cast_to_decimal_two_places()
	{
		$employee = Employee::factory()->create();
		$option  = AllowanceOption::factory()->create();

		$allowance = Allowance::create([
			'employee_id'      => $employee->id,
			'allowance_option' => $option->id,
			'title'            => 'Test',
			'amount'           => 99.999,
			'type'             => 'percentage',
			'created_by'       => 'user1',
		]);

		$this->assertSame('100.00', (string) $allowance->amount);
	}
}
