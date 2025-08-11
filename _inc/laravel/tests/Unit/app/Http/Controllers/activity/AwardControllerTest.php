<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Award, AwardType, Employee, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Notification, Log, Storage};
use Tests\TestCase;

class AwardControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $admin;
	protected Employee $employee;
	protected AwardType $awardType;

	protected function setUp(): void
	{
		parent::setUp();

		$this->admin    = User::factory()->admin()->create();
		$this->employee = Employee::factory()->for($this->admin, 'createdBy')->create();
		$this->awardType = AwardType::factory()->for($this->admin, 'createdBy')->create();
	}

	/**
	 ** @test
	 **
	 ** Ensure that a user without 'manage award' permission is forbidden
	 ** when accessing the awards index.
	 **/
	public function test_index_requires_permission(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user)
			->get(route('award.index'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that an admin with correct permission can view the awards index
	 ** and sees the 'award.index' view rendered.
	 **/
	public function test_admin_can_see_award_index(): void
	{
		$this->actingAs($this->admin)
			->get(route('award.index'))
			->assertOk()
			->assertViewIs('award.index');
	}

	/**
	 ** @test
	 **
	 ** Ensure that accessing the award creation form without 'create award'
	 ** permission returns a 403 status.
	 **/
	public function test_create_requires_permission(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user)
			->get(route('award.create'))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that an admin with 'create award' permission can access
	 ** the award creation form and sees the 'award.create' view.
	 **/
	public function test_admin_can_view_create_form(): void
	{
		$this->actingAs($this->admin)
			->get(route('award.create'))
			->assertOk()
			->assertViewIs('award.create');
	}

	/**
	 ** @test
	 **
	 ** Ensure that submitting invalid data to the award store endpoint
	 ** triggers validation errors for required fields.
	 **/
	public function test_store_requires_valid_data(): void
	{
		$this->actingAs($this->admin)
			->post(route('award.store'), [])
			->assertSessionHasErrors(['employee_id', 'award_type', 'date', 'gift']);
	}

	/**
	 ** @test
	 **
	 ** Verify that an admin can successfully create an award with valid data,
	 ** which stores the award in the database and redirects to index.
	 **/
	public function test_admin_can_create_award(): void
	{
		$this->withoutExceptionHandling();
		$this->actingAs($this->admin);
		Notification::fake();

		$response = $this->post(route('award.store'), [
			'employee_id' => $this->employee->id,
			'award_type'  => $this->awardType->id,
			'date'        => now()->toDateString(),
			'gift'        => 'Gold Watch',
			'description' => 'Outstanding achievement',
		]);

		$response->assertRedirect(route('award.index'));
		$this->assertDatabaseHas('awards', [
			'employee_id' => $this->employee->id,
			'award_type'  => $this->awardType->id,
			'gift'        => 'Gold Watch',
		]);
	}

	/**
	 ** @test
	 **
	 ** Ensure that editing an award requires 'edit award' permission,
	 ** and returns 403 for unauthorized users.
	 **/
	public function test_edit_requires_permission(): void
	{
		$award = Award::factory()
			->for($this->employee)
			->for($this->awardType)
			->create();

		$user = User::factory()->create();
		$this->actingAs($user)
			->get(route('award.edit', $award))
			->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Verify that an admin can access the award edit form for awards
	 ** they own, and sees the 'award.edit' view.
	 **/
	public function test_admin_can_edit_award(): void
	{
		$award = Award::factory()
			->for($this->employee)
			->for($this->awardType)
			->create(['created_by' => $this->admin->creatorId()]);

		$this->actingAs($this->admin)
			->get(route('award.edit', $award))
			->assertOk()
			->assertViewIs('award.edit');
	}

	/**
	 ** @test
	 **
	 ** Ensure that an admin can update an existing award with valid data,
	 ** and that the database record is updated accordingly.
	 **/
	public function test_admin_can_update_award(): void
	{
		$award = Award::factory()
			->for($this->employee)
			->for($this->awardType)
			->create(['created_by' => $this->admin->creatorId()]);

		$this->actingAs($this->admin)
			->put(route('award.update', $award), [
				'employee_id' => $this->employee->id,
				'award_type'  => $this->awardType->id,
				'date'        => now()->toDateString(),
				'gift'        => 'Updated Gift',
				'description' => 'Updated description',
			])
			->assertRedirect(route('award.index'));

		$this->assertDatabaseHas('awards', [
			'id'   => $award->id,
			'gift' => 'Updated Gift',
		]);
	}

	/**
	 ** @test
	 **
	 ** Verify that an admin can delete an award they own, and that the
	 ** award record is removed from the database.
	 **/
	public function test_admin_can_delete_award(): void
	{
		$award = Award::factory()
			->for($this->employee)
			->for($this->awardType)
			->create(['created_by' => $this->admin->creatorId()]);

		$this->actingAs($this->admin)
			->delete(route('award.destroy', $award))
			->assertRedirect(route('award.index'));

		$this->assertDatabaseMissing('awards', ['id' => $award->id]);
	}

	/**
	 ** @test
	 **
	 ** Ensure that an admin cannot edit an award belonging to another user,
	 ** returning a 401 or appropriate error response.
	 **/
	public function test_cannot_edit_foreign_award(): void
	{
		$otherAdmin = User::factory()->admin()->create();
		$award = Award::factory()
			->for($this->employee)
			->for($this->awardType)
			->create(['created_by' => $otherAdmin->creatorId()]);

		$this->actingAs($this->admin)
			->get(route('award.edit', $award))
			->assertStatus(401);
	}
}
