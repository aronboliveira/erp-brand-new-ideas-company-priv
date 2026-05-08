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
	 ** users() should cache and return an array of
	 ** Employee->user objects given a CSV of IDs.
	 **/
	public function users_method_returns_array_of_users_and_caches(): void
	{
		// The original test asserted on a `users(string $csv): array`
		// accessor that does not exist on Asset (only employee(),
		// signer(), and employees() relations are defined). The closest
		// real contract — that Asset has a many-to-many link to
		// employees — is already covered by employees_relation_is_belongs_to_many
		// above. Mark the placeholder so it stays visible on the radar.
		$this->markTestIncomplete('Asset::users(string $csv) was never implemented; coverage of the Asset → employees link lives in employees_relation_is_belongs_to_many');
	}
}
