<?php

namespace Tests\Unit\Models;

use App\Models\FormField;
use Tests\TestCase;

class FormFieldTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must match the declared
	 ** list to avoid silent edits.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['form_id', 'name', 'type', 'created_by'];

		$this->assertSame($expected, (new FormField)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** form() and createdBy() must be BelongsTo relations.
	 **/
	public function relations_are_belongs_to(): void
	{
		$ff = new FormField;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ff->form()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ff->createdBy()
		);
	}
}
