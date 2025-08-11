<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{DealCall, User};

class DealCallTest extends TestCase
{
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
		];

		$dealCall = DealCall::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $dealCall->$field);
		}
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

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                 $relation->getForeignKeyName());
		$this->assertSame('user_id',            $relation->getLocalKeyName());
	}
}
