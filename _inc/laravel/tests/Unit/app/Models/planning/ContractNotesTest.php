<?php

namespace Tests\Unit\Models;

use App\Models\ContractNotes;
use Tests\TestCase;

class ContractNotesTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Validate the HasOne “user” relation
	 ** created_by → users.id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new ContractNotes)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('created_by', $rel->getLocalKeyName());
	}

	/**
	 ** @test
	 *
	 ** Ensure $fillable stays aligned with
	 ** the model constant.
	 **/
	public function fillable_fields_are_correct(): void
	{
		$ref     = new \ReflectionClass(ContractNotes::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new ContractNotes)->getFillable());
	}
}
