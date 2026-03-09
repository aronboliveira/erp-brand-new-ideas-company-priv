<?php

namespace Tests\Unit\app\Models\activity;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{Commission, Employee};

class CommissionTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Commission is mass assignable for employee_id, title, amount, and type
	 **/
	public function commission_is_fillable()
	{
		$data = [
			'employee_id' => 'emp-123',
			'title'       => 'Referral Bonus',
			'amount'      => 250.75,
			'type'        => 'fixed',
		];

		$commission = Commission::create($data);

		$this->assertEquals('emp-123',       $commission->employee_id);
		$this->assertEquals('Referral Bonus', $commission->title);
		$this->assertEquals(250.75,           $commission->amount);
		$this->assertEquals('fixed',          $commission->type);
	}

	/**
	 ** @test
	 **
	 ** Commission uses UUID for primary key: string, non-incrementing, and valid UUID format
	 **/
	public function commission_uses_uuid_for_primary_key()
	{
		$commission = Commission::create([
			'employee_id' => 'emp-456',
			'title'       => 'Performance Bonus',
			'amount'      => 100.00,
			'type'        => 'percentage',
			'created_by'  => 'system',
		]);

		$key = $commission->getKey();

		$this->assertIsString($key);
		$this->assertFalse($commission->getIncrementing());
		$this->assertSame('string', $commission->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** Commission::$commissionType contains the expected keys and labels
	 **/
	public function commission_type_static_property_is_correct()
	{
		$types = Commission::$commissionType;

		$this->assertArrayHasKey('fixed',      $types);
		$this->assertSame('Fixed',      $types['fixed']);

		$this->assertArrayHasKey('percentage', $types);
		$this->assertSame('Percentage', $types['percentage']);
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to App\Models\Employee
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Commission)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class, get_class($relation->getRelated()));
		$this->assertSame('employee_id',          $relation->getForeignKeyName());
		$this->assertSame('id', $relation->getOwnerKeyName());
	}
}
