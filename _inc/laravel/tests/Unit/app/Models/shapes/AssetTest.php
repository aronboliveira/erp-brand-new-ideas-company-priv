<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class AssetTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

	use SafeAliasMock;

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}

	/**
	 ** @test
	 *
	 ** The $fillable array must match the private
	 ** constant so that no fields are added/removed.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'serial',
			'category',
			'type',
			'name',
			'employee_id',
			'purchase_date',
			'supported_date',
			'amount',
			'description',
			'purpose',
			'order',
			'transaction',
			'signed_by',
			'signed_by_name',
			'attachments',
			'metadata',
			'tags',
		];

		$this->assertSame($expected, (new Asset)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employees() must return a BelongsToMany relation.
	 **/
	public function employees_relation_is_belongs_to_many(): void
	{
		$rel = (new Asset)->employees();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
			$rel
		);
	}

	/**
	 ** @test
	 *
	 ** Asset should expose employee relations through explicit relation names.
	 **/
	public function legacy_users_method_is_not_part_of_asset_contract(): void
	{
		$this->assertFalse(method_exists(Asset::class, 'users'));
	}
}
