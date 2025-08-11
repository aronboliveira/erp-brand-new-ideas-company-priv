<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{DealDiscussion, User};

class DealDiscussionTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** DealDiscussion is mass assignable for deal_id, comment, and created_by
	 **/
	public function deal_discussion_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'deal_id'    => 'deal-123',
			'comment'    => 'This is a test discussion.',
			'created_by' => $user?->id,
		];

		$discussion = DealDiscussion::create($data);

		$this->assertEquals('deal-123',               $discussion->deal_id);
		$this->assertEquals('This is a test discussion.', $discussion->comment);
		$this->assertEquals($user?->id,                $discussion->created_by);
	}

	/**
	 ** @test
	 **
	 ** DealDiscussion uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function deal_discussion_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$discussion = DealDiscussion::create([
			'deal_id'    => 'deal-456',
			'comment'    => 'Another comment.',
			'created_by' => $user?->id,
		]);

		$key = $discussion->getKey();

		$this->assertIsString($key);
		$this->assertFalse($discussion->getIncrementing());
		$this->assertSame('string', $discussion->getKeyType());
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
		$relation = (new DealDiscussion)->user();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                 $relation->getForeignKeyName());
		$this->assertSame('created_by',         $relation->getLocalKeyName());
	}
}
