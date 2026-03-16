<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase,
};
use App\Models\{IpRestrict, User};

use Illuminate\Support\Facades\DB;
class IpRestrictTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
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

		$this->assertFillableMatches($data, $ir);
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

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('created_by',                $relation->getForeignKeyName());
		$this->assertSame('id',        $relation->getOwnerKeyName());
	}
}
