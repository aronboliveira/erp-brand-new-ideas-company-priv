<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\HasOne,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{Bug, BugComment, User};

class BugCommentTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** BugComment is mass assignable for comment, bug_id, created_by, and user_type
	 **/
	public function bug_comment_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'comment'    => 'A comment',
			'bug_id'     => Bug::factory()->create()->id,
			'created_by' => $user?->id,
			'user_type'  => 'employee',
		];

		$comment = BugComment::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $comment->$field);
		}
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

		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(User::class,         get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('created_by',        $relation->getLocalKeyName());
	}
}
