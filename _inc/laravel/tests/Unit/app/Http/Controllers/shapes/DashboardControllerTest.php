<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class DashboardControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** accountDashboardIndex should redirect guests to login
	 **/
	public function account_dashboard_redirects_guests_to_login()
	{
		$response = $this->get(route('dashboard.account'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** accountDashboardIndex should redirect clients to client dashboard
	 **/
	public function account_dashboard_redirects_client_users_to_client_dashboard()
	{
		$user = User::factory()->create(['type' => 'client']);
		$response = $this->actingAs($user)->get(route('dashboard.account'));
		$response->assertRedirect(route('client.dashboard.view'));
	}

	/**
	 ** @test
	 **
	 ** accountDashboardIndex should deny access without permission
	 **/
	public function account_dashboard_denies_access_without_permission()
	{
		$user = User::factory()->create(['type' => 'company']);
		$response = $this->actingAs($user)->get(route('dashboard.account'));
		$response->assertRedirect('/');
	}

	/**
	 ** @test
	 **
	 ** accountDashboardIndex should display the dashboard view for authorized users
	 **/
	public function account_dashboard_displays_view_for_authorized_user()
	{
		// Give user the required permission
		Permission::create(['name' => 'show account dashboard']);
		$user = User::factory()->create(['type' => 'company']);
		$user?->givePermissionTo('show account dashboard');

		// Stub minimal data for models the controller fetches
		DB::table('revenues')->insert([
			['created_by' => $user?->creatorId(), 'amount' => 100, 'created_at' => now(), 'updated_at' => now()]
		]);
		DB::table('payments')->insert([
			['created_by' => $user?->creatorId(), 'amount' => 50, 'created_at' => now(), 'updated_at' => now()]
		]);
		DB::table('product_service_categories')->insert([
			['created_by' => $user?->creatorId(), 'type' => 'income', 'color' => 'FF0000', 'name' => 'Cat1'],
			['created_by' => $user?->creatorId(), 'type' => 'expense', 'color' => '00FF00', 'name' => 'Cat2'],
		]);
		DB::table('bank_accounts')->insert([
			['created_by' => $user?->creatorId(), 'name' => 'Acc1']
		]);
		DB::table('invoices')->insert([
			['created_by' => $user?->creatorId(), 'client_id' => null, 'created_at' => now(), 'updated_at' => now()]
		]);
		DB::table('bills')->insert([
			['created_by' => $user?->creatorId(), 'created_at' => now(), 'updated_at' => now()]
		]);
		DB::table('goals')->insert([
			['created_by' => $user?->creatorId(), 'is_display' => 1]
		]);
		// Ensure there's at least one plan for Plan::find
		DB::table('plans')->insert(['id' => 1, 'storage_limit' => 100, 'price' => 0, 'created_at' => now(), 'updated_at' => now()]);

		$response = $this->actingAs($user)->get(route('dashboard.account'));

		$response->assertStatus(200);
		$response->assertViewIs('dashboard.account-dashboard');
		$response->assertViewHasAll([
			'latestIncome',
			'latestExpense',
			'incomeCategoryColor',
			'incomeCategory',
			'incomeCatAmount',
			'expenseCategoryColor',
			'expenseCategory',
			'expenseCatAmount',
			'incExpBarChartData',
			'incExpLineChartData',
			'currentYear',
			'currentMonth',
			'constant',
			'bankAccountDetail',
			'recentInvoice',
			'weeklyInvoice',
			'monthlyInvoice',
			'recentBill',
			'weeklyBill',
			'monthlyBill',
			'goals',
			'users',
			'plan',
			'storage_limit',
		]);
	}
}
