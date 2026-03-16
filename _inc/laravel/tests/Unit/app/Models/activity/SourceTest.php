<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{Source, User};

use Illuminate\Support\Facades\DB;
class SourceTest extends TestCase
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
	 ** Source is mass assignable for name and created_by
	 **/
	public function source_is_fillable()
	{
		$data = [
			'name' => 'Referral',
		];

		$source = Source::create($data);

		$this->assertEquals('Referral', $source->getAttributes()['name']);
	}

	/**
	 ** @test
	 **
	 ** Source uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function source_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$source = Source::create([
			'name'       => 'Web Form',
			'created_by' => $user?->id,
		]);

		$key = $source->getKey();

		$this->assertIsString($key);
		$this->assertFalse($source->getIncrementing());
		$this->assertSame('string', $source->getKeyType());
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
		$relation = (new Source)->user();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
