<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\{Gate, Storage};
use Spatie\Permission\Models\Permission;
use App\Models\{User, TimeTracker, TrackPhoto, Project, ProjectTask};

class TimeTrackerControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private TimeTracker $tracker;

	protected function setUp(): void
	{
		parent::setUp();

		// ensure our user model has creatorId()
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

		// common user
		$this->user = User::factory()->create();

		// make sure storage is fake
		Storage::fake('local');

		// set up a project & task to satisfy store/update rules
		$project = Project::factory()->create(['created_by' => $this->user->id]);
		$task   = ProjectTask::factory()->create([
			'project_id' => $project->id,
			'created_by' => $this->user->id,
		]);

		// a single tracker for show/edit/update/destroy tests
		$this->tracker = TimeTracker::factory()->create([
			'project_id' => $project->id,
			'task_id'    => $task->id,
			'created_by' => $this->user->id,
			'start_time' => now()->subHour(),
			'end_time'   => now(),
			'is_active'  => true,
			'is_billable' => false,
			'name'       => 'Test Track',
		]);
	}

	/** @test
	 ** index should list only the authenticated user’s trackers
	 **/
	public function index_lists_only_user_trackers()
	{
		Permission::create(['name' => 'manage time tracker']);
		$this->user->givePermissionTo('manage time tracker');

		// add one more for this user and one for another
		TimeTracker::factory()->create(['created_by' => $this->user->id]);
		TimeTracker::factory()->create();

		$response = $this->actingAs($this->user)->get(route('time-tracker.index'));

		$response->assertOk()
			->assertViewIs('time_trackers.index')
			->assertViewHas('trackers', function ($trackers) {
				return $trackers->every(fn ($t) => $t->created_by === $this->user->id);
			});
	}

	/** @test
	 ** create should show the creation form when permitted
	 **/
	public function create_displays_form_for_permitted_user()
	{
		Permission::create(['name' => 'create time tracker']);
		$this->user->givePermissionTo('create time tracker');

		$response = $this->actingAs($this->user)->get(route('time-tracker.create'));

		$response->assertOk()
			->assertViewIs('time_trackers.create');
	}

	/** @test
	 ** store should validate input and redirect back with errors on failure
	 **/
	public function store_validation_failure_redirects_back_with_error()
	{
		Permission::create(['name' => 'create time tracker']);
		$this->user->givePermissionTo('create time tracker');

		$response = $this->actingAs($this->user)
			->post(route('time-tracker.store'), [
				'project_id' => null, // missing required
			]);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test
	 ** store should persist a valid tracker and redirect with success
	 **/
	public function store_persists_tracker_and_redirects()
	{
		Permission::create(['name' => 'create time tracker']);
		$this->user->givePermissionTo('create time tracker');

		// we need valid related project and task
		$project   = Project::factory()->create(['created_by' => $this->user->id]);
		$task      = ProjectTask::factory()->create([
			'project_id' => $project->id,
			'created_by' => $this->user->id,
		]);

		$payload = [
			'project_id'  => $project->id,
			'task_id'     => $task->id,
			'is_active'   => true,
			'tag_id'      => null,
			'name'        => 'New Track',
			'is_billable' => false,
			'start_time'  => now()->subHour()->toDateTimeString(),
			'end_time'    => now()->toDateTimeString(),
			'total_time'  => 60,
		];

		$response = $this->actingAs($this->user)
			->post(route('time-tracker.store'), $payload);

		$response->assertRedirect(route('time-tracker.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('time_trackers', [
			'name'        => 'New Track',
			'created_by'  => $this->user->id,
		]);
	}

	/** @test
	 ** show should display a tracker to its owner
	 **/
	public function show_displays_tracker_for_owner()
	{
		Permission::create(['name' => 'view time tracker']);
		$this->user->givePermissionTo('view time tracker');

		$response = $this->actingAs($this->user)
			->get(route('time-tracker.show', $this->tracker));

		$response->assertOk()
			->assertViewIs('time_trackers.show')
			->assertViewHas('timeTracker', fn ($t) => $t->id === $this->tracker->id);
	}

	/** @test
	 ** show returns 403 if not owner
	 **/
	public function show_returns_403_for_non_owner()
	{
		Permission::create(['name' => 'view time tracker']);
		$this->user->givePermissionTo('view time tracker');

		$other = User::factory()->create();
		$response = $this->actingAs($other)
			->get(route('time-tracker.show', $this->tracker));

		$response->assertStatus(403);
	}

	/** @test
	 ** edit should display the edit form for owner
	 **/
	public function edit_displays_form_for_owner()
	{
		Permission::create(['name' => 'edit time tracker']);
		$this->user->givePermissionTo('edit time tracker');

		$response = $this->actingAs($this->user)
			->get(route('time-tracker.edit', $this->tracker));

		$response->assertOk()
			->assertViewIs('time_trackers.edit')
			->assertViewHas('timeTracker', fn ($t) => $t->id === $this->tracker->id);
	}

	/** @test
	 ** edit returns 403 for non-owner
	 **/
	public function edit_returns_403_for_non_owner()
	{
		Permission::create(['name' => 'edit time tracker']);
		$this->user->givePermissionTo('edit time tracker');

		$other = User::factory()->create();
		$response = $this->actingAs($other)
			->get(route('time-tracker.edit', $this->tracker));

		$response->assertStatus(403);
	}

	/** @test
	 ** update should validate input and redirect back with error on failure
	 **/
	public function update_validation_failure_redirects_back()
	{
		Permission::create(['name' => 'edit time tracker']);
		$this->user->givePermissionTo('edit time tracker');

		$response = $this->actingAs($this->user)
			->put(route('time-tracker.update', $this->tracker), [
				'project_id' => null, // invalid
			]);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test
	 ** update should persist changes and redirect with success
	 **/
	public function update_persists_changes_and_redirects()
	{
		Permission::create(['name' => 'edit time tracker']);
		$this->user->givePermissionTo('edit time tracker');

		$project   = Project::factory()->create(['created_by' => $this->user->id]);
		$task      = ProjectTask::factory()->create([
			'project_id' => $project->id,
			'created_by' => $this->user->id,
		]);

		$payload = [
			'project_id'  => $project->id,
			'task_id'     => $task->id,
			'is_active'   => false,
			'tag_id'      => null,
			'name'        => 'Updated Track',
			'is_billable' => true,
			'start_time'  => now()->toDateTimeString(),
			'end_time'    => now()->addHour()->toDateTimeString(),
			'total_time'  => 120,
		];

		$response = $this->actingAs($this->user)
			->put(route('time-tracker.update', $this->tracker), $payload);

		$response->assertRedirect(route('time-tracker.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('time_trackers', [
			'id'          => $this->tracker->id,
			'name'        => 'Updated Track',
			'is_active'   => 0,
			'is_billable' => 1,
		]);
	}

	/** @test
	 ** destroy should remove tracker and associated photos
	 **/
	public function destroy_deletes_tracker_and_photos_and_redirects()
	{
		Permission::create(['name' => 'delete time tracker']);
		$this->user->givePermissionTo('delete time tracker');

		// create a photo under this tracker
		$photo = TrackPhoto::factory()->create([
			'track_id' => $this->tracker->id,
			'user_id'  => $this->user->id,
			'img_path' => 'photos/test.jpg',
		]);
		Storage::disk('local')->put('photos/test.jpg', 'dummy');

		$response = $this->actingAs($this->user)
			->delete(route('time-tracker.destroy', ['trackerId' => $this->tracker->id]));

		$response->assertRedirect(route('time-tracker.index'))
			->assertSessionHas('success');

		$this->assertModelMissing($this->tracker);
		$this->assertModelMissing($photo);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertMissing('photos/test.jpg');
	}

	/** @test
	 ** getTrackerImages should render images view for owner
	 **/
	public function get_tracker_images_displays_view_for_owner()
	{
		Permission::create(['name' => 'manage time tracker']);
		$this->user->givePermissionTo('manage time tracker');

		$photo = TrackPhoto::factory()->create([
			'track_id' => $this->tracker->id,
			'user_id'  => $this->user->id,
		]);

		$response = $this->actingAs($this->user)
			->post(route('time-tracker.getTrackerImages'), ['id' => $this->tracker->id]);

		$response->assertOk()
			->assertViewIs('time_trackers.images')
			->assertViewHasAll(['images', 'tracker'])
			->assertViewHas('images', fn ($imgs) => $imgs->contains('id', $photo->id));
	}

	/** @test
	 ** removeTrackerImages should delete the photo via JSON
	 **/
	public function remove_tracker_images_deletes_photo_and_returns_json_success()
	{
		Permission::create(['name' => 'delete time tracker']);
		$this->user->givePermissionTo('delete time tracker');

		$photo = TrackPhoto::factory()->create([
			'user_id'  => $this->user->id,
			'img_path' => 'photos/remove.jpg',
		]);
		Storage::disk('local')->put('photos/remove.jpg', 'data');

		$response = $this->actingAs($this->user)
			->postJson(route('time-tracker.removeTrackerImages'), ['id' => $photo->id]);

		$response->assertJson(['success' => true]);
		$this->assertModelMissing($photo);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter::disk('local')->assertMissing('photos/remove.jpg');
	}

	/** @test
	 ** removeTracker should delete a tracker via JSON
	 **/
	public function remove_tracker_deletes_tracker_and_returns_json_success()
	{
		Permission::create(['name' => 'delete time tracker']);
		$this->user->givePermissionTo('delete time tracker');

		$response = $this->actingAs($this->user)
			->postJson(route('time-tracker.removeTracker'), ['id' => $this->tracker->id]);

		$response->assertJson(['success' => true]);
		$this->assertModelMissing($this->tracker);
	}
}
