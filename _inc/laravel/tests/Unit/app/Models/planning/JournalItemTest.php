<?php

/**
 * JournalItem model tests
 */

namespace Tests\Unit\Models;

use App\Models\JournalItem;
use Tests\TestCase;

class JournalItemTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Guard the $fillable list against silent
	 ** drift by comparing it with the runtime
	 ** array created in the model constant
	 ** declaration.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'journal', 'account', 'description', 'debit', 'credit',
		];

		$this->assertSame($expected, (new JournalItem)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The accounts() relation must be HasOne
	 ** mapping ChartOfAccount::id ←
	 ** journal_items.account.
	 **/
	public function accounts_relation_is_has_one(): void
	{
		$rel = (new JournalItem)->accounts();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',       $rel->getForeignKeyName());
		$this->assertSame('account',  $rel->getLocalKeyName());
	}
}
