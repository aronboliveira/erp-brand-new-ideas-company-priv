<?php

namespace Tests\Unit\Models;

use App\Models\TaskComment;
use Tests\TestCase;

class TaskCommentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The fillable list is immutable.
	 **/
	public function fillable_array_matches_definition(): void
	{
		$expected = [
			'comment', 'task_id', 'user_id', 'user_type', 'created_by'
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
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('created_by', $rel->getLocalKeyName());
	}
}
