<?php

namespace Tests\Unit\Models;

use App\Models\ContractNote;
use Tests\TestCase;

class ContractNotesTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Validate the BelongsTo "user" relation
	 ** user_id → users.id.
	 **/
	public function user_relation_is_belongs_to(): void
	{
		$rel = (new ContractNote)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame(
			\App\Config\Constants\UsersConstants::COL_USER_ID,
			$rel->getForeignKeyName()
		);
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Ensure $fillable stays aligned with
	 ** the model constant.
	 **/
	public function fillable_fields_are_correct(): void
	{
		$ref     = new \ReflectionClass(ContractNote::class);
		$expected = $ref->getConstant('FILLABLE');

		$this->assertSame($expected, (new ContractNote)->getFillable());
	}
}
