<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{Complaint, Employee};

class ComplaintTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Complaint is mass assignable for all fillable fields
	 **/
	public function complaint_is_fillable()
	{
		$against = Employee::factory()->create();
		$from   = Employee::factory()->create();
		$owner  = Employee::factory()->create();

		$data = [
			'complaint_against' => $against->id,
			'complaint_from'    => $from->id,
			'employee_id'       => $owner->id,
			'title'             => 'Late Delivery',
			'description'       => 'Package arrived 3 days late',
			'created_by'        => 'admin_user',
			'complaint_date'    => '2025-05-22',
		];

		$complaint = Complaint::create($data);

		foreach ($data as $key => $value) {
			$this->assertEquals($value, $complaint->$key);
		}
	}

	/**
	 ** @test
	 **
	 ** Complaint uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function complaint_uses_uuid_for_primary_key()
	{
		$complaint = Complaint::create([
			'complaint_against' => Employee::factory()->create()->id,
			'complaint_from'    => Employee::factory()->create()->id,
			'employee_id'       => Employee::factory()->create()->id,
			'title'             => 'Test',
			'description'       => 'Desc',
			'created_by'        => 'user1',
			'complaint_date'    => '2025-05-22',
		]);

		$key = $complaint->getKey();

		$this->assertIsString($key);
		$this->assertFalse($complaint->getIncrementing());
		$this->assertSame('string', $complaint->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** complaintAgainst() relation should point to App\Models\Employee via complaint_against
	 **/
	public function complaint_against_relation_resolves_to_employee_model()
	{
		$relation = (new Complaint)->complaintAgainst();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('complaint_against', $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** complaintFrom() relation should point to App\Models\Employee via complaint_from
	 **/
	public function complaint_from_relation_resolves_to_employee_model()
	{
		$relation = (new Complaint)->complaintFrom();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('id',             $relation->getForeignKeyName());
		$this->assertSame('complaint_from', $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee via employee_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Complaint)->employee();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('id',          $relation->getForeignKeyName());
		$this->assertSame('employee_id', $relation->getLocalKeyName());
	}
}
