<?php

namespace Tests\Unit\Models;

use App\Models\TaskChecklist;
use Tests\TestCase;

class TaskChecklistTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Fillable array must match constant list.
	 **/
	public function fillable_array_is_correct(): void
	{
		$ref     = new \ReflectionClass(TaskChecklist::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new TaskChecklist)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation is HasOne via created_by.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new TaskChecklist)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',            $rel->getForeignKeyName());
		$this->assertSame('created_by',    $rel->getLocalKeyName());
	}
}
