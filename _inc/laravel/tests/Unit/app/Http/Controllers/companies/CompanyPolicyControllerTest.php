<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CompanyPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CompanyPolicyControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass all permission checks
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 */
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
	}

	/**
	 ** @test
	 **
	 ** The index action should display only the company policies
	 ** created by the authenticated user.
	 **/
	public function index_displays_only_user_policies()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();

		// Seed policies
		$policy1 = CompanyPolicy::factory()->create([
			'created_by' => $user?->creatorId(),
		]);
		CompanyPolicy::factory()->count(2)->create([
			'created_by' => $other->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('company-policy.index'));

		$response->assertStatus(200)
			->assertViewIs('companyPolicy.index')
			->assertViewHas(
				'companyPolicy',
				fn ($list) =>
				$list->pluck('id')->all() === [$policy1->id]
			);
	}

	/**
	 ** @test
	 **
	 ** The create action should render the form and
	 ** provide a list of branches for the authenticated user.
	 **/
	public function create_shows_branch_list()
	{
		$user = User::factory()->create();
		$b1  = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('company-policy.create'));

		$response->assertStatus(200)
			->assertViewIs('companyPolicy.create')
			->assertViewHas(
				'branch',
				fn ($branches) =>
				array_key_exists($b1->id, $branches)
			);
	}

	/**
	 ** @test
	 **
	 ** The store action should validate input and
	 ** create a new company policy record for the user.
	 **/
	public function store_validates_and_creates_policy()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$payload = [
			'branch'      => $branch->id,
			'title'       => 'New Policy',
			'description' => 'Some details',
		];

		$response = $this->actingAs($user)
			->post(route('company-policy.store'), $payload);

		$response->assertRedirect(route('company-policy.index'));
		$this->assertDatabaseHas('company_policies', [
			'branch'     => $branch->id,
			'title'      => 'New Policy',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** The store action should redirect back with an error
	 ** when validation fails (missing required fields).
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();

		// Missing both branch and title
		$response = $this->actingAs($user)
			->post(route('company-policy.store'), []);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The show action should always redirect to the index,
	 ** regardless of input.
	 **/
	public function show_always_redirects_to_index()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('company-policy.show', ['company_policy' => 123]));

		$response->assertRedirect(route('company-policy.index'));
	}

	/**
	 ** @test
	 **
	 ** The edit action should display the existing policy
	 ** and branch list for editing.
	 **/
	public function edit_displays_existing_policy_and_branches()
	{
		$user  = User::factory()->create();
		$branch = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$policy = CompanyPolicy::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch'     => $branch->id,
		]);

		$response = $this->actingAs($user)
			->get(route('company-policy.edit', $policy));

		$response->assertStatus(200)
			->assertViewIs('companyPolicy.edit')
			->assertViewHasAll(['companyPolicy', 'branch']);
	}

	/**
	 ** @test
	 **
	 ** The update action should validate input and
	 ** update the policy record accordingly.
	 **/
	public function update_validates_and_updates_policy()
	{
		$user   = User::factory()->create();
		$branch1 = Branch::factory()->create(['created_by' => $user?->creatorId()]);
		$branch2 = Branch::factory()->create(['created_by' => $user?->creatorId()]);

		$policy = CompanyPolicy::factory()->create([
			'created_by' => $user?->creatorId(),
			'branch'     => $branch1->id,
			'title'      => 'Old',
		]);

		$payload = [
			'branch'      => $branch2->id,
			'title'       => 'Updated Title',
			'description' => 'New Desc',
		];

		$response = $this->actingAs($user)
			->put(route('company-policy.update', $policy), $payload);

		$response->assertRedirect(route('company-policy.index'));
		$this->assertDatabaseHas('company_policies', [
			'id'     => $policy->id,
			'branch' => $branch2->id,
			'title'  => 'Updated Title',
		]);
	}

	/**
	 ** @test
	 **
	 ** The update action should redirect back with an error
	 ** when validation fails (missing branch or title).
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		$user  = User::factory()->create();
		$policy = CompanyPolicy::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->put(route('company-policy.update', $policy), [
				// missing branch & title
			]);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The destroy action should delete the policy (and its attachment if any)
	 ** and redirect back to the index.
	 **/
	public function destroy_deletes_the_policy_and_attachment_if_present()
	{
		$user  = User::factory()->create();
		$policy = CompanyPolicy::factory()->create([
			'created_by' => $user?->creatorId(),
			'attachment' => null,
		]);

		$response = $this->actingAs($user)
			->delete(route('company-policy.destroy', $policy));

		$response->assertRedirect(route('company-policy.index'));
		$this->assertDatabaseMissing('company_policies', ['id' => $policy->id]);
	}
}
