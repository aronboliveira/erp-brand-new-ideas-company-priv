<?php

namespace Tests\Feature;

use App\Models\{Label, Pipeline, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;
	private Pipeline $pipeline;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks by default
		Gate::before(fn () => true);

		// ownerId() returns own ID
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

		$this->company = User::factory()->create(['type' => 'company']);
		$this->other   = User::factory()->create(['type' => 'company']);
		$this->pipeline = Pipeline::factory()->create(['created_by' => $this->company->ownerId()]);

		$this->actingAs($this->company);
	}

	/**
	 ** @test
	 **
	 ** index_lists_labels_grouped_by_pipeline_for_owner_only
	 **
	 ** Should fetch and group only labels created by the owner, deny when lacking permission.
	 **/
	public function index_lists_labels_grouped_by_pipeline_for_owner_only()
	{
		// setup labels in two pipelines
		$p2 = Pipeline::factory()->create(['created_by' => $this->company->ownerId()]);
		Label::factory()->create([
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->company->ownerId(),
		]);
		Label::factory()->create([
			'pipeline_id' => $p2->id,
			'created_by'  => $this->company->ownerId(),
		]);
		// other user's label
		Label::factory()->create([
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->other->ownerId(),
		]);

		// authorized
		$resp = $this->get(route('labels.index'));
		$resp->assertOk()
			->assertViewIs('labels.index')
			->assertViewHas('pipelines', fn ($groups) => count($groups) === 2);

		// deny permission
		Gate::before(fn () => false);
		$this->get(route('labels.index'))->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form_with_pipelines_and_colors
	 **
	 ** Should render create form for authorized user.
	 **/
	public function create_displays_form_with_pipelines_and_colors()
	{
		$resp = $this->actingAs($this->company)
			->get(route('labels.create'));
		$resp->assertOk()
			->assertViewIs('labels.create')
			->assertViewHasAll(['pipelines', 'colors']);

		// deny
		Gate::before(fn () => false);
		$this->get(route('labels.create'))->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_label
	 **
	 ** Missing fields redirect with error; valid input persists.
	 **/
	public function store_validates_and_creates_label()
	{
		// validation failure
		$this->post(route('labels.store'), [])
			->assertRedirect(route('labels.index'))
			->assertSessionHas('error');

		// success
		Gate::before(fn () => true);
		$payload = [
			'name'        => 'New Label',
			'pipeline_id' => $this->pipeline->id,
			'color'       => Label::$colors[0],
		];
		$this->post(route('labels.store'), $payload)
			->assertRedirect(route('labels.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('labels', [
			'name'        => 'New Label',
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->company->ownerId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show_redirects_to_index
	 **
	 ** Accessing show should redirect back to index.
	 **/
	public function show_redirects_to_index()
	{
		$label = Label::factory()->create(['created_by' => $this->company->ownerId()]);
		$this->get(route('labels.show', $label))
			->assertRedirect(route('labels.index'));
	}

	/**
	 ** @test
	 **
	 ** edit_enforces_permission_and_ownership
	 **
	 ** Only owner with permission can access edit form.
	 **/
	public function edit_enforces_permission_and_ownership()
	{
		$label = Label::factory()->create([
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->company->ownerId(),
		]);

		// no permission
		Gate::before(fn () => false);
		$this->get(route('labels.edit', $label))->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->get(route('labels.edit', $label))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->get(route('labels.edit', $label))
			->assertOk()
			->assertViewIs('labels.edit')
			->assertViewHasAll(['label', 'pipelines', 'colors']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_changes
	 **
	 ** Invalid input errors; valid updates persisted.
	 **/
	public function update_validates_and_saves_changes()
	{
		$label = Label::factory()->create([
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->company->ownerId(),
		]);

		// validation fail
		$this->put(route('labels.update', $label), ['name' => ''])
			->assertRedirect(route('labels.index'))
			->assertSessionHas('error');

		// success
		$payload = [
			'name'        => 'Updated Label',
			'pipeline_id' => $this->pipeline->id,
			'color'       => Label::$colors[1],
		];
		$this->put(route('labels.update', $label), $payload)
			->assertRedirect(route('labels.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('labels', [
			'id'          => $label->id,
			'name'        => 'Updated Label',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_enforces_permission_and_deletes_label
	 **
	 ** Only owner with permission may delete.
	 **/
	public function destroy_enforces_permission_and_deletes_label()
	{
		$label = Label::factory()->create([
			'pipeline_id' => $this->pipeline->id,
			'created_by'  => $this->company->ownerId(),
		]);

		// no permission
		Gate::before(fn () => false);
		$this->delete(route('labels.destroy', $label))->assertStatus(403);

		// wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->delete(route('labels.destroy', $label))
			->assertStatus(403);

		// success
		$this->actingAs($this->company)
			->delete(route('labels.destroy', $label))
			->assertRedirect(route('labels.index'))
			->assertSessionHas('success');

		$this->assertDatabaseMissing('labels', ['id' => $label->id]);
	}
}
