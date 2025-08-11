<?php

namespace Tests\Feature;

use App\Models\{Order, Plan, PlanRequest, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Gate, Crypt};
use Tests\TestCase;

class PlanRequestControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $admin;
	private User $user;
	private Plan $plan;

	protected function setUp(): void
	{
		parent::setUp();

		// allow permissions per test
		Gate::before(fn () => true);

		// have creatorId() return own id
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

		$this->admin = User::factory()->create(['type' => 'company']);
		$this->user = User::factory()->create(['requested_plan' => 0]);
		$this->plan = Plan::factory()->create(['price' => 100]);

		$this->actingAs($this->admin);
	}

	/**
	 ** @test
	 **
	 ** index_denies_without_permission:
	 **   - users lacking 'view plan requests' get redirected (403)
	 **/
	public function index_denies_without_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->admin)
			->get(route('plan-request.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** index_displays_all_plan_requests:
	 **   - shows view with list of planRequests
	 **/
	public function index_displays_all_plan_requests()
	{
		PlanRequest::factory()->count(2)->create();

		$resp = $this->actingAs($this->admin)
			->get(route('plan-request.index'));

		$resp->assertOk()
			->assertViewIs('plan_request.index')
			->assertViewHas('planRequests', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** requestView_denies_without_permission:
	 **   - users lacking 'view plan details' get 403
	 **/
	public function requestView_denies_without_permission()
	{
		Gate::before(fn () => false);

		$enc = Crypt::encrypt($this->plan->id);
		$this->get(route('plan-request.requestView', $enc))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** requestView_shows_plan_on_valid_id:
	 **   - decrypts id and displays the plan details
	 **/
	public function requestView_shows_plan_on_valid_id()
	{
		$enc = Crypt::encryptString($this->plan->id);

		$resp = $this->actingAs($this->admin)
			->get(route('plan-request.requestView', $enc));

		$resp->assertOk()
			->assertViewIs('plan_request.show')
			->assertViewHas('plan', fn ($p) => $p->id === $this->plan->id);
	}

	/**
	 ** @test
	 **
	 ** userRequest_denies_without_permission:
	 **   - users lacking 'request plan' get 403
	 **/
	public function userRequest_denies_without_permission()
	{
		Gate::before(fn () => false);

		$enc = Crypt::encryptString($this->plan->id);
		$this->actingAs($this->user)
			->post(route('plan-request.userRequest', $enc))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** userRequest_prevents_if_already_requested:
	 **   - user with requested_plan != 0 sees error
	 **/
	public function userRequest_prevents_if_already_requested()
	{
		$this->user->update(['requested_plan' => $this->plan->id]);

		$enc = Crypt::encryptString($this->plan->id);
		$this->actingAs($this->user)
			->post(route('plan-request.userRequest', $enc))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** userRequest_creates_plan_request_and_updates_user:
	 **   - stores PlanRequest and sets user's requested_plan
	 **/
	public function userRequest_creates_plan_request_and_updates_user()
	{
		$enc = Crypt::encryptString($this->plan->id);
		$this->actingAs($this->user)
			->post(route('plan-request.userRequest', $enc))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('plan_requests', [
			'user_id' => $this->user->id,
			'plan_id' => $this->plan->id,
		]);

		$this->assertEquals($this->plan->id, $this->user->fresh()->requested_plan);
	}

	/**
	 ** @test
	 **
	 ** acceptRequest_denies_without_permission:
	 **   - users lacking 'accept plan request' get 403
	 **/
	public function acceptRequest_denies_without_permission()
	{
		Gate::before(fn () => false);

		$pr = PlanRequest::factory()->create(['plan_id' => $this->plan->id, 'user_id' => $this->user->id]);
		$this->actingAs($this->admin)
			->get(route('plan-request.acceptRequest', [$pr->id, 1]))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** acceptRequest_rejects_and_clears_request:
	 **   - response=0 clears user's requested_plan and deletes the request
	 **/
	public function acceptRequest_rejects_and_clears_request()
	{
		$pr = PlanRequest::factory()->create(['plan_id' => $this->plan->id, 'user_id' => $this->user->id]);
		$this->user->update(['requested_plan' => $this->plan->id]);

		$this->actingAs($this->admin)
			->get(route('plan-request.acceptRequest', [$pr->id, 0]))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('plan_requests', ['id' => $pr->id]);
		$this->assertEquals(0, $this->user->fresh()->requested_plan);
	}

	/**
	 ** @test
	 **
	 ** cancelRequest_resets_user_and_deletes_request:
	 **   - clears requested_plan and removes PlanRequest records
	 **/
	public function cancelRequest_resets_user_and_deletes_request()
	{
		PlanRequest::factory()->count(2)->create(['user_id' => $this->user->id]);
		$this->user->update(['requested_plan' => $this->plan->id]);

		$this->actingAs($this->admin)
			->get(route('plan-request.cancelRequest', [$this->user->id]))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('plan_requests', ['user_id' => $this->user->id]);
		$this->assertEquals(0, $this->user->fresh()->requested_plan);
	}
}
