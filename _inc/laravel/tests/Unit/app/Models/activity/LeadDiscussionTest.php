<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\{LeadDiscussion, User};

class LeadDiscussionTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** LeadDiscussion is mass assignable for lead_id, comment, and created_by
	 **/
	public function lead_discussion_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'lead_id'    => 'lead-123',
			'comment'    => 'Test discussion comment',
			'created_by' => $user?->id,
		];

		$discussion = LeadDiscussion::create($data);

		$this->assertEquals('lead-123',               $discussion->lead_id);
		$this->assertEquals('Test discussion comment', $discussion->comment);
		$this->assertEquals($user?->id,                $discussion->created_by);
	}

	/**
	 ** @test
	 **
	 ** LeadDiscussion uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function it_uses_uuid_for_primary_key()
	{
		$user = User::factory()->create();

		$discussion = LeadDiscussion::create([
			'lead_id'    => 'lead-456',
			'comment'    => 'Another comment',
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
	public function user_relation_resolves_correctly()
	{
		$relation = (new LeadDiscussion)->user();

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('created_by',        $relation->getLocalKeyName());
	}
}
