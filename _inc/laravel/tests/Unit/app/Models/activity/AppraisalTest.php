<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Appraisal;
use App\Models\Branch;
use App\Models\Employee;

class AppraisalTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** branches() relation should point to App\Models\Branch
	 **/
	public function branches_relation_resolves_to_branch_model()
	{
		$relation = (new Appraisal)->branches();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Branch::class, get_class($relation->getRelated()));
		$this->assertSame('branch', $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** employees() relation should point to App\Models\Employee
	 **/
	public function employees_relation_resolves_to_employee_model()
	{
		$relation = (new Appraisal)->employees();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('employee', $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getOwnerKeyName());
	}
}
