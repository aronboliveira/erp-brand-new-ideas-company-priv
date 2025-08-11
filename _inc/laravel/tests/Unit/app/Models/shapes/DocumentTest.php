<?php

namespace Tests\Unit\Models;

use App\Models\Document;
use Tests\TestCase;

class DocumentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The $fillable array must remain consistent
	 ** with the private constant to avoid silent edits.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(Document::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new Document)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() must be a HasOne relation mapping
	 ** users.id ← documents.created_by.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new Document)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',         $rel->getForeignKeyName());
		$this->assertSame('created_by', $rel->getLocalKeyName());
	}
}
