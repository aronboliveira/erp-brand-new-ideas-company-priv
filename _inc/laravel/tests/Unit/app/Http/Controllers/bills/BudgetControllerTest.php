<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\BudgetController;
use App\Models\Budget;
use App\Models\ProductServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Request};
use Tests\TestCase;

final class BudgetControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
		$this->actingAs($this->user);
	}

	/**
	 ** @test
	 **
	 ** The index route should return the budget.index view
	 ** with the list of budgets belonging to the authenticated user.
	 **/
	public function test_index_returns_view_with_budgets(): void
	{
		Budget::factory()->count(3)->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->get(route('budget.index'));

		$response->assertStatus(200);
		$response->assertViewIs('budget.index');
		$response->assertViewHas('budgets');
	}

	/**
	 ** @test
	 **
	 ** The create route should render the form with separate
	 ** income and expense categories and the list of years.
	 **/
	public function test_create_renders_form(): void
	{
		ProductServiceCategory::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'income',
		]);
		ProductServiceCategory::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'expense',
		]);

		$response = $this->get(route('budget.create'));

		$response->assertStatus(200);
		$response->assertViewIs('budget.create');
		$response->assertViewHas(['incomeproduct', 'expenseproduct', 'yearList']);
	}

	/**
	 ** @test
	 **
	 ** Posting valid budget data to store should save a new budget
	 ** and redirect back to the index.
	 **/
	public function test_store_creates_budget(): void
	{
		$payload = [
			'name'    => 'Budget 2025',
			'year'    => 2025,
			'period'  => 'monthly',
			'income'  => ['1' => ['Jan' => 1000]],
			'expense' => ['2' => ['Jan' => 800]],
		];

		$response = $this->post(route('budget.store'), $payload);

		$response->assertRedirect(route('budget.index'));
		$this->assertDatabaseHas('budgets', ['name' => 'Budget 2025']);
	}

	/**
	 ** @test
	 **
	 ** The show route, given an encrypted budget ID,
	 ** should decrypt and display the budget.show view.
	 **/
	public function test_show_displays_budget(): void
	{
		$budget = Budget::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		$enc = Crypt::encryptString($budget->id);

		$response = $this->get(route('budget.show', $enc));

		$response->assertStatus(200);
		$response->assertViewIs('budget.show');
		$response->assertViewHas('budget');
	}

	/**
	 ** @test
	 **
	 ** The edit route for an encrypted ID should decrypt
	 ** and render the budget.edit form with budget data.
	 **/
	public function test_edit_displays_edit_form(): void
	{
		$budget = Budget::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		$enc = Crypt::encryptString($budget->id);

		$response = $this->get(route('budget.edit', $enc));

		$response->assertStatus(200);
		$response->assertViewIs('budget.edit');
		$response->assertViewHas('budget');
	}

	/**
	 ** @test
	 **
	 ** Sending updated budget fields to update should persist
	 ** changes and redirect back to the index.
	 **/
	public function test_update_applies_changes(): void
	{
		$budget = Budget::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$payload = [
			'name'    => 'Updated Budget',
			'year'    => 2024,
			'period'  => 'quarterly',
			'income'  => ['1' => ['Q1' => 5000]],
			'expense' => ['1' => ['Q1' => 2500]],
		];

		$response = $this->put(route('budget.update', $budget), $payload);

		$response->assertRedirect(route('budget.index'));
		$this->assertDatabaseHas('budgets', ['name' => 'Updated Budget']);
	}

	/**
	 ** @test
	 **
	 ** Deleting an existing budget should remove it
	 ** and redirect back to budget.index.
	 **/
	public function test_destroy_deletes_budget(): void
	{
		$budget = Budget::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->delete(route('budget.destroy', $budget));

		$response->assertRedirect(route('budget.index'));
		$this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
	}

	/**
	 ** @test
	 **
	 ** yearMonth() returns an empty array when the user is not authenticated.
	 **/
	public function year_month_returns_empty_for_guest()
	{
		auth()->logout();
		$controller = new BudgetController();
		$request   = Request::create('/year-month', 'GET');

		$result = $controller->yearMonth($request);

		$this->assertSame([], $result);
	}

	/**
	 ** @test
	 **
	 ** yearMonth() returns the full list of months when the user is authenticated.
	 **/
	public function year_month_returns_all_months_for_authenticated_user()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$controller = new BudgetController();
		$request   = Request::create('/year-month', 'GET');

		$expected = [
			'January', 'February', 'March', 'April',
			'May', 'June', 'July', 'August',
			'September', 'October', 'November', 'December'
		];

		$this->assertSame($expected, $controller->yearMonth($request));
	}

	/**
	 ** @test
	 **
	 ** yearList() returns an empty array when the user is not authenticated.
	 **/
	public function year_list_returns_empty_for_guest()
	{
		auth()->logout();
		$controller = new BudgetController();
		$request   = Request::create('/year-list', 'GET');

		$result = $controller->yearList($request);

		$this->assertSame([], $result);
	}

	/**
	 ** @test
	 **
	 ** yearList() returns the last six years (current year down to five years ago) for an authenticated user.
	 **/
	public function year_list_returns_recent_years_for_authenticated_user()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// freeze time to ensure consistent test
		Carbon::setTestNow(Carbon::create(2025, 5, 17));

		$controller = new BudgetController();
		$request   = Request::create('/year-list', 'GET');

		$end  = Carbon::now()->year;
		$start = $end - 5;
		$expected = array_combine(
			range($end, $start),
			range($end, $start)
		);

		$this->assertSame($expected, $controller->yearList($request));

		Carbon::setTestNow(); // clear test now
	}
}
