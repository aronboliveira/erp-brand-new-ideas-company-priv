<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class SourceControllerTest extends TestCase
{
	use RefreshDatabase;


	private User $company;
	private Source $source;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permission checks
		Gate::before(fn () => true);

		// Make creatorId() return the user's own ID
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

		// Create & authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// Seed a Source owned by that user
		$this->source = Source::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);
	}

	/**
	 ** @test
	 *
	 ** Should display the sources index page for an authorized user.
	 **/
	public function test_index_displays_sources_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage source']);
		$user?->givePermissionTo('manage source');

		Source::create(['name' => 'First', 'created_by' => $user?->creatorId()]);
		Source::create(['name' => 'Second', 'created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('sources.index'));

		$response->assertStatus(200);
		$response->assertViewIs('sources.index');
		$response->assertViewHas('sources', function ($sources) use ($user) {
			return $sources->count() === 2
				&& $sources->first()->created_by === $user?->creatorId();
		});
	}

	/**
	 ** @test
	 *
	 ** Should redirect back when listing sources without proper permission.
	 **/
	public function test_index_redirects_if_not_authorized()
	{
		$user = User::factory()->create();
		// no permission given

		$response = $this->actingAs($user)->get(route('sources.index'));

		$response->assertRedirect(route('sources.index'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 *
	 ** Should show the create form for an authorized user.
	 **/
	public function test_create_displays_form_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create source']);
		$user?->givePermissionTo('create source');

		$response = $this->actingAs($user)->get(route('sources.create'));

		$response->assertStatus(200);
		$response->assertViewIs('sources.create');
	}

	/**
	 ** @test
	 *
	 ** Should persist a new source and redirect on successful store.
	 **/
	public function test_store_persists_new_source_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create source']);
		$user?->givePermissionTo('create source');

		$response = $this->actingAs($user)->post(route('sources.store'), [
			'name' => 'New Source',
		]);

		$response->assertRedirect(route('sources.index'))
			->assertSessionHas('success', __('Source successfully created!'));
		$this->assertDatabaseHas('sources', [
			'name'       => 'New Source',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 *
	 ** Should fail validation when storing a source with empty name.
	 **/
	public function test_store_fails_validation_with_empty_name()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create source']);
		$user?->givePermissionTo('create source');

		$response = $this->actingAs($user)->post(route('sources.store'), [
			'name' => '',
		]);

		$response->assertRedirect(route('sources.index'))
			->assertSessionHas('error');
		$this->assertDatabaseCount('sources', 0);
	}

	/**
	 ** @test
	 *
	 ** Should show the edit form for the owner with proper permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit source']);
		$user?->givePermissionTo('edit source');

		$source = Source::create([
			'name'       => 'Old Name',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->get(route('sources.edit', $source));

		$response->assertStatus(200);
		$response->assertViewIs('sources.edit');
		$response->assertViewHas('source', $source);
	}

	/**
	 ** @test
	 *
	 ** Should update an existing source and redirect on success.
	 **/
	public function test_update_changes_source_name_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit source']);
		$user?->givePermissionTo('edit source');

		$source = Source::create([
			'name'       => 'Old Name',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->put(route('sources.update', $source), [
			'name' => 'Updated Name',
		]);

		$response->assertRedirect(route('sources.index'))
			->assertSessionHas('success', __('Source successfully updated!'));
		$this->assertDatabaseHas('sources', [
			'id'   => $source->id,
			'name' => 'Updated Name',
		]);
	}

	/**
	 ** @test
	 *
	 ** Should fail validation when updating a source with empty name.
	 **/
	public function test_update_fails_validation_with_empty_name()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit source']);
		$user?->givePermissionTo('edit source');

		$source = Source::create([
			'name'       => 'Keep Name',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->put(route('sources.update', $source), [
			'name' => '',
		]);

		$response->assertRedirect(route('sources.index'))
			->assertSessionHas('error');
		$this->assertDatabaseHas('sources', [
			'id'   => $source->id,
			'name' => 'Keep Name',
		]);
	}

	/**
	 ** @test
	 *
	 ** Should delete a source and redirect on success.
	 **/
	public function test_destroy_deletes_source_and_redirects_on_success()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete source']);
		$user?->givePermissionTo('delete source');

		$source = Source::create([
			'name'       => 'To Be Deleted',
			'created_by' => $user?->creatorId(),
		]);

		$this->assertDatabaseHas('sources', ['id' => $source->id]);

		$response = $this->actingAs($user)->delete(route('sources.destroy', $source));

		$response->assertRedirect(route('sources.index'))
			->assertSessionHas('success', __('Source successfully deleted!'));
		$this->assertDatabaseMissing('sources', ['id' => $source->id]);
	}

	/**
	 ** @test
	 **
	 ** A company user can view their own source and see the correct view.
	 **/
	public function owner_can_view_source()
	{
		$response = $this->get(route('sources.show', $this->source));

		$response->assertOk()
			->assertViewIs('sources.show')
			->assertViewHas('source', fn ($s) => $s->id === $this->source->id);
	}

	/**
	 ** @test
	 **
	 ** A user who does not own the source receives a 403 Forbidden.
	 **/
	public function non_owner_gets_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('sources.show', $this->source));

		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login.
	 **/
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$response = $this->get(route('sources.show', $this->source));

		$response->assertRedirect(); // defaults to the login page
	}

	/**
	 ** @test
	 **
	 ** Permission denied (Gate::before → false) returns a 403 status.
	 **/
	public function permission_denied_returns_403()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('sources.show', $this->source));

		$response->assertStatus(403);
	}
}
