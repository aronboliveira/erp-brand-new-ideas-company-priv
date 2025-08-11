<?php

namespace Tests\Unit\Models;

use App\Models\TaskFile;
use Tests\TestCase;

class TaskFileTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment list is as expected.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'file', 'name', 'extension', 'file_size',
			'task_id', 'user_type', 'created_by'
		];

		$this->assertSame($expected, (new TaskFile)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation should be HasOne.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new TaskFile)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',        $rel->getForeignKeyName());
		$this->assertSame('created_by', $rel->getLocalKeyName());
	}
}
