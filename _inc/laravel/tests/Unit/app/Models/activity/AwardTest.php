<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{Award, AwardType, Employee};

class AwardTest extends TestCase
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
	 ** awardType() relation should point to App\Models\AwardType
	 **/
	public function award_type_relation_resolves_to_award_type_model()
	{
		$relation = (new Award)->awardType();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(AwardType::class, get_class($relation->getRelated()));
		$this->assertSame('award_type', $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Award)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('employee_id', $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getOwnerKeyName());
	}
}
