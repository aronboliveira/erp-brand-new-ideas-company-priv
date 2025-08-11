<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LeaveTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// By default grant all permissions
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->user = User::factory()->create();
		$this->other = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Index requires "manage leave type" permission and returns 403 if denied.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->user)
			->get(route('leavetype.index'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Index displays only the leave types created by the current user.
	 **/
	public function index_shows_only_user_leave_types()
	{
		LeaveType::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);
		LeaveType::factory()->create(['created_by' => $this->other->creatorId()]);

		$response = $this->actingAs($this->user)
			->get(route('leavetype.index'));

		$response->assertOk()
			->assertViewIs('leavetype.index')
			->assertViewHas('leaveTypes', fn ($list) => $list->count() === 2);
	}

	/**
	 ** @test
	 **
	 ** Create requires "create leave type" permission and returns 403 if denied.
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->user)
			->get(route('leavetype.create'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Create displays the leave type creation form when authorized.
	 **/
	public function create_displays_form()
	{
		$this->actingAs($this->user)
			->get(route('leavetype.create'))
			->assertOk()
			->assertViewIs('leavetype.create');
	}

	/**
	 ** @test
	 **
	 ** Store validates input and creates a new leave type, then redirects with success.
	 **/
	public function store_validates_and_creates_leave_type()
	{
		// missing fields => validation error
		$this->actingAs($this->user)
			->post(route('leavetype.store'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// valid data
		$payload = ['title' => 'Vacation', 'days' => 10];

		$this->actingAs($this->user)
			->post(route('leavetype.store'), $payload)
			->assertRedirect(route('leavetype.index'))
			->assertSessionHas('success', __('LeaveType successfully created.'));

		$this->assertDatabaseHas('leave_types', [
			'title'      => 'Vacation',
			'days'       => 10,
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show always redirects to index.
	 **/
	public function show_always_redirects_to_index()
	{
		$lt = LeaveType::factory()->create(['created_by' => $this->user->creatorId()]);

		$this->actingAs($this->user)
			->get(route('leavetype.show', $lt))
			->assertRedirect(route('leavetype.index'));
	}

	/**
	 ** @test
	 **
	 ** Edit requires permission and ownership. Denied => 401 JSON, allowed => form.
	 **/
	public function edit_requires_permission_and_owner()
	{
		$lt = LeaveType::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->get(route('leavetype.edit', $lt))
			->assertStatus(403);

		// reset to allow permission, but wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->get(route('leavetype.edit', $lt))
			->assertStatus(401)
			->assertJson(['error' => __('Permission denied.')]);

		// correct owner and permission
		$this->actingAs($this->user)
			->get(route('leavetype.edit', $lt))
			->assertOk()
			->assertViewIs('leavetype.edit')
			->assertViewHas('leaveType', fn ($v) => $v->id === $lt->id);
	}

	/**
	 ** @test
	 **
	 ** Update validates input and updates the leave type, then redirects with success.
	 **/
	public function update_validates_and_updates_leave_type()
	{
		$lt = LeaveType::factory()->create([
			'title'      => 'Old',
			'days'       => 5,
			'created_by' => $this->user->creatorId(),
		]);

		// validation error
		$this->actingAs($this->user)
			->put(route('leavetype.update', $lt), ['title' => '', 'days' => ''])
			->assertRedirect()
			->assertSessionHas('error');

		// wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->put(route('leavetype.update', $lt), ['title' => 'X', 'days' => 1])
			->assertStatus(403);

		// correct owner
		$this->actingAs($this->user)
			->put(route('leavetype.update', $lt), ['title' => 'New', 'days' => 7])
			->assertRedirect(route('leavetype.index'))
			->assertSessionHas('success', __('LeaveType successfully updated.'));

		$this->assertDatabaseHas('leave_types', [
			'id'    => $lt->id,
			'title' => 'New',
			'days'  => 7,
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy requires permission and ownership, then deletes the leave type.
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$lt = LeaveType::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->delete(route('leavetype.destroy', $lt))
			->assertStatus(403);

		// reset to allow permission, but wrong owner
		Gate::before(fn () => true);
		$this->actingAs($this->other)
			->delete(route('leavetype.destroy', $lt))
			->assertRedirect(route('leavetype.index'))
			->assertSessionHas('error');

		// correct owner
		$this->actingAs($this->user)
			->delete(route('leavetype.destroy', $lt))
			->assertRedirect(route('leavetype.index'))
			->assertSessionHas('success', __('LeaveType successfully deleted.'));

		$this->assertDatabaseMissing('leave_types', ['id' => $lt->id]);
	}
}
