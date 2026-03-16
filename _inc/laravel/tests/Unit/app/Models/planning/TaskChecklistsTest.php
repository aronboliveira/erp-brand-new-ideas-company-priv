<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\TaskChecklist;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class TaskChecklistsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Fillable array must match constant list.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'name',
			'description',
			'url',
			'completed',
			'completed_at',
			'due_date',
			'is_favorite',
			'task_id',
			'user_type',
			'status',
			'order',
			'stage',
			'involved',
			'attachments',
			'tags',
			'positioning',
		];

		$this->assertSame($expected, (new TaskChecklist)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation is HasOne via created_by.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new TaskChecklist)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by',            $rel->getForeignKeyName());
		$this->assertSame('id',    $rel->getOwnerKeyName());
	}
}
