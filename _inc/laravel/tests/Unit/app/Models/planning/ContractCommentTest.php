<?php

namespace Tests\Unit\Models;

use App\Models\ContractComment;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractCommentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
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
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('user_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Protect the fillable definition
	 ** against unintended edits.
	 **/
	public function fillable_array_is_as_expected(): void
	{
		$expected = [
			'time',
			'comment',
			'reference',
			'user_id',
			'user_type',
			'is_edited',
			'is_deleted',
			'deleter',
			'deleted_at',
			'edit_count',
			'flagged',
			'thread',
			'is_reply',
			'reply_count',
			'order',
			'depth',
			'parent',
			'attachments',
			'tags',
			'reactions',
			'replies',
			'edits',
			'metadata',
			'contract_id',
		];
		$this->assertSame($expected, (new ContractComment)->getFillable());
	}
}
