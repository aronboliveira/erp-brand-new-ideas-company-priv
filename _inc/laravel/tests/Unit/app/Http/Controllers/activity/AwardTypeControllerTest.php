<?php

namespace Tests\Feature\Controllers;

use App\Models\{AwardType, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AwardTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// Seed a logged-in user
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		// Default all permissions to true
		Gate::define('manage award type', fn () => true);
		Gate::define('create award type', fn () => true);
		Gate::define('edit award type', fn () => true);
		Gate::define('delete award type', fn () => true);
	}

	/**
	 ** @test
	 **
	 ** The index route should render the award type index view
	 ** and supply the created award types to the view.
	 **/
	public function test_index_renders_award_type_index(): void
	{
		AwardType::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->get(route('award_type.index'));

		$response->assertOk();
		$response->assertViewHas('awardTypes');
	}

	/**
	 ** @test
	 **
	 ** The create route should display the form for creating a new award type.
	 **/
	public function test_create_renders_form(): void
	{
		$response = $this->get(route('award_type.create'));

		$response->assertOk();
		$response->assertViewIs('award_type.create');
	}

	/**
	 ** @test
	 **
	 ** Posting valid data to store should create a new award type
	 ** and redirect back to the index.
	 **/
	public function test_store_creates_award_type(): void
	{
		$payload = ['name' => 'Top Performer'];
		$response = $this->post(route('award_type.store'), $payload);

		$response->assertRedirect(route('award_type.index'));
		$this->assertDatabaseHas('award_types', [
			'name'       => 'Top Performer',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** A user without ownership should receive a 403 when accessing edit.
	 **/
	public function test_edit_requires_ownership(): void
	{
		$type = AwardType::factory()->create(); // Different user
		$response = $this->get(route('award_type.edit', $type));

		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** The edit route should show the edit form to the owning user.
	 **/
	public function test_edit_renders_edit_view_for_owner(): void
	{
		$type = AwardType::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->get(route('award_type.edit', $type));

		$response->assertOk();
		$response->assertViewIs('award_type.edit');
	}

	/**
	 ** @test
	 **
	 ** A valid update request from the owner should change the award type
	 ** and redirect to the index.
	 **/
	public function test_update_succeeds_for_owner(): void
	{
		$type = AwardType::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->put(route('award_type.update', $type), [
			'name' => 'Updated Name'
		]);

		$response->assertRedirect(route('award_type.index'));
		$this->assertDatabaseHas('award_types', [
			'id'   => $type->id,
			'name' => 'Updated Name'
		]);
	}

	/**
	 ** @test
	 **
	 ** The owner can delete their award type and it should be removed
	 ** from the database.
	 **/
	public function test_destroy_deletes_for_owner(): void
	{
		$type = AwardType::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->delete(route('award_type.destroy', $type));

		$response->assertRedirect(route('award_type.index'));
		$this->assertDatabaseMissing('award_types', ['id' => $type->id]);
	}

	/**
	 ** @test
	 **
	 ** Submitting the store endpoint without a name should fail validation
	 ** and return errors in the session.
	 **/
	public function test_store_fails_validation(): void
	{
		$response = $this->post(route('award_type.store'), []);

		$response->assertSessionHasErrors(['name']);
	}

	/**
	 ** @test
	 **
	 ** Submitting the update endpoint with an empty name should fail validation
	 ** and return errors in the session.
	 **/
	public function test_update_fails_validation(): void
	{
		$type = AwardType::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->put(route('award_type.update', $type), ['name' => '']);

		$response->assertSessionHasErrors(['name']);
	}
}
