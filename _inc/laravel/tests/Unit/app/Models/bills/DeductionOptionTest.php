<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{DeductionOption, User};

class DeductionOptionTest extends TestCase
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
	 ** DeductionOption is mass assignable for name and created_by
	 **/
	public function deduction_option_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'name'       => 'Health Insurance',
			'created_by' => $user?->id,
		];

		$option = DeductionOption::create($data);

		$this->assertEquals('Health Insurance', $option->name);
		$this->assertEquals($user?->id,          $option->created_by);
	}

	/**
	 ** @test
	 **
	 ** DeductionOption uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function deduction_option_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$option = DeductionOption::create([
			'name'       => 'Pension Contribution',
			'created_by' => $user?->id,
		]);

		$key = $option->getKey();

		$this->assertIsString($key);
		$this->assertFalse($option->getIncrementing());
		$this->assertSame('string', $option->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** createdBy() relation should point to App\Models\User via created_by
	 **/
	public function created_by_relation_resolves_to_user_model()
	{
		$relation = (new DeductionOption)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
