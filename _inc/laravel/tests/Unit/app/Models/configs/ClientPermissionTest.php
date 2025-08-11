<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Support\Str
};
use App\Models\ClientPermission;

class ClientPermissionTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** ClientPermission is fillable for client_id, deal_id, and permissions
	 **/
	public function client_permission_is_fillable()
	{
		$data = [
			'client_id'   => Str::uuid()->toString(),
			'deal_id'     => Str::uuid()->toString(),
			'permissions' => '["view","edit"]',
		];

		$cp = ClientPermission::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $cp->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Uses UUID for primary key: string, non-incrementing, valid UUID
	 **/
	public function client_permission_primary_key_is_uuid()
	{
		$cp = ClientPermission::factory()->create();
		$key = $cp->getKey();

		$this->assertIsString($key);
		$this->assertFalse($cp->getIncrementing());
		$this->assertSame('string', $cp->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
