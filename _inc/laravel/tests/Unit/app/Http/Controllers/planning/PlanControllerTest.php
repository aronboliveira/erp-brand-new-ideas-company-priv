<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PlanControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $admin;

	protected function setUp(): void
	{
		parent::setUp();

		// Macro so creatorId() returns the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		// By default grant all permissions
		Gate::before(fn () => true);

		$this->admin = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** index requires "manage plan" permission and returns 403 if denied.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->admin)
			->get(route('plan.index'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** index shows the plan list view with all plans.
	 **/
	public function index_displays_plans()
	{
		Plan::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)
			->get(route('plan.index'));

		$response->assertOk()
			->assertViewIs('plan.index')
			->assertViewHas('plans', fn ($plans) => $plans->count() === 3)
			->assertViewHas('adminPaymentSetting');
	}

	/**
	 ** @test
	 **
	 ** create requires "create plan" permission and returns 403 if denied.
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->admin)
			->get(route('plan.create'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** create displays the plan creation form with durations.
	 **/
	public function create_displays_form()
	{
		$response = $this->actingAs($this->admin)
			->get(route('plan.create'));

		$response->assertOk()
			->assertViewIs('plan.create')
			->assertViewHas('arrDuration', fn ($d) => is_array($d));
	}

	/**
	 ** @test
	 **
	 ** store fails with error when no payment methods are enabled.
	 **/
	public function store_fails_without_payment_settings()
	{
		// ensure no settings present
		DB::table('settings')->truncate();

		$response = $this->actingAs($this->admin)
			->post(route('plan.store'), [
				'duration' => 'monthly',
				'max_customers' => 10,
				'max_users' => 5,
				'max_vendors' => 2,
				'name' => 'Test Plan',
				'price' => 100,
				'storage_limit' => 1000,
			]);

		$response->assertRedirect()
			->assertSessionHas(
				'error',
				__('Please set stripe or paypal api key & secret key for add new plan.')
			);
	}

	/**
	 ** @test
	 **
	 ** store succeeds when at least one payment setting is "on".
	 **/
	public function store_creates_plan_when_payment_enabled()
	{
		// seed a payment setting key
		DB::table('settings')->insert([
			['name' => 'is_stripe_enabled', 'value' => 'on', 'created_by' => $this->admin->creatorId()],
		]);

		$response = $this->actingAs($this->admin)
			->post(route('plan.store'), [
				'duration' => 'monthly',
				'max_customers' => 10,
				'max_users' => 5,
				'max_vendors' => 2,
				'name' => 'Basic Plan',
				'price' => 0,
				'storage_limit' => 500,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Plan successfully created.'));

		$this->assertDatabaseHas('plans', [
			'name' => 'Basic Plan',
			'duration' => 'monthly',
			'max_customers' => 10,
			'max_users' => 5,
			'max_vendors' => 2,
			'storage_limit' => 500,
		]);
	}

	/**
	 ** @test
	 **
	 ** edit requires "edit plan" permission and returns 403 if denied.
	 **/
	public function edit_requires_permission()
	{
		$plan = Plan::factory()->create();

		Gate::before(fn () => false);

		$this->actingAs($this->admin)
			->get(route('plan.edit', $plan->id))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** edit displays the plan editing form with plan data.
	 **/
	public function edit_displays_form()
	{
		$plan = Plan::factory()->create();

		$response = $this->actingAs($this->admin)
			->get(route('plan.edit', $plan->id));

		$response->assertOk()
			->assertViewIs('plan.edit')
			->assertViewHasAll([
				'plan'        => fn ($p) => $p->id === $plan->id,
				'arrDuration' => fn ($d) => is_array($d),
			]);
	}

	/**
	 ** @test
	 **
	 ** update fails with error when no payment methods are enabled.
	 **/
	public function update_fails_without_payment_settings()
	{
		$plan = Plan::factory()->create();

		DB::table('settings')->truncate();

		$response = $this->actingAs($this->admin)
			->put(route('plan.update', $plan->id), [
				'duration' => 'yearly',
				'max_customers' => 20,
				'max_users' => 10,
				'max_vendors' => 4,
				'name' => 'Updated Plan',
				'storage_limit' => 2000,
			]);

		$response->assertRedirect()
			->assertSessionHas('error', __('Please set stripe api key & secret key for add new plan.'));
	}

	/**
	 ** @test
	 **
	 ** update succeeds when payment is enabled and updates the plan.
	 **/
	public function update_modifies_plan_when_payment_enabled()
	{
		$plan = Plan::factory()->create([
			'name' => 'Original',
		]);

		// enable a payment method
		DB::table('settings')->insert([
			['name' => 'is_paypal_enabled', 'value' => 'on', 'created_by' => $this->admin->creatorId()],
		]);

		$response = $this->actingAs($this->admin)
			->put(route('plan.update', $plan->id), [
				'duration' => 'yearly',
				'max_customers' => 20,
				'max_users' => 10,
				'max_vendors' => 4,
				'name' => 'Pro Plan',
				'storage_limit' => 2000,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Plan successfully updated.'));

		$this->assertDatabaseHas('plans', [
			'id'   => $plan->id,
			'name' => 'Pro Plan',
			'duration' => 'yearly',
			'storage_limit' => 2000,
		]);
	}

	/**
	 ** @test
	 **
	 ** userPlan activates free plan and redirects with success.
	 **/
	public function user_plan_activates_free_plan()
	{
		$freePlan = Plan::factory()->create(['price' => 0]);
		$code = Crypt::encrypt($freePlan->id);

		$response = $this->actingAs($this->admin)
			->post(route('plan.userPlan'), ['code' => $code]);

		$response->assertRedirect(route('plans.index'))
			->assertSessionHas('success', __('Plan successfully activated.'));
	}

	/**
	 ** @test
	 **
	 ** userPlan fails on paid plan and redirects back with error.
	 **/
	public function user_plan_fails_on_paid_plan()
	{
		$paidPlan = Plan::factory()->create(['price' => 100]);
		$code = Crypt::encrypt($paidPlan->id);

		$response = $this->actingAs($this->admin)
			->post(route('plan.userPlan'), ['code' => $code]);

		$response->assertRedirect()
			->assertSessionHas('error', __('Something is wrong.'));
	}
}
