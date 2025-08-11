<?php

namespace Tests\Feature\Controllers;

use App\Models\{User, Pipeline, LeadStage};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LeadStageControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected int $ownerId;

	protected function setUp(): void
	{
		parent::setUp();

		// Create and authenticate a user
		$this->user   = User::factory()->create();
		$this->actingAs($this->user);
		$this->ownerId = $this->user->ownerId();

		// By default grant all permissions
		Gate::define('manage lead stage', fn () => true);
		Gate::define('create lead stage', fn () => true);
		Gate::define('edit lead stage', fn () => true);
		Gate::define('delete lead stage', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Deny access to the index when 'manage lead stage' permission is not granted.
	 **/
	public function test_index_denies_without_permission()
	{
		Gate::define('manage lead stage', fn () => false);

		$response = $this->get(route('leadStages.index'));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Show lead stages grouped by pipeline when permission is granted.
	 **/
	public function test_index_shows_grouped_by_pipeline()
	{
		$p1 = Pipeline::factory()->create(['created_by' => $this->ownerId]);
		$p2 = Pipeline::factory()->create(['created_by' => $this->ownerId]);

		LeadStage::factory()->count(2)->create([
			'pipeline_id' => $p1->id,
			'created_by'  => $this->ownerId,
		]);
		LeadStage::factory()->count(3)->create([
			'pipeline_id' => $p2->id,
			'created_by'  => $this->ownerId,
		]);

		$resp = $this->get(route('leadStages.index'));
		$resp->assertStatus(200)
			->assertViewIs('leadStages.index')
			->assertViewHas('pipelines');

		$viewPipelines = $resp->viewData('pipelines');
		$this->assertCount(2, $viewPipelines);
		$this->assertCount(2, $viewPipelines[$p1->id]['leadStages']);
		$this->assertCount(3, $viewPipelines[$p2->id]['leadStages']);
	}

	/**
	 ** @test
	 **
	 ** Deny access to the create form when 'create lead stage' permission is not granted.
	 **/
	public function test_create_denies_without_permission()
	{
		Gate::define('create lead stage', fn () => false);

		$resp = $this->get(route('leadStages.create'));
		$resp->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Display the create form with available pipelines when permitted.
	 **/
	public function test_create_shows_pipeline_list()
	{
		Pipeline::factory()->count(2)->create(['created_by' => $this->ownerId]);

		$resp = $this->get(route('leadStages.create'));
		$resp->assertStatus(200)
			->assertViewIs('leadStages.create')
			->assertViewHas('pipelines');
	}

	/**
	 ** @test
	 **
	 ** Redirect back with error when store validation fails.
	 **/
	public function test_store_validation_errors_redirect_back()
	{
		$resp = $this->post(route('leadStages.store'), []);
		$resp->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Create a new lead stage and redirect to index on success.
	 **/
	public function test_store_creates_new_stage()
	{
		$pipeline = Pipeline::factory()->create(['created_by' => $this->ownerId]);

		$resp = $this->post(route('leadStages.store'), [
			'name'        => 'Initial Contact',
			'pipeline_id' => $pipeline->id,
		]);

		$resp->assertRedirect(route('leadStages.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('lead_stages', [
			'name'        => 'Initial Contact',
			'pipeline_id' => $pipeline->id,
			'created_by'  => $this->ownerId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Redirect show to index.
	 **/
	public function test_show_redirects_to_index()
	{
		$stage = LeadStage::factory()->create([
			'created_by' => $this->ownerId,
		]);

		$resp = $this->get(route('leadStages.show', $stage));
		$resp->assertRedirect(route('leadStages.index'));
	}

	/**
	 ** @test
	 **
	 ** Deny edit when permission missing or wrong owner, allow for correct owner.
	 **/
	public function test_edit_denies_without_permission_or_wrong_owner()
	{
		$stage = LeadStage::factory()->create([
			'created_by' => $this->ownerId,
		]);

		// No permission
		Gate::define('edit lead stage', fn () => false);
		$resp = $this->get(route('leadStages.edit', $stage));
		$resp->assertStatus(403);

		// Wrong owner
		Gate::define('edit lead stage', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$resp = $this->get(route('leadStages.edit', $stage));
		$resp->assertStatus(403);

		// Correct owner
		$this->actingAs($this->user);
		$resp = $this->get(route('leadStages.edit', $stage));
		$resp->assertStatus(200)
			->assertViewIs('leadStages.edit')
			->assertViewHasAll(['leadStage', 'pipelines']);
	}

	/**
	 ** @test
	 **
	 ** Validate update input and save changes on success.
	 **/
	public function test_update_validation_and_success()
	{
		$stage = LeadStage::factory()->create([
			'created_by' => $this->ownerId,
		]);

		// Validation failure
		$resp = $this->put(route('leadStages.update', $stage), ['name' => '']);
		$resp->assertRedirect()
			->assertSessionHas('error');

		// Successful update
		$pipeline = Pipeline::factory()->create(['created_by' => $this->ownerId]);
		$data    = ['name' => 'Follow up', 'pipeline_id' => $pipeline->id];

		$resp = $this->put(route('leadStages.update', $stage), $data);
		$resp->assertRedirect(route('leadStages.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('lead_stages', [
			'id'          => $stage->id,
			'name'        => 'Follow up',
			'pipeline_id' => $pipeline->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Deny destroy when permission missing or wrong owner, allow and delete when valid.
	 **/
	public function test_destroy_denies_without_permission_or_wrong_owner()
	{
		$stage = LeadStage::factory()->create([
			'created_by' => $this->ownerId,
		]);

		// No permission
		Gate::define('delete lead stage', fn () => false);
		$r = $this->delete(route('leadStages.destroy', $stage));
		$r->assertStatus(403);

		// Wrong owner
		Gate::define('delete lead stage', fn () => true);
		$other = User::factory()->create();
		$this->actingAs($other);
		$r = $this->delete(route('leadStages.destroy', $stage));
		$r->assertStatus(403);

		// Correct owner
		$this->actingAs($this->user);
		$r = $this->delete(route('leadStages.destroy', $stage));
		$r->assertRedirect(route('leadStages.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('lead_stages', ['id' => $stage->id]);
	}

	/**
	 ** @test
	 **
	 ** Update the order of lead stages and return OK status.
	 **/
	public function test_order_updates_stage_order()
	{
		$s1 = LeadStage::factory()->create(['created_by' => $this->ownerId]);
		$s2 = LeadStage::factory()->create(['created_by' => $this->ownerId]);
		$s3 = LeadStage::factory()->create(['created_by' => $this->ownerId]);

		Gate::define('edit lead stage', fn () => true);

		$payload = ['order' => [$s3->id, $s1->id, $s2->id]];
		$resp   = $this->postJson(route('leadStages.order'), $payload);

		$resp->assertStatus(200)
			->assertJson(['status' => 'ok']);

		$this->assertDatabaseHas('lead_stages', ['id' => $s3->id, 'order' => 0]);
		$this->assertDatabaseHas('lead_stages', ['id' => $s1->id, 'order' => 1]);
		$this->assertDatabaseHas('lead_stages', ['id' => $s2->id, 'order' => 2]);
	}
}
