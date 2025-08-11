<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{Award, AwardType, Employee};

class AwardTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** awardType() relation should point to App\Models\AwardType
	 **/
	public function award_type_relation_resolves_to_award_type_model()
	{
		$relation = (new Award)->awardType();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(AwardType::class, get_class($relation->getRelated()));
		$this->assertSame('id', $relation->getForeignKeyName());
		$this->assertSame('award_type', $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Award)->employee();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('id', $relation->getForeignKeyName());
		$this->assertSame('employee_id', $relation->getLocalKeyName());
	}
}
