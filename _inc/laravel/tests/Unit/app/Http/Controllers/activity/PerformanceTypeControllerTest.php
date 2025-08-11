<?php

namespace Tests\Feature;

use App\Models\{User, PerformanceType};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PerformanceTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $companyUser;
	protected int $ownerId;

	public function setUp(): void
	{
		parent::setUp();

		// Create a “company” user and authenticate
		$this->companyUser = User::factory()->create([
			'type' => 'company',
		]);
		$this->actingAs($this->companyUser);

		$this->ownerId = $this->companyUser->creatorId();

		// Stub permissions
		Gate::define('manage performance type', fn () => true);
		Gate::define('create performance type', fn () => true);
		Gate::define('edit performance type', fn () => true);
		Gate::define('delete performance type', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Deny access to index if user is not a company or lacks 'manage performance type' permission.
	 **/
	public function test_index_requires_manage_permission_and_company_type()
	{
		// non-company type
		$emp = User::factory()->create(['type' => 'Employee']);
		$this->actingAs($emp);
		Gate::define('manage performance type', fn () => true);
		$this->get(route('performanceType.index'))
			->assertStatus(403);

		// company without permission
		$this->actingAs($this->companyUser);
		Gate::define('manage performance type', fn () => false);
		$this->get(route('performanceType.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Show all performance types for a company with 'manage' permission.
	 **/
	public function test_index_shows_all_types()
	{
		PerformanceType::factory()->count(3)->create([
			'created_by' => $this->ownerId,
		]);

		$response = $this->get(route('performanceType.index'));

		$response->assertStatus(200)
			->assertViewIs('performanceType.index')
			->assertViewHas('types', function ($types) {
				return $types->count() === 3;
			});
	}

	/**
	 ** @test
	 **
	 ** Deny access to create form if not a company or lacks 'create' permission.
	 **/
	public function test_create_requires_create_permission_and_company_type()
	{
		// non-company
		$emp = User::factory()->create(['type' => 'Employee']);
		$this->actingAs($emp);
		Gate::define('create performance type', fn () => true);
		$this->get(route('performanceType.create'))
			->assertStatus(403);

		// company no perm
		$this->actingAs($this->companyUser);
		Gate::define('create performance type', fn () => false);
		$this->get(route('performanceType.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Display the creation form for performance types to authorized company users.
	 **/
	public function test_create_displays_form()
	{
		$response = $this->get(route('performanceType.create'));
		$response->assertStatus(200)
			->assertViewIs('performanceType.create');
	}

	/**
	 ** @test
	 **
	 ** Validate input and create a new performance type, or return error on failure.
	 **/
	public function test_store_validation_and_success()
	{
		// validation fail
		$this->post(route('performanceType.store'), [])
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$this->post(route('performanceType.store'), ['name' => 'Quality'])
			->assertRedirect(route('performanceType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('performance_types', [
			'name'       => 'Quality',
			'created_by' => $this->ownerId,
		]);
	}

	/**
	 ** @test
	 **
	 ** Redirect show requests back to the index.
	 **/
	public function test_show_redirects_to_index()
	{
		$type = PerformanceType::factory()->create(['created_by' => $this->ownerId]);
		$this->get(route('performanceType.show', $type))
			->assertRedirect(route('performanceType.index'));
	}

	/**
	 ** @test
	 **
	 ** Deny or allow access to edit form based on company type, 'edit' permission, and ownership.
	 **/
	public function test_edit_requires_edit_permission_and_owner()
	{
		$type = PerformanceType::factory()->create(['created_by' => $this->ownerId]);

		// non-company
		$emp = User::factory()->create(['type' => 'Employee']);
		$this->actingAs($emp);
		Gate::define('edit performance type', fn () => true);
		$this->get(route('performanceType.edit', $type))
			->assertStatus(403);

		// company no perm
		$this->actingAs($this->companyUser);
		Gate::define('edit performance type', fn () => false);
		$this->get(route('performanceType.edit', $type))
			->assertStatus(403);

		// wrong owner
		Gate::define('edit performance type', fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);
		$this->get(route('performanceType.edit', $type))
			->assertStatus(403);

		// success
		$this->actingAs($this->companyUser);
		$response = $this->get(route('performanceType.edit', $type));
		$response->assertStatus(200)
			->assertViewIs('performanceType.edit')
			->assertViewHas('performanceType', fn ($v) => $v->id === $type->id);
	}

	/**
	 ** @test
	 **
	 ** Validate update input and persist changes, or return error.
	 **/
	public function test_update_validation_and_success()
	{
		$type = PerformanceType::factory()->create([
			'name'       => 'Old',
			'created_by' => $this->ownerId,
		]);

		// invalid
		$this->put(route('performanceType.update', $type), ['name' => ''])
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$this->put(route('performanceType.update', $type), ['name' => 'New'])
			->assertRedirect(route('performanceType.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('performance_types', [
			'id'   => $type->id,
			'name' => 'New',
		]);
	}

	/**
	 ** @test
	 **
	 ** Deny or allow deletion based on company type, 'delete' permission, and ownership.
	 **/
	public function test_destroy_requires_delete_permission_and_owner()
	{
		$type = PerformanceType::factory()->create(['created_by' => $this->ownerId]);

		// non-company
		$emp = User::factory()->create(['type' => 'Employee']);
		$this->actingAs($emp);
		Gate::define('delete performance type', fn () => true);
		$this->delete(route('performanceType.destroy', $type))
			->assertStatus(403);

		// company no perm
		$this->actingAs($this->companyUser);
		Gate::define('delete performance type', fn () => false);
		$this->delete(route('performanceType.destroy', $type))
			->assertStatus(403);

		// wrong owner
		Gate::define('delete performance type', fn () => true);
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);
		$this->delete(route('performanceType.destroy', $type))
			->assertStatus(403);

		// success
		$this->actingAs($this->companyUser);
		$this->delete(route('performanceType.destroy', $type))
			->assertRedirect(route('performanceType.index'))
			->assertSessionHas('success');
		$this->assertDatabaseMissing('performance_types', ['id' => $type->id]);
	}
}
