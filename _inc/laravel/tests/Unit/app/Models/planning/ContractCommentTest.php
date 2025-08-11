<?php

namespace Tests\Unit\Models;

use App\Models\ContractComment;
use Tests\TestCase;

class ContractCommentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The “user” relation must be HasOne
	 ** from contract_comment.created_by →
	 ** users.id.
	 **/
	public function user_relation_is_has_one_with_correct_keys(): void
	{
		$rel = (new ContractComment)->user();

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
	 ** Protect the fillable definition
	 ** against unintended edits.
	 **/
	public function fillable_array_is_as_expected(): void
	{
		$expected = ['contract_id', 'user_id', 'comment', 'created_by'];
		$this->assertSame($expected, (new ContractComment)->getFillable());
	}
}
