<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\TerminationType;
use Spatie\Permission\Models\Permission;

class TerminationTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Index should list all termination types for authorized user.
	 **/
	public function test_index_lists_all_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage termination type']);
		$user?->givePermissionTo('manage termination type');

		TerminationType::factory()->count(2)->create(['created_by' => $user?->creatorId()]);
		TerminationType::factory()->create(); // belongs to another user

		$response = $this->actingAs($user)->get(route('terminationtype.index'));

		$response->assertStatus(200)
			->assertViewIs('terminationtype.index')
			->assertViewHas('terminationTypes', function ($types) use ($user) {
				return $types->count() === 2
					&& $types->every(fn ($t) => $t->created_by === $user?->creatorId());
			});
	}

	/**
	 ** @test
	 **
	 ** Show should display detail for owner with permission.
	 **/
	public function test_show_displays_detail_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage termination type']);
		$user?->givePermissionTo('manage termination type');

		$type = TerminationType::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('terminationtype.show', $type));

		$response->assertStatus(200)
			->assertViewIs('terminationtype.show')
			->assertViewHas('terminationType', $type);
	}

	/**
	 ** @test
	 **
	 ** Show should deny access for non-owner.
	 **/
	public function test_show_denies_non_owner()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage termination type']);
		$user?->givePermissionTo('manage termination type');

		$other = TerminationType::factory()->create();
		$response = $this->actingAs($user)->get(route('terminationtype.show', $other));

		$response->assertRedirect(route('terminationtype.index'))
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Create should display form when user has permission.
	 **/
	public function test_create_displays_form_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination type']);
		$user?->givePermissionTo('create termination type');

		$response = $this->actingAs($user)->get(route('terminationtype.create'));

		$response->assertStatus(200)
			->assertViewIs('terminationtype.create');
	}

	/**
	 ** @test
	 **
	 ** Store should persist a new termination type and redirect.
	 **/
	public function test_store_creates_type_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination type']);
		$user?->givePermissionTo('create termination type');

		$response = $this->actingAs($user)->post(route('terminationtype.store'), [
			'name' => 'Resignation',
		]);

		$response->assertRedirect(route('terminationtype.index'))
			->assertSessionHas('success', __('Termination type successfully created.'));
		$this->assertDatabaseHas('termination_types', [
			'name'       => 'Resignation',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation with empty name.
	 **/
	public function test_store_fails_validation_with_empty_name()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create termination type']);
		$user?->givePermissionTo('create termination type');

		$response = $this->actingAs($user)->post(route('terminationtype.store'), [
			'name' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseCount('termination_types', 0);
	}

	/**
	 ** @test
	 **
	 ** Edit should display form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination type']);
		$user?->givePermissionTo('edit termination type');

		$type = TerminationType::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('terminationtype.edit', $type));

		$response->assertStatus(200)
			->assertViewIs('terminationtype.edit')
			->assertViewHas('terminationType', $type);
	}

	/**
	 ** @test
	 **
	 ** Update should modify the termination type and redirect.
	 **/
	public function test_update_modifies_type_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination type']);
		$user?->givePermissionTo('edit termination type');

		$type = TerminationType::factory()->create([
			'name'       => 'Old',
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->put(route('terminationtype.update', $type), [
			'name' => 'Updated',
		]);

		$response->assertRedirect(route('terminationtype.index'))
			->assertSessionHas('success', __('Termination type successfully updated.'));
		$this->assertDatabaseHas('termination_types', [
			'id'   => $type->id,
			'name' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** Update should fail validation with empty name.
	 **/
	public function test_update_fails_validation_with_empty_name()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit termination type']);
		$user?->givePermissionTo('edit termination type');

		$type = TerminationType::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->put(route('terminationtype.update', $type), [
			'name' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseHas('termination_types', ['id' => $type->id, 'name' => $type->name]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the termination type and redirect.
	 **/
	public function test_destroy_deletes_type_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete termination type']);
		$user?->givePermissionTo('delete termination type');

		$type = TerminationType::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->delete(route('terminationtype.destroy', $type));

		$response->assertRedirect(route('terminationtype.index'))
			->assertSessionHas('success', __('Termination type successfully deleted.'));
		$this->assertModelMissing($type);
	}
}
