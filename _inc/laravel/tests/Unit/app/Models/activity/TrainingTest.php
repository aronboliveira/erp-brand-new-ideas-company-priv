<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Training;
use App\Models\Branch;
use App\Models\TrainingType;
use App\Models\Employee;
use App\Models\Trainer;

class TrainingTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Training is mass assignable for all fillable fields
	 **/
	public function training_is_fillable()
	{
		$data = [
			'branch'          => 'branch-123',
			'trainer_option'  => 'Internal',
			'training_type'   => 'type-456',
			'trainer'         => 'trainer-789',
			'training_cost'   => 1500.50,
			'employee'        => 'emp-321',
			'start_date'      => '2025-06-01',
			'end_date'        => '2025-06-05',
			'description'     => 'Safety training',
			'remarks'         => 'Bring ID badge',
			'performance'     => 'Satisfactory',
			'status'          => 'Pending',
			'created_by'      => 'user-999',
		];

		$training = Training::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $training->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Training uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function training_uses_uuid_for_primary_key()
	{
		$training = Training::factory()->create();

		$key = $training->getKey();

		$this->assertIsString($key);
		$this->assertFalse($training->getIncrementing());
		$this->assertSame('string', $training->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** $options static property contains exactly 'Internal' and 'External'
	 **/
	public function options_static_property_is_correct()
	{
		$this->assertSame(
			['Internal', 'External'],
			Training::$options
		);
	}

	/**
	 ** @test
	 **
	 ** $performance static property contains the five expected levels
	 **/
	public function performance_static_property_is_correct()
	{
		$this->assertSame(
			['Not Concluded', 'Satisfactory', 'Average', 'Poor', 'Excellent'],
			Training::$performance
		);
	}

	/**
	 ** @test
	 **
	 ** $status static property contains the four expected statuses
	 **/
	public function status_static_property_is_correct()
	{
		$this->assertSame(
			['Pending', 'Started', 'Completed', 'Terminated'],
			Training::$status
		);
	}

	/**
	 ** @test
	 **
	 ** branches() relation should point to App\Models\Branch
	 **/
	public function branches_relation_resolves_to_branch_model()
	{
		$relation = (new Training)->branches();

		$this->assertInstanceOf(HasOne::class, get_class($relation));
		$this->assertSame(Branch::class,        get_class($relation->getRelated()));
		$this->assertSame('id',                 $relation->getForeignKeyName());
		$this->assertSame('branch',             $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** types() relation should point to App\Models\TrainingType
	 **/
	public function types_relation_resolves_to_training_type_model()
	{
		$relation = (new Training)->types();

		$this->assertInstanceOf(HasOne::class,        get_class($relation));
		$this->assertSame(TrainingType::class,        get_class($relation->getRelated()));
		$this->assertSame('id',                       $relation->getForeignKeyName());
		$this->assertSame('training_type',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** employees() relation should point to App\Models\Employee
	 **/
	public function employees_relation_resolves_to_employee_model()
	{
		$relation = (new Training)->employees();

		$this->assertInstanceOf(HasOne::class,        get_class($relation));
		$this->assertSame(Employee::class,            get_class($relation->getRelated()));
		$this->assertSame('id',                       $relation->getForeignKeyName());
		$this->assertSame('employee',                 $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** trainers() relation should point to App\Models\Trainer
	 **/
	public function trainers_relation_resolves_to_trainer_model()
	{
		$relation = (new Training)->trainers();

		$this->assertInstanceOf(HasOne::class,        get_class($relation));
		$this->assertSame(Trainer::class,             get_class($relation->getRelated()));
		$this->assertSame('id',                       $relation->getForeignKeyName());
		$this->assertSame('trainer',                  $relation->getLocalKeyName());
	}
}
