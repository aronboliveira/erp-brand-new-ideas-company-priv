<?php

namespace Tests\Unit\Models;

use App\Models\CustomFieldValue;
use Tests\TestCase;

class CustomFieldValueTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must match the private
	 ** constant, guarding against silent drift.
	 **/
	public function fillable_array_is_correct(): void
	{
		$ref     = new \ReflectionClass(CustomFieldValue::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new CustomFieldValue)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** field() must be a BelongsTo relation
	 ** mapping field_id → custom_fields.id.
	 **/
	public function field_relation_is_belongs_to(): void
	{
		$rel = (new CustomFieldValue)->field();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('field_id', $rel->getForeignKeyName());
		$this->assertSame('id',       $rel->getOwnerKeyName());
	}
}
