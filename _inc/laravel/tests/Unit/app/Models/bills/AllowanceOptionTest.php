<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{AllowanceOption, User};

class AllowanceOptionTest extends TestCase
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
	 ** AllowanceOption is mass assignable for name and created_by
	 **/
	public function allowance_option_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'name'       => 'Transport Allowance',
			'created_by' => $user?->id,
		];

		$option = AllowanceOption::create($data);

		$this->assertEquals('Transport Allowance', $option->name);
		$this->assertEquals($user?->id,             $option->created_by);
	}

	/**
	 ** @test
	 **
	 ** AllowanceOption uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function allowance_option_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$option = AllowanceOption::create([
			'name'       => 'Meal Allowance',
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
		$relation = (new AllowanceOption)->createdBy();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
