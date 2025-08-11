<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase,
};
use App\Models\{IpRestrict, User};

class IpRestrictTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** IpRestrict is fillable for ip and created_by
	 **/
	public function ip_restrict_is_fillable()
	{
		$user = User::factory()->create();
		$data = [
			'ip'         => '192.168.0.1',
			'created_by' => $user?->id,
		];

		$ir = IpRestrict::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $ir->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Uses UUID for primary key: string, non-incrementing, valid UUID
	 **/
	public function ip_restrict_primary_key_is_uuid()
	{
		$ir = IpRestrict::factory()->create();
		$key = $ir->getKey();

		$this->assertIsString($key);
		$this->assertFalse($ir->getIncrementing());
		$this->assertSame('string', $ir->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** user() relation should point to App\Models\User via created_by
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$relation = (new IpRestrict)->user();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('created_by',        $relation->getLocalKeyName());
	}
}
