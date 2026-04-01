<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Branch, Department, Employee, Transfer};

class TransferTest extends TestCase
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
	 ** Transfer is mass assignable for employee_id, branch_id, department_id, transfer_date, description, and created_by
	 **/
	public function transfer_is_fillable()
	{
		$employee  = Employee::factory()->create();
		$branch    = Branch::factory()->create();
		$department = Department::factory()->create();

		$data = [
			'employee_id'    => $employee->id,
			'branch_id'      => $branch->id,
			'department_id'  => $department->id,
			'transfer_date'  => '2025-05-25',
			'description'    => 'Relocated to new branch',
			'created_by'     => $employee->id,
		];

		$transfer = Transfer::create($data);

		$this->assertFillableMatches($data, $transfer);
	}

	/**
	 ** @test
	 **
	 ** Transfer uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function transfer_uses_uuid_for_primary_key()
	{
		$transfer = Transfer::factory()->create();

		$key = $transfer->getKey();

		$this->assertIsString($key);
		$this->assertFalse($transfer->getIncrementing());
		$this->assertSame('string', $transfer->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** department() relation should point to App\Models\Department via department_id
	 **/
	public function department_relation_resolves_to_department_model()
	{
		$relation = (new Transfer)->department();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Department::class,        get_class($relation->getRelated()));
		$this->assertSame('department_id',                     $relation->getForeignKeyName());
		$this->assertSame('id',          $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** branch() relation should point to App\Models\Branch via branch_id
	 **/
	public function branch_relation_resolves_to_branch_model()
	{
		$relation = (new Transfer)->branch();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Branch::class,           get_class($relation->getRelated()));
		$this->assertSame('branch_id',                    $relation->getForeignKeyName());
		$this->assertSame('id',             $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Transfer)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class,       get_class($relation->getRelated()));
		$this->assertSame('employee_id',                  $relation->getForeignKeyName());
		$this->assertSame('id',         $relation->getOwnerKeyName());
	}
}
