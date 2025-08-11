<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TransactionControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Create and assign the required permission
		Permission::create(['name' => 'manage transaction']);
		$this->user = User::factory()->create();
		$this->user->givePermissionTo('manage transaction');
	}

	/**
	 ** @test
	 *
	 ** Guests cannot access the transaction index and are redirected to the login page.
	 **/
	public function guests_cannot_access_index()
	{
		$response = $this->get(route('transaction.index'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 *
	 ** Authenticated users with proper permission can view the transaction index page.
	 **/
	public function authenticated_users_with_permission_can_view_index()
	{
		// Seed some data for the view
		$account = BankAccount::factory()->create(['created_by' => $this->user->creatorId()]);
		ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 1,
			'name'       => 'Invoice'
		]);
		Transaction::factory()->count(3)->create([
			'created_by' => $this->user->creatorId(),
			'account'    => $account->id,
			'category'   => 'Invoice',
		]);

		$response = $this->actingAs($this->user)
			->get(route('transaction.index'));

		$response->assertStatus(200);
		$response->assertViewIs('transaction.index');
		$response->assertViewHasAll([
			'transactions',
			'account',
			'category',
			'filter',
			'accounts',
		]);
	}

	/**
	 ** @test
	 *
	 ** Authenticated users with proper permission can export transactions as an Excel file.
	 **/
	public function authenticated_users_with_permission_can_export_transactions()
	{
		// Freeze time so the filename is predictable
		Carbon::setTestNow($now = Carbon::now());

		Excel::fake();

		$response = $this->actingAs($this->user)
			->get(route('transaction.export'));

		$expectedFile = 'transaction_' . $now->format('Y-m-d_H-i-s') . '.xlsx';
		$response->assertDownload($expectedFile);
	}
}
