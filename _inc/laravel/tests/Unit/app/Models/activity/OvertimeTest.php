<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\Overtime;
use App\Models\Employee;

use Illuminate\Support\Facades\DB;
class OvertimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Overtime is mass assignable for employee_id, title, number_of_days, hours, rate, type, and created_by
	 **/
	public function overtime_is_fillable()
	{
		$employee = Employee::factory()->create();

		$data = [
			'employee_id'    => $employee->id,
			'title'          => 'Overtime Work',
			'number_of_days' => 2,
			'hours'          => 8,
			'rate'           => 50.00,
			'type'           => 'weekend',
			'created_by'     => 'admin_user',
		];

		$overtime = Overtime::create($data);

		$this->assertFillableMatches($data, $overtime);
	}

	/**
	 ** @test
	 **
	 ** Overtime uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function overtime_uses_uuid_for_primary_key()
	{
		$employee = Employee::factory()->create();

		$overtime = Overtime::create([
			'employee_id'    => $employee->id,
			'title'          => 'Night Shift',
			'number_of_days' => 1,
			'hours'          => 5,
			'rate'           => 75.00,
			'type'           => 'holiday',
			'created_by'     => 'user_123',
		]);

		$key = $overtime->getKey();

		$this->assertIsString($key);
		$this->assertFalse($overtime->getIncrementing());
		$this->assertSame('string', $overtime->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Overtime)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class,      get_class($relation->getRelated()));
		$this->assertSame('employee_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',        $relation->getOwnerKeyName());
	}
}
