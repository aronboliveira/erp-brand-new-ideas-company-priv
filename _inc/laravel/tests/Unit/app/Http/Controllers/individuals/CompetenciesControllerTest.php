<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Competencies;
use App\Models\PerformanceType;
use Spatie\Permission\Models\Permission;

class CompetenciesControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		// create necessary permissions
		Permission::create(['name' => 'Manage Competencies']);
		Permission::create(['name' => 'Create Competencies']);
		Permission::create(['name' => 'Edit Competencies']);
		Permission::create(['name' => 'Delete Competencies']);
	}

	/**
	 ** @test
	 **
	 ** Users without Manage Competencies permission are redirected from index.
	 **/
	public function index_redirects_without_manage_permission()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'index']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with Manage Competencies permission see the index view.
	 **/
	public function index_shows_view_with_competencies()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Manage Competencies');

		Competencies::create([
			'name'       => 'Skill A',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'index']));

		$response->assertStatus(200);
		$response->assertViewIs('competencies.index');
		$response->assertViewHas('competencies', function ($c) {
			return $c->first()->name === 'Skill A';
		});
	}

	/**
	 ** @test
	 **
	 ** Users without Create Competencies permission are redirected from create.
	 **/
	public function create_redirects_without_create_permission()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'create']));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with Create Competencies permission see the create form.
	 **/
	public function create_shows_form_with_performance_types()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Create Competencies');

		PerformanceType::create([
			'name'       => 'Type A',
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'create']));

		$response->assertStatus(200);
		$response->assertViewIs('competencies.create');
		$response->assertViewHas('performanceTypes');
	}

	/**
	 ** @test
	 **
	 ** Store redirects back on validation error.
	 **/
	public function store_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Create Competencies');

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\CompetenciesController::class, 'store']), [
			// missing 'name' and 'type'
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Store creates a competency and redirects on success.
	 **/
	public function store_creates_competency_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Create Competencies');

		PerformanceType::create([
			'name'       => 'Type B',
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->post(action([\App\Http\Controllers\CompetenciesController::class, 'store']), [
			'name' => 'Skill B',
			'type' => 1,
		]);

		$response->assertRedirect(route('competencies.index'));
		$this->assertDatabaseHas('competencies', [
			'name'       => 'Skill B',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show always redirects to the index route.
	 **/
	public function show_redirects_to_index()
	{
		$competency = Competencies::factory()->create();
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'show'], $competency));
		$response->assertRedirect(route('competencies.index'));
	}

	/**
	 ** @test
	 **
	 ** Users without Edit permission are redirected from edit.
	 **/
	public function edit_redirects_without_edit_permission()
	{
		$user = User::factory()->create();
		$competency = Competencies::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'edit'], $competency));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with Edit permission but not owner are denied edit.
	 **/
	public function edit_redirects_non_owner()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$other->givePermissionTo('Edit Competencies');

		$competency = Competencies::factory()->create(['created_by' => $owner->creatorId()]);

		$this->actingAs($other);
		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'edit'], $competency));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Owners with Edit permission see the edit form.
	 **/
	public function edit_shows_form_for_owner()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Edit Competencies');

		PerformanceType::create([
			'name'       => 'Type C',
			'created_by' => $user?->creatorId(),
		]);

		$competency = Competencies::create([
			'name'       => 'Skill C',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->get(action([\App\Http\Controllers\CompetenciesController::class, 'edit'], $competency));

		$response->assertStatus(200);
		$response->assertViewIs('competencies.edit');
		$response->assertViewHasAll(['competency', 'performanceTypes']);
	}

	/**
	 ** @test
	 **
	 ** Update redirects back on validation error.
	 **/
	public function update_redirects_back_on_validation_error()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Edit Competencies');

		$competency = Competencies::create([
			'name'       => 'Skill D',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->put(action([\App\Http\Controllers\CompetenciesController::class, 'update'], $competency), [
			// missing fields
		]);

		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Update changes the competency and redirects on success.
	 **/
	public function update_updates_competency_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Edit Competencies');

		$competency = Competencies::create([
			'name'       => 'Skill E',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->put(action([\App\Http\Controllers\CompetenciesController::class, 'update'], $competency), [
			'name' => 'Skill E Updated',
			'type' => 1,
		]);

		$response->assertRedirect(route('competencies.index'));
		$this->assertDatabaseHas('competencies', [
			'id'   => $competency->id,
			'name' => 'Skill E Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** Users without Delete permission are redirected from destroy.
	 **/
	public function destroy_redirects_without_delete_permission()
	{
		$user = User::factory()->create();
		$competency = Competencies::factory()->create(['created_by' => $user?->creatorId()]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\CompetenciesController::class, 'destroy'], $competency));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with Delete permission but not owner are denied destroy.
	 **/
	public function destroy_redirects_non_owner()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$other->givePermissionTo('Delete Competencies');

		$competency = Competencies::factory()->create(['created_by' => $owner->creatorId()]);

		$this->actingAs($other);
		$response = $this->delete(action([\App\Http\Controllers\CompetenciesController::class, 'destroy'], $competency));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Owners with Delete permission can delete and are redirected.
	 **/
	public function destroy_deletes_competency_and_redirects()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('Delete Competencies');

		$competency = Competencies::create([
			'name'       => 'Skill F',
			'type'       => 1,
			'created_by' => $user?->creatorId(),
		]);

		$this->actingAs($user);
		$response = $this->delete(action([\App\Http\Controllers\CompetenciesController::class, 'destroy'], $competency));

		$response->assertRedirect(route('competencies.index'));
		$this->assertDatabaseMissing('competencies', ['id' => $competency->id]);
	}
}
