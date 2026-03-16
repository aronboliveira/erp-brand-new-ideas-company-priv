<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\TaskComment;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class TaskCommentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The fillable list is immutable.
	 **/
	public function fillable_array_matches_definition(): void
	{
		$expected = [
			'time',
			'comment',
			'reference',
			'user_id',
			'user_type',
			'is_edited',
			'is_deleted',
			'deleter',
			'deleted_at',
			'edit_count',
			'flagged',
			'thread',
			'is_reply',
			'reply_count',
			'order',
			'depth',
			'parent',
			'attachments',
			'tags',
			'reactions',
			'replies',
			'edits',
			'metadata',
			'task_id',
		];

		$this->assertSame($expected, (new TaskComment)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() must be HasOne created_by → users.id
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new TaskComment)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('user_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
