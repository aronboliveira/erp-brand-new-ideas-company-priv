<?php

namespace Tests\Unit\Models;

use Mockery;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class JournalEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** The $fillable array must match the declared list
	 ** to guard against silent changes.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'code',
			'name',
			'reference',
			'date',
			'posting_date',
			'reversal_date',
			'period',
			'author',
			'reviewer',
			'accepted_at',
			'rejected_at',
			'rejection_reason',
			'status',
			'payment_type',
			'total_debit',
			'total_credit',
			'currency',
			'exchange_rate',
			'description',
			'memo',
			'notes',
			'company',
			'branch',
			'department',
			'project',
			'document',
			'invoice_id',
			'bill_id',
			'order_id',
			'transaction_id',
			'payment_id',
			'payslip_id',
			'expense_id',
			'pos_id',
			'pos_payment_id',
			'credit_note_id',
			'debit_note_id',
			'loan_id',
			'allowance_id',
			'revenue',
			'contract',
			'deal',
			'journal_id',
			'is_reversal',
			'reversing_id',
			'reversed_id',
			'book_type',
			'nire',
			'hash_ecd',
			'ecd_transmitted',
			'ecd_transmitted_at',
			'nfe_key',
			'nfe_number',
			'nfe_series',
			'nfe_xml_path',
			'nfe_protocol',
			'nfe_authorized_at',
			'origin_user_id',
			'ip_address',
			'user_agent',
			'attachments',
			'tags',
			'metadata',
			'taxes',
			'items',
			'transactions',
		];

		$this->assertSame($expected, (new JournalEntry)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** accounts() must be a HasMany relation.
	 **/
	public function accounts_relation_is_has_many(): void
	{
		$rel = (new JournalEntry)->accounts();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$rel
		);
	}

	/**
	 ** @test
	 **
	 ** totalCredit() should sum the 'credit' field
	 ** on the loaded accounts collection.
	 **/
	public function total_credit_sums_accounts_correctly(): void
	{
		$je = new JournalEntry;
		$je->setRelation('accounts', collect([
			(object)['credit' => 100.5, 'debit' => 0],
			(object)['credit' =>  50.0, 'debit' => 0],
		]));

		$this->assertSame(150.5, $je->totalCredit());
	}

	/**
	 ** @test
	 **
	 ** totalDebit() should sum the 'debit' field
	 ** on the loaded accounts collection.
	 **/
	public function total_debit_sums_accounts_correctly(): void
	{
		$je = new JournalEntry;
		$je->setRelation('accounts', collect([
			(object)['credit' => 0, 'debit' => 75.25],
			(object)['credit' => 0, 'debit' => 24.75],
		]));

		$this->assertSame(100.0, $je->totalDebit());
	}

	/**
	 ** @test
	 **
	 ** If summing throws an exception, the method
	 ** should catch it, log an error, and return 0.0.
	 **/
	public function total_credit_handles_exceptions_and_returns_zero(): void
	{
		// Create a stub where ->accounts is not iterable
		$je = new JournalEntry;
		$je->setRelation('accounts', null);

		// Expect Log::error called once
		Log::shouldReceive('error')->once();

		$this->assertSame(0.0, $je->totalCredit());
	}

	/**
	 ** @test
	 **
	 ** If summing throws an exception, the method
	 ** should catch it, log an error, and return 0.0.
	 **/
	public function total_debit_handles_exceptions_and_returns_zero(): void
	{
		$je = new JournalEntry;
		$je->setRelation('accounts', null);

		Log::shouldReceive('error')->once();

		$this->assertSame(0.0, $je->totalDebit());
	}
}
