<?php

namespace Tests\Unit;

use App\Http\Controllers\JournalEntryController;
use App\Models\{
	BankAccount,
	ChartOfAccount,
	JournalEntry,
	JournalItem,
	User
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, Gate};
use ReflectionMethod;
use Tests\TestCase;

class JournalEntryControllerTest extends TestCase
{
	use RefreshDatabase;

	private JournalEntryController $controller;
	private JournalItem $item;
	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// set up a logged-in user with creatorId macro
		$this->user = User::factory()->create();
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
		Auth::login($this->user);
		$this->controller = new JournalEntryController();
		$this->item = JournalItem::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** journalNumber should return 1 when there are no existing entries.
	 **/
	public function journal_number_returns_one_if_no_entries()
	{
		$method = new ReflectionMethod(JournalEntryController::class, 'journalNumber');
		$method->setAccessible(true);

		// no JournalEntry in database
		$this->assertEquals(1, $method->invoke($this->controller));
	}

	/**
	 ** @test
	 **
	 ** journalNumber should increment from the latest journal_id.
	 **/
	public function journal_number_increments_from_latest()
	{
		$latest = JournalEntry::factory()->create([
			'journal_id' => 5,
			'created_by' => $this->user->creatorId(),
		]);

		$method = new ReflectionMethod(JournalEntryController::class, 'journalNumber');
		$method->setAccessible(true);

		$this->assertEquals(6, $method->invoke($this->controller));
	}

	/**
	 ** @test
	 **
	 ** updateBankBalances should adjust each bank account's opening_balance.
	 **/
	public function update_bank_balances_adjusts_opening_balances()
	{
		// create a chart account and a bank account tied to it
		$coa = ChartOfAccount::factory()->create(['created_by' => $this->user->creatorId()]);
		$bank = BankAccount::factory()->create([
			'chart_account_id' => $coa->id,
			'opening_balance'  => 1000,
		]);

		// create a dummy journal item: debit 200, credit 50
		$journalEntry = JournalEntry::factory()->create(['created_by' => $this->user->creatorId()]);
		$item = JournalItem::factory()->create([
			'journal' => $journalEntry->id,
			'account' => $coa->id,
			'debit'   => 200,
			'credit'  => 50,
		]);

		$method = new ReflectionMethod(JournalEntryController::class, 'updateBankBalances');
		$method->setAccessible(true);

		// invoke balance update
		$method->invoke($this->controller, $item);

		// reload and assert new balance = 1000 - debit + credit = 1000 - 200 + 50 = 850
		$this->assertDatabaseHas('bank_accounts', [
			'id'              => $bank->id,
			'opening_balance' => 850,
		]);
	}

	/**
	 ** @test
	 **
	 ** accountDestroy deletes the journal item by ID from request and redirects back with success.
	 **/
	public function account_destroy_removes_item_and_redirects()
	{
		$response = $this->post(
			action([JournalEntryController::class, 'accountDestroy']),
			['id' => $this->item->id]
		);

		$response->assertRedirect();
		$response->assertSessionHas('success', __('Journal entry account successfully deleted.'));
		$this->assertDatabaseMissing('journal_items', ['id' => $this->item->id]);
	}

	/**
	 ** @test
	 **
	 ** accountDestroy denies access without permission.
	 **/
	public function account_destroy_denies_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->post(
			action([JournalEntryController::class, 'accountDestroy']),
			['id' => $this->item->id]
		);

		// Should redirect to index
		$response->assertRedirect(action([JournalEntryController::class, 'index']));
	}

	/**
	 ** @test
	 **
	 ** journalDestroy deletes the given itemId and redirects back with success.
	 **/
	public function journal_destroy_deletes_and_redirects()
	{
		$response = $this->delete(
			action([JournalEntryController::class, 'journalDestroy'], ['itemId' => $this->item->id])
		);

		$response->assertRedirect();
		$response->assertSessionHas('success', __('Journal account successfully deleted.'));
		$this->assertDatabaseMissing('journal_items', ['id' => $this->item->id]);
	}

	/**
	 ** @test
	 **
	 ** journalDestroy returns error flash if the item does not exist.
	 **/
	public function journal_destroy_handles_missing_item()
	{
		$missingId = 999;

		$response = $this->delete(
			action([JournalEntryController::class, 'journalDestroy'], ['itemId' => $missingId])
		);

		$response->assertRedirect();
		$response->assertSessionHas('error', __('Journal account not found.'));
	}

	/**
	 ** @test
	 **
	 ** journalDestroy denies access without permission.
	 **/
	public function journal_destroy_denies_without_permission()
	{
		Gate::before(fn () => false);

		$response = $this->delete(
			action([JournalEntryController::class, 'journalDestroy'], ['itemId' => $this->item->id])
		);

		$response->assertRedirect(action([JournalEntryController::class, 'index']));
	}
}
