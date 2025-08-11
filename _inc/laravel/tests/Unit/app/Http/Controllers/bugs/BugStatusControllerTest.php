<?php

namespace Tests\Feature;

use App\Models\BugStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BugStatusControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions for simplicity in these tests
		Gate::before(fn () => true);

		// Ensure creatorId() exists on User and returns the user’s own ID
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
	 ** index should retrieve all BugStatus records created by the user
	 ** and render them in the 'bugstatus.index' view ordered by ID.
	 **/
	public function index_displays_bugStatus_list()
	{
		$user = User::factory()->create();
		$statuses = BugStatus::factory()->count(3)->create([
			'created_by' => $user?->creatorId(),
			'order'      => 0,
		]);

		$response = $this->actingAs($user)->get(route('bugstatus.index'));

		$response->assertStatus(200)
			->assertViewIs('bugstatus.index')
			->assertViewHas('bug_statuses', function ($viewStatuses) use ($statuses) {
				return $viewStatuses->pluck('id')->sort()->values()
					->all() === $statuses->pluck('id')->sort()->values()->all();
			});
	}

	/**
	 ** @test
	 **
	 ** create should render the 'bugstatus.create' form
	 ** for adding a new BugStatus.
	 **/
	public function create_page_is_accessible()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)->get(route('bugstatus.create'));

		$response->assertStatus(200)
			->assertViewIs('bugstatus.create');
	}

	/**
	 ** @test
	 **
	 ** store should create a new BugStatus with the next order value
	 ** and redirect back to the index.
	 **/
	public function store_creates_new_bugStatus_with_incremented_order()
	{
		$user = User::factory()->create();

		// pre-seed one status so max order = 0
		BugStatus::factory()->create([
			'created_by' => $user?->creatorId(),
			'order'      => 0,
		]);

		$response = $this->actingAs($user)
			->post(route('bugstatus.store'), [
				'title' => 'In Progress',
			]);

		$response->assertRedirect(route('bugstatus.index'));
		$this->assertDatabaseHas('bug_statuses', [
			'title'      => 'In Progress',
			'order'      => 1,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** edit should display the 'bugstatus.edit' form
	 ** populated with the existing BugStatus data.
	 **/
	public function edit_page_shows_existing_status()
	{
		$user = User::factory()->create();
		$status = BugStatus::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('bugstatus.edit', $status));

		$response->assertStatus(200)
			->assertViewIs('bugstatus.edit')
			->assertViewHas('bug_status', function ($viewStatus) use ($status) {
				return $viewStatus->id === $status->id;
			});
	}

	/**
	 ** @test
	 **
	 ** update should change the title of the specified BugStatus
	 ** and redirect back to the index.
	 **/
	public function update_changes_title_of_existing_status()
	{
		$user = User::factory()->create();
		$status = BugStatus::factory()->create([
			'created_by' => $user?->creatorId(),
			'title'      => 'Old Title',
		]);

		$response = $this->actingAs($user)
			->put(route('bugstatus.update', $status), [
				'title' => 'New Title',
			]);

		$response->assertRedirect(route('bugstatus.index'));
		$this->assertDatabaseHas('bug_statuses', [
			'id'    => $status->id,
			'title' => 'New Title',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the specified BugStatus record
	 ** and redirect back to the index view.
	 **/
	public function destroy_deletes_the_status()
	{
		$user = User::factory()->create();
		$status = BugStatus::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->delete(route('bugstatus.destroy', $status));

		$response->assertRedirect(route('bugstatus.index'));
		$this->assertDatabaseMissing('bug_statuses', [
			'id' => $status->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** order endpoint should accept a new sequence of BugStatus IDs
	 ** and update each record's 'order' field accordingly,
	 ** then return a 204 No Content response.
	 **/
	public function order_endpoint_updates_each_status_order()
	{
		$user = User::factory()->create();
		$statuses = BugStatus::factory()->count(3)->create([
			'created_by' => $user?->creatorId(),
		]);

		// reorder: [2, 0, 1]
		$newOrder = [
			$statuses[2]->id,
			$statuses[0]->id,
			$statuses[1]->id,
		];

		$response = $this->actingAs($user)
			->post(route('bugstatus.order'), [
				'order' => $newOrder,
			]);

		$response->assertNoContent();

		foreach ($newOrder as $index => $id) {
			$this->assertDatabaseHas('bug_statuses', [
				'id'    => $id,
				'order' => $index,
			]);
		}
	}

	/**
	 ** @test
	 **
	 ** show route should redirect to index since there is no
	 ** dedicated 'show' view for a single BugStatus.
	 **/
	public function show_redirects_to_index()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('bugstatus.show', 999));

		$response->assertRedirect(route('bugstatus.index'));
	}
}
