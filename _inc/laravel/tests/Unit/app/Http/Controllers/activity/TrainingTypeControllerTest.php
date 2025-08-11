<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\TrainingType;
use App\Http\Controllers\TrainingTypeController;

class TrainingTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Unauthorized users are redirected to login when accessing the index action.
	 **/
	public function unauthorized_user_cannot_access_index()
	{
		$response = $this->get(action([TrainingTypeController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Authorized users see the training types list on the index page.
	 **/
	public function authorized_user_can_view_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		TrainingType::create([
			'name'       => 'Test Type',
			'created_by' => $user?->id,
		]);

		$response = $this->get(action([TrainingTypeController::class, 'index']));

		$response->assertStatus(200);
		$response->assertViewIs('trainingtype.index');
		$response->assertViewHas('trainingtypes', function ($types) use ($user) {
			return $types->first()->name === 'Test Type'
				&& $types->first()->created_by === $user?->id;
		});
	}

	/**
	 ** @test
	 **
	 ** Authorized users can create a new training type via the store action.
	 **/
	public function store_creates_training_type_and_redirects()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->post(action([TrainingTypeController::class, 'store']), [
			'name' => 'New Type',
		]);

		$response->assertRedirect(route('trainingtype.index'));
		$this->assertDatabaseHas('training_types', [
			'name'       => 'New Type',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** The show action always redirects back to the index route.
	 **/
	public function show_redirects_to_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$tt = TrainingType::create([
			'name'       => 'Show Type',
			'created_by' => $user?->id,
		]);

		$response = $this->get(action([TrainingTypeController::class, 'show'], $tt));
		$response->assertRedirect(route('trainingtype.index'));
	}

	/**
	 ** @test
	 **
	 ** Owners can access the edit form for their own training types.
	 **/
	public function owner_can_access_edit_form()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$tt = TrainingType::create([
			'name'       => 'Edit Type',
			'created_by' => $user?->id,
		]);

		$response = $this->get(action([TrainingTypeController::class, 'edit'], $tt));

		$response->assertStatus(200);
		$response->assertViewIs('trainingtype.edit');
		$response->assertViewHas('trainingType', $tt);
	}

	/**
	 ** @test
	 **
	 ** Non-owners are denied access to the edit form and redirected.
	 **/
	public function non_owner_cannot_access_edit_form()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$this->actingAs($other);

		$tt = TrainingType::create([
			'name'       => 'Edit Type',
			'created_by' => $owner->id,
		]);

		$response = $this->get(action([TrainingTypeController::class, 'edit'], $tt));
		$response->assertRedirect(route('trainingtype.index'));
	}

	/**
	 ** @test
	 **
	 ** Owners can update their own training types via the update action.
	 **/
	public function owner_can_update_training_type()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$tt = TrainingType::create([
			'name'       => 'Old Name',
			'created_by' => $user?->id,
		]);

		$response = $this->put(action([TrainingTypeController::class, 'update'], $tt), [
			'name' => 'Updated Name',
		]);

		$response->assertRedirect(route('trainingtype.index'));
		$this->assertDatabaseHas('training_types', [
			'id'   => $tt->id,
			'name' => 'Updated Name',
		]);
	}

	/**
	 ** @test
	 **
	 ** Owners can delete their own training types via the destroy action.
	 **/
	public function owner_can_delete_training_type()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$tt = TrainingType::create([
			'name'       => 'Delete Me',
			'created_by' => $user?->id,
		]);

		$response = $this->delete(action([TrainingTypeController::class, 'destroy'], $tt));

		$this->assertDatabaseMissing('training_types', [
			'id' => $tt->id,
		]);
		$response->assertRedirect(route('trainingtype.index'));
	}
}
