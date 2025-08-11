<?php

namespace Tests\Feature;

use App\Models\{Pipeline, Stage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PipelineControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass all permission checks
		Gate::before(fn () => true);

		// Have creatorId() return the user's own ID
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
	 ** The index action should list only the pipelines
	 ** created by the authenticated user.
	 **/
	public function index_displays_only_user_pipelines()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$pipe1 = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);
		Pipeline::factory()->create(['created_by' => $other->creatorId()]);

		$response = $this->actingAs($user)->get(route('pipelines.index'));

		$response->assertStatus(200)
			->assertViewIs('pipelines.index')
			->assertViewHas(
				'pipelines',
				fn ($list) => $list->pluck('id')->all() === [$pipe1->id]
			);
	}

	/**
	 ** @test
	 **
	 ** The create action should render the pipeline creation form.
	 **/
	public function create_page_is_accessible()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)->get(route('pipelines.create'));

		$response->assertStatus(200)
			->assertViewIs('pipelines.create');
	}

	/**
	 ** @test
	 **
	 ** Storing a valid pipeline should persist it
	 ** and redirect to the index.
	 **/
	public function store_creates_pipeline_and_redirects()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->post(route('pipelines.store'), ['name' => 'New Pipeline']);

		$response->assertRedirect(route('pipelines.index'));
		$this->assertDatabaseHas('pipelines', [
			'name'       => 'New Pipeline',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** If validation fails on store, the user should
	 ** be redirected back with an error message.
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();

		// Missing 'name'
		$response = $this->actingAs($user)
			->post(route('pipelines.store'), []);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The edit action should display the edit form only
	 ** for the owner of the pipeline.
	 **/
	public function edit_shows_pipeline_for_owner_only()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$pipe1 = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);
		$pipe2 = Pipeline::factory()->create(['created_by' => $other->creatorId()]);

		// Owner may edit
		$ok = $this->actingAs($user)->get(route('pipelines.edit', $pipe1));
		$ok->assertStatus(200)->assertViewIs('pipelines.edit');

		// Non-owner denied
		$denied = $this->actingAs($user)->get(route('pipelines.edit', $pipe2));
		$denied->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Updating a pipeline with a valid name should persist the change
	 ** and redirect to the index.
	 **/
	public function update_changes_pipeline_name()
	{
		$user = User::factory()->create();
		$pipe = Pipeline::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Old Name',
		]);

		$response = $this->actingAs($user)
			->put(route('pipelines.update', $pipe), ['name' => 'Updated']);

		$response->assertRedirect(route('pipelines.index'));
		$this->assertDatabaseHas('pipelines', [
			'id'   => $pipe->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** If validation fails on update (e.g. name too long),
	 ** the user should be redirected back with an error.
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		$user = User::factory()->create();
		$pipe = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->put(route('pipelines.update', $pipe), ['name' => str_repeat('x', 21)]);

		$response->assertRedirect();
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Destroying a pipeline with no stages should delete it
	 ** and redirect with success.
	 **/
	public function destroy_deletes_pipeline_without_stages()
	{
		$user = User::factory()->create();
		$pipe = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->delete(route('pipelines.destroy', $pipe));

		$response->assertRedirect(route('pipelines.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('pipelines', ['id' => $pipe->id]);
	}

	/**
	 ** @test
	 **
	 ** Attempting to delete a pipeline that has stages
	 ** should redirect back with an error and not delete it.
	 **/
	public function destroy_redirects_with_error_if_pipeline_has_stages()
	{
		$user = User::factory()->create();
		$pipe = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);

		// Attach a stage to trigger the constraint
		Stage::factory()->create(['pipeline_id' => $pipe->id]);

		$response = $this->actingAs($user)
			->delete(route('pipelines.destroy', $pipe));

		$response->assertRedirect(route('pipelines.index'))
			->assertSessionHas('error');
		$this->assertDatabaseHas('pipelines', ['id' => $pipe->id]);
	}

	/**
	 ** @test
	 **
	 ** If the user lacks the 'manage pipeline' permission,
	 ** accessing show should be denied.
	 **/
	public function it_denies_access_when_not_permitted()
	{
		Gate::before(fn () => false);

		$user = User::factory()->create();
		$pipeline = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('pipelines.show', $pipeline));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** If the user does not own the pipeline,
	 ** show should be forbidden (ownership denied).
	 **/
	public function it_denies_access_when_not_owner()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$pipeline = Pipeline::factory()->create(['created_by' => $other->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('pipelines.show', $pipeline));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** If the user is permitted and owns the pipeline,
	 ** show should redirect back to the index.
	 **/
	public function it_redirects_to_index_when_owner_and_permitted()
	{
		$user = User::factory()->create();
		$pipeline = Pipeline::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)
			->get(route('pipelines.show', $pipeline));

		$response->assertRedirect(route('pipelines.index'));
	}
}
