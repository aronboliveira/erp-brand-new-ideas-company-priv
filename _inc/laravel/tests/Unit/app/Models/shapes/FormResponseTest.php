<?php

namespace Tests\Unit\Models;

use App\Models\FormResponse;
use Tests\TestCase;

class FormResponseTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must match the declared fields.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(FormResponse::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new FormResponse)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** form() relation must be a BelongsTo mapping
	 ** forms.id ← form_responses.form_id.
	 **/
	public function form_relation_is_belongs_to(): void
	{
		$rel = (new FormResponse)->form();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('form_id', $rel->getForeignKeyName());
		$this->assertSame('id',      $rel->getOwnerKeyName());
	}
}
