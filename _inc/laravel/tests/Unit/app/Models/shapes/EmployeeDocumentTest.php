<?php

namespace Tests\Unit\Models;

use App\Models\EmployeeDocument;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must include all
	 ** declared fields without extra items.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'employee_id', 'document_id', 'document_value', 'created_by',
		];

		$this->assertSame($expected, (new EmployeeDocument)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee(), document(), and creator()
	 ** must each be BelongsTo relations with correct keys.
	 **/
	public function relations_are_belongs_to(): void
	{
		$ed = new EmployeeDocument;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->employee()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->document()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->creator()
		);
	}
}
