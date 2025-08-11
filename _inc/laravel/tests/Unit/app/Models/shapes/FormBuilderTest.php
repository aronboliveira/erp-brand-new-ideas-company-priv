<?php

namespace Tests\Unit\Models;

use App\Models\FormBuilder;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must match the private
	 ** constant fields.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(FormBuilder::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new FormBuilder)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The static $fieldTypes array must be
	 ** unchanged to ensure consistency.
	 **/
	public function static_field_types_array_is_intact(): void
	{
		$expected = [
			'text'     => 'Text',
			'email'    => 'Email',
			'number'   => 'Number',
			'date'     => 'Date',
			'textarea' => 'Textarea',
		];

		$this->assertSame($expected, FormBuilder::$fieldTypes);
	}

	/**
	 ** @test
	 *
	 ** formField(), fieldResponse(), and response()
	 ** must each be Eloquent relations of the correct type.
	 **/
	public function relations_are_correct_type(): void
	{
		$fb = new FormBuilder;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$fb->formField()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$fb->fieldResponse()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$fb->response()
		);
	}
}
