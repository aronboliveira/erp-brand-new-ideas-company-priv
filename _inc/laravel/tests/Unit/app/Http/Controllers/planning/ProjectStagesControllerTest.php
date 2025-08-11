<?php

namespace Tests\Feature\Controllers;

use App\Models\{ProjectStages, Task, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProjectStagesControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Bypass all permission checks
		Gate::before(fn () => true);

		// Ensure creatorId() returns the user's own ID
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
	}

	/**
	 ** @test
	 **
	 ** index displays only stages created by the user in correct order.
	 **/
	public function index_displays_user_stages()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		ProjectStages::factory()->create([
			'created_by' => $user?->creatorId(),
			'order'      => 2,
		]);
		ProjectStages::factory()->create([
			'created_by' => $user?->creatorId(),
			'order'      => 1,
		]);
		// other user's stage
		ProjectStages::factory()->create([
			'created_by' => $user?->creatorId() + 1,
			'order'      => 0,
		]);

		$response = $this->get(route('projectstages.index'));

		$response->assertOk()
			->assertViewIs('projectstages.index')
			->assertViewHas('projectStages', function ($stages) {
				return $stages->count() === 2
					&& $stages->pluck('order')->all() === [1, 2];
			});
	}

	/**
	 ** @test
	 **
	 ** create displays the create form.
	 **/
	public function create_displays_form()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('projectstages.create'));

		$response->assertOk()
			->assertViewIs('projectstages.create');
	}

	/**
	 ** @test
	 **
	 ** store creates a new stage and redirects, or fails validation.
	 **/
	public function store_creates_and_validates()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// validation failure
		$responseFail = $this->post(route('projectstages.store'), ['name' => '']);
		$responseFail->assertRedirect(route('projectstages.index'))
			->assertSessionHas('error');

		// successful create
		$response = $this->post(route('projectstages.store'), [
			'name'  => 'New Stage',
			'color' => 'ff0000',
		]);
		$response->assertRedirect(route('projectstages.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('projectstages', [
			'name'       => 'New Stage',
			'color'      => '#ff0000',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit displays form for owner or denies access otherwise.
	 **/
	public function edit_displays_or_denies()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$this->actingAs($user);

		$stageOwned = ProjectStages::factory()->create(['created_by' => $user?->creatorId()]);
		$stageOther = ProjectStages::factory()->create(['created_by' => $other->creatorId()]);

		// owner can edit
		$ok = $this->get(route('projectstages.edit', $stageOwned->id));
		$ok->assertOk()->assertViewIs('projectstages.edit');

		// non-owner denied
		$denied = $this->get(route('projectstages.edit', $stageOther->id));
		$denied->assertRedirect(route('projectstages.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update modifies stage or fails validation.
	 **/
	public function update_modifies_and_validates()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$stage = ProjectStages::factory()->create([
			'created_by' => $user?->creatorId(),
			'name'       => 'Old',
		]);

		// validation fail
		$respFail = $this->put(route('projectstages.update', $stage->id), ['name' => '']);
		$respFail->assertRedirect(route('projectstages.index'))
			->assertSessionHas('error');

		// success
		$resp = $this->put(route('projectstages.update', $stage->id), [
			'name'  => 'Updated',
			'color' => '00ff00',
		]);
		$resp->assertRedirect(route('projectstages.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('projectstages', [
			'id'    => $stage->id,
			'name'  => 'Updated',
			'color' => '#00ff00',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy removes stage if no tasks, or errors if tasks exist or wrong owner.
	 **/
	public function destroy_deletes_or_errors()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$stage = ProjectStages::factory()->create(['created_by' => $user?->creatorId()]);
		$other = ProjectStages::factory()->create(['created_by' => $user?->creatorId() + 1]);

		// wrong owner
		$resp1 = $this->delete(route('projectstages.destroy', $other->id));
		$resp1->assertRedirect(route('projectstages.index'))
			->assertSessionHas('error');

		// assign task to stage
		Task::factory()->create(['stage' => $stage->id]);
		$resp2 = $this->delete(route('projectstages.destroy', $stage->id));
		$resp2->assertRedirect(route('projectstages.index'))
			->assertSessionHas('error');
		$this->assertDatabaseHas('projectstages', ['id' => $stage->id]);

		// remove task and succeed
		Task::where('stage', $stage->id)->delete();
		$resp3 = $this->delete(route('projectstages.destroy', $stage->id));
		$resp3->assertRedirect(route('projectstages.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('projectstages', ['id' => $stage->id]);
	}

	/**
	 ** @test
	 **
	 ** order endpoint reorders stages or denies without permission.
	 **/
	public function order_reorders_or_denies()
	{
		// deny permission
		Gate::before(fn ($user, $ability) => $ability === 'move project stage' ? false : null);
		$user = User::factory()->create();
		$this->actingAs($user);

		$respDeny = $this->postJson(route('projectstages.order'), ['order' => []]);
		$respDeny->assertStatus(401);

		// allow and reorder
		Gate::before(fn () => true);
		$s1 = ProjectStages::factory()->create(['created_by' => $user?->creatorId(), 'order' => 0]);
		$s2 = ProjectStages::factory()->create(['created_by' => $user?->creatorId(), 'order' => 1]);

		$resp = $this->postJson(route('projectstages.order'), ['order' => [$s2->id, $s1->id]]);
		$resp->assertOk()->assertJson(['success' => true]);

		$this->assertDatabaseHas('projectstages', ['id' => $s2->id, 'order' => 0]);
		$this->assertDatabaseHas('projectstages', ['id' => $s1->id, 'order' => 1]);
	}
}
