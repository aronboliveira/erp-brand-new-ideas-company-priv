<?php

namespace Tests\Unit\Models;

use App\Models\UserToDo;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserToDoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment whitelist is correct.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'title',
			'description',
			'user_id',
			'assigned_by',
			'assigned_at',
			'notification',
			'milestone',
			'project',
			'task',
			'priority',
			'progress',
			'order',
			'estimated_hrs',
			'due_date',
			'is_complete',
			'completed_at',
			'is_favorite',
			'tags',
			'attachments',
			'updated_by',
		];

		$this->assertSame($expected, (new UserToDo)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation must be HasOne.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new UserToDo)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
	}
}
