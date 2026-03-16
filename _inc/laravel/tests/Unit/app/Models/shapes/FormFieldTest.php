<?php

namespace Tests\Unit\Models;

use App\Models\FormField;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class FormFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must match the declared
	 ** list to avoid silent edits.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'form_id',
			'email',
			'custom_question_id',
			'name',
			'type',
			'module',
			'description',
			'default',
			'placeholder',
			'pattern',
			'readonly',
			'required',
			'multiline',
			'multiple',
			'autocapitalize',
			'autocomplete',
			'autocorrect',
			'disabled',
			'min',
			'max',
			'step',
			'minlength',
			'maxlength',
			'rows',
			'cols',
			'wrap',
			'spellcheck',
			'options',
			'optgroups',
			'accepts',
			'aria',
			'dataset',
			'selectors',
			'size',
			'tags',
		];

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
