<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{DealCall, User};

use Illuminate\Support\Facades\DB;
class DealCallTest extends TestCase
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
	 ** DealCall is mass assignable for deal_id, subject, call_type, duration, user_id, description, call_result
	 **/
	public function deal_call_is_fillable()
	{
		$user = User::factory()->create();
		$data = [
			'deal_id'     => 'deal-123',
			'subject'     => 'Follow Up',
			'call_type'   => 'outbound',
			'duration'    => 300,
			'user_id'     => $user?->id,
			'description' => 'Called client for update',
			'call_result' => 'Connected',
			'from'        => '+5511999990001',
			'to'          => '+5511999990002',
			'phone'       => '+5511999990001',
		];

		$dealCall = DealCall::create($data);

		$this->assertFillableMatches($data, $dealCall);
	}

	/**
	 ** @test
	 **
	 ** DealCall uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function deal_call_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();
		$dealCall = DealCall::create([
			'deal_id'     => 'deal-456',
			'subject'     => 'Intro',
			'call_type'   => 'inbound',
			'duration'    => 150,
			'user_id'     => $user?->id,
			'description' => 'Introductory call',
			'call_result' => 'Voicemail',
			'from'        => '+5511999990003',
			'to'          => '+5511999990004',
			'phone'       => '+5511999990003',
		]);

		$key = $dealCall->getKey();

		$this->assertIsString($key);
		$this->assertFalse($dealCall->getIncrementing());
		$this->assertSame('string', $dealCall->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** getDealCallUser() relation should point to App\Models\User
	 **/
	public function get_deal_call_user_relation_resolves_to_user_model()
	{
		$relation = (new DealCall)->getDealCallUser();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('user_id',                 $relation->getForeignKeyName());
		$this->assertSame('id',            $relation->getOwnerKeyName());
	}
}
