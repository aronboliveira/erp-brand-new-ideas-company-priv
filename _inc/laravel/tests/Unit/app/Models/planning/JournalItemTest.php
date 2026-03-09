<?php

/**
 * JournalItem model tests
 */

namespace Tests\Unit\Models;

use App\Models\JournalItem;
use Tests\TestCase;

class JournalItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
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
			'code',
			'journal',
			'account',
			'line',
			'posting_type',
			'debit',
			'credit',
			'currency',
			'exchange_rate',
			'bank_account',
			'bank_extract_date',
			'transaction',
			'transaction_type',
			'transfer',
			'payment',
			'transfer_type',
			'pix_key',
			'check_number',
			'ted_doc_number',
			'company',
			'branch',
			'department',
			'project',
			'entity',
			'description',
			'memo',
			'notes',
			'is_reconciled',
			'reconciled_date',
			'reconciliation_document',
			'nfe_key',
			'nfe_number',
			'nfe_series',
			'nfe_xml_path',
			'nfe_protocol',
			'nfe_authorized_at',
			'attachments',
			'taxes',
			'categories',
			'metadata',
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
