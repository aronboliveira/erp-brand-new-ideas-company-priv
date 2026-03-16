<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Bug, BugComment, User};

use Illuminate\Support\Facades\DB;
class BugCommentTest extends TestCase
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
	 ** BugComment is mass assignable for comment, bug_id, created_by, and user_type
	 **/
	public function bug_comment_is_fillable()
	{
		$user = User::factory()->create();
		$bug  = Bug::factory()->create();

		$data = [
			'comment'    => 'A comment',
			'bug_id'     => $bug->id,
			'user_type'  => 'company',
		];

		$comment = BugComment::create($data);

		$this->assertNotNull($comment->id);
		$this->assertEquals('A comment', $comment->getRawOriginal('comment'));
		$this->assertEquals($bug->id, $comment->getRawOriginal('bug_id'));
		$this->assertEquals('company', $comment->getRawOriginal('user_type'));
	}

	/**
	 ** @test
	 **
	 ** primary key uses UUID: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$comment = BugComment::factory()->create();

		$this->assertIsString($comment->getKey());
		$this->assertFalse($comment->getIncrementing());
		$this->assertSame('string', $comment->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$comment->getKey()
		);
	}

	/**
	 ** @test
	 **
	 ** commentUser() returns the User model or null
	 **/
	public function comment_user_method_returns_user_or_null()
	{
		$user   = User::factory()->create();
		$comment = BugComment::factory()->create(['created_by' => $user?->id]);

		$this->assertInstanceOf(User::class, $comment->commentUser());

		$this->assertNull(BugComment::factory()->make(['created_by' => 'non'])->commentUser());
	}

	/**
	 ** @test
	 **
	 ** user() relation should point to User via created_by
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$relation = (new BugComment)->user();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('user_id',                   $relation->getForeignKeyName());
		$this->assertSame('id',        $relation->getOwnerKeyName());
	}
}
