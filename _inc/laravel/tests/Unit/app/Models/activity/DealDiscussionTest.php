<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\{DealDiscussion, User};

use Illuminate\Support\Facades\DB;
class DealDiscussionTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** DealDiscussion is mass assignable for deal_id, comment, and created_by
	 **/
	public function deal_discussion_is_fillable()
	{
		$data = [
			'deal_id'    => 'deal-123',
			'comment'    => 'This is a test discussion.',
		];

		$discussion = DealDiscussion::create($data);

		$this->assertEquals('deal-123',                   $discussion->getAttributes()['deal_id']);
		$this->assertEquals('This is a test discussion.', $discussion->getAttributes()['comment']);
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

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,          get_class($relation->getRelated()));
		$this->assertSame('created_by',                 $relation->getForeignKeyName());
		$this->assertSame('id',         $relation->getOwnerKeyName());
	}
}
