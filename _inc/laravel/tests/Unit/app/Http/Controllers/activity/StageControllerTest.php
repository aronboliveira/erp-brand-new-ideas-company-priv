<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Deal, Pipeline, Stage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class StageControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $owner;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks
		Gate::before(fn () => true);

		// make ownerId() return the user's own ID
		User::macro('ownerId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->owner = User::factory()->create(['type' => 'company']);
		$this->other = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** Index should list stages grouped by pipeline for authorized user.
	 **/
	public function test_index_lists_stages_grouped_by_pipeline_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage stage']);
		$user?->givePermissionTo('manage stage');

		$p1 = Pipeline::factory()->create(['created_by' => $user?->ownerId(), 'name' => 'Pipeline A']);
		$p2 = Pipeline::factory()->create(['created_by' => $user?->ownerId(), 'name' => 'Pipeline B']);

		$s1 = Stage::factory()->create(['pipeline_id' => $p1->id, 'created_by' => $user?->ownerId(), 'order' => 0]);
		$s2 = Stage::factory()->create(['pipeline_id' => $p1->id, 'created_by' => $user?->ownerId(), 'order' => 1]);
		$s3 = Stage::factory()->create(['pipeline_id' => $p2->id, 'created_by' => $user?->ownerId(), 'order' => 0]);

		$response = $this->actingAs($user)->get(route('stages.index'));

		$response->assertStatus(200)
			->assertViewIs('stages.index')
			->assertViewHas('pipelines', function ($pipelines) use ($p1, $p2) {
				return isset($pipelines[$p1->id]['stages'])
					&& count($pipelines[$p1->id]['stages']) === 2
					&& isset($pipelines[$p2->id]['stages'])
					&& count($pipelines[$p2->id]['stages']) === 1;
			});
	}

	/**
	 ** @test
	 **
	 ** Create should show form for authorized user.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create stage']);
		$user?->givePermissionTo('create stage');

		Pipeline::factory()->create(['created_by' => $user?->ownerId(), 'name' => 'Pipe']);

		$response = $this->actingAs($user)->get(route('stages.create'));

		$response->assertStatus(200)
			->assertViewIs('stages.create')
			->assertViewHas('pipelines');
	}

	/**
	 ** @test
	 **
	 ** Store should persist new stage and redirect on success.
	 **/
	public function test_store_persists_new_stage_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create stage']);
		$user?->givePermissionTo('create stage');

		$pipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);

		$response = $this->actingAs($user)->post(route('stages.store'), [
			'name'        => 'New Stage',
			'pipeline_id' => $pipeline->id,
		]);

		$response->assertRedirect(route('stages.index'))
			->assertSessionHas('success', __('Deal Stage successfully created!'));

		$this->assertDatabaseHas('stages', [
			'name'        => 'New Stage',
			'pipeline_id' => $pipeline->id,
			'created_by'  => $user?->ownerId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation when name is missing.
	 **/
	public function test_store_fails_validation_with_empty_name()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create stage']);
		$user?->givePermissionTo('create stage');

		$pipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);

		$response = $this->actingAs($user)->post(route('stages.store'), [
			'name'        => '',
			'pipeline_id' => $pipeline->id,
		]);

		$response->assertRedirect(route('stages.index'))
			->assertSessionHas('error');

		$this->assertDatabaseCount('stages', 0);
	}

	/**
	 ** @test
	 **
	 ** Edit should show form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit stage']);
		$user?->givePermissionTo('edit stage');

		$pipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$stage = Stage::factory()->create([
			'pipeline_id' => $pipeline->id,
			'created_by'  => $user?->ownerId(),
		]);

		$response = $this->actingAs($user)->get(route('stages.edit', $stage));

		$response->assertStatus(200)
			->assertViewIs('stages.edit')
			->assertViewHasAll(['stage', 'pipelines']);
	}

	/**
	 ** @test
	 **
	 ** Update should change stage and redirect on success.
	 **/
	public function test_update_changes_stage_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit stage']);
		$user?->givePermissionTo('edit stage');

		$p1 = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$p2 = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);

		$stage = Stage::factory()->create([
			'name'        => 'Old',
			'pipeline_id' => $p1->id,
			'created_by'  => $user?->ownerId(),
		]);

		$response = $this->actingAs($user)->put(route('stages.update', $stage), [
			'name'        => 'Updated',
			'pipeline_id' => $p2->id,
		]);

		$response->assertRedirect(route('stages.index'))
			->assertSessionHas('success', __('Deal Stage successfully updated!'));

		$this->assertDatabaseHas('stages', [
			'id'          => $stage->id,
			'name'        => 'Updated',
			'pipeline_id' => $p2->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete stage and redirect on success.
	 **/
	public function test_destroy_deletes_stage_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete stage']);
		$user?->givePermissionTo('delete stage');

		$pipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$stage = Stage::factory()->create([
			'pipeline_id' => $pipeline->id,
			'created_by'  => $user?->ownerId(),
		]);

		$response = $this->actingAs($user)->delete(route('stages.destroy', $stage));

		$response->assertRedirect(route('stages.index'))
			->assertSessionHas('success', __('Deal Stage successfully deleted!'));

		$this->assertModelMissing($stage);
	}

	/**
	 ** @test
	 **
	 ** Destroy should fail when there are deals on the stage.
	 **/
	public function test_destroy_fails_if_stage_has_deals()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete stage']);
		$user?->givePermissionTo('delete stage');

		$pipeline = Pipeline::factory()->create(['created_by' => $user?->ownerId()]);
		$stage = Stage::factory()->create([
			'pipeline_id' => $pipeline->id,
			'created_by'  => $user?->ownerId(),
		]);

		Deal::factory()->create([
			'stage_id'   => $stage->id,
			'created_by' => $stage->created_by,
		]);

		$response = $this->actingAs($user)->delete(route('stages.destroy', $stage));

		$response->assertRedirect(route('stages.index'))
			->assertSessionHas('error', __('There are some deals on stage, please remove it first!'));

		$this->assertModelExists($stage);
	}

	/**
	 ** @test
	 **
	 ** Order should update positions and return ok status.
	 **/
	public function test_order_updates_positions_and_returns_ok()
	{
		$user = User::factory()->create();

		Stage::factory()->create(['id' => 10, 'order' => 0]);
		Stage::factory()->create(['id' => 20, 'order' => 1]);

		$response = $this->actingAs($user)->postJson(route('stages.order'), [
			'order' => [20, 10],
		]);

		$response->assertJson(['status' => 'ok']);
		$this->assertDatabaseHas('stages', ['id' => 20, 'order' => 0]);
		$this->assertDatabaseHas('stages', ['id' => 10, 'order' => 1]);
	}

	/**
	 ** @test
	 **
	 ** Json should return name list filtered by pipeline when given.
	 **/
	public function test_json_returns_names_filtered_by_pipeline()
	{
		$user = User::factory()->create();

		$p1 = Pipeline::factory()->create();
		$p2 = Pipeline::factory()->create();

		$s1 = Stage::factory()->create(['pipeline_id' => $p1->id, 'name' => 'One']);
		$s2 = Stage::factory()->create(['pipeline_id' => $p2->id, 'name' => 'Two']);

		$response = $this->actingAs($user)->getJson(route('stages.json', ['pipeline_id' => $p1->id]));

		$response->assertJson([$s1->id => 'One'])
			->assertJsonMissing([$s2->id => 'Two']);
	}

	/** @test
	 **
	 ** show redirects owner back to index
	 **/
	public function show_redirects_owner_to_index()
	{
		// given a stage belonging to owner
		$stage = Stage::factory()->create([
			'created_by' => $this->owner->ownerId(),
		]);

		// when owner visits show
		$resp = $this->actingAs($this->owner)
			->get(route('stage.show', $stage));

		// then redirected to index
		$resp->assertRedirect(route('stage.index'));
	}

	/** @test
	 **
	 ** show denies non-owner with permission error
	 **/
	public function show_denies_non_owner_and_redirects_with_error()
	{
		// given a stage belonging to owner
		$stage = Stage::factory()->create([
			'created_by' => $this->owner->ownerId(),
		]);

		// when another user tries to view it
		$resp = $this->actingAs($this->other)
			->get(route('stage.show', $stage));

		// then redirected to index with error
		$resp->assertRedirect(route('stage.index'))
			->assertSessionHas('error');
	}

	/** @test
	 **
	 ** show redirects guests to login
	 **/
	public function show_redirects_guest_to_login()
	{
		// given any stage
		$stage = Stage::factory()->create();

		// when unauthenticated
		$resp = $this->get(route('stage.show', $stage));

		// then they are redirected to login
		$resp->assertRedirect();
	}
}
