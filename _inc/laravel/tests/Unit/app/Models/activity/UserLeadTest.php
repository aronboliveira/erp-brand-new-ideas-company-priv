<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserLead;
use App\Models\User;
use App\Models\Lead;

class UserLeadTest extends TestCase
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
	 ** UserLead is mass assignable for user_id and lead_id
	 **/
	public function user_lead_is_fillable()
	{
		$user = User::factory()->create();
		$lead = Lead::factory()->create();

		$data = [
			'user_id' => $user?->id,
			'lead_id' => $lead->id,
		];

		$userLead = UserLead::create($data);

		$this->assertEquals($user?->id, $userLead->user_id);
		$this->assertEquals($lead->id, $userLead->lead_id);
	}

	/**
	 ** @test
	 **
	 ** UserLead uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function user_lead_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();
		$lead = Lead::factory()->create();

		$userLead = UserLead::create([
			'user_id' => $user?->id,
			'lead_id' => $lead->id,
		]);

		$key = $userLead->getKey();

		$this->assertIsString($key);
		$this->assertFalse($userLead->getIncrementing());
		$this->assertSame('string', $userLead->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** lead() relation should point to App\Models\Lead via lead_id
	 **/
	public function lead_relation_resolves_to_lead_model()
	{
		$relation = (new UserLead)->lead();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Lead::class,            get_class($relation->getRelated()));
		$this->assertSame('lead_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** user() relation should point to App\Models\User via user_id
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$relation = (new UserLead)->user();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('user_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
