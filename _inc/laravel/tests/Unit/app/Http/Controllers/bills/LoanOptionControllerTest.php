<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, LoanOption};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoanOptionControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
	}

	/**
	 ** @test
	 **
	 ** The index route should display all loan options for the authenticated user.
	 **/
	public function test_index_displays_loan_options(): void
	{
		LoanOption::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('loanoption.index'));

		$response
			->assertStatus(200)
			->assertViewIs('loanoption.index');
		$response->assertViewHas('loanOptions');
	}

	/**
	 ** @test
	 **
	 ** The create route should render the form for adding a new loan option.
	 **/
	public function test_create_renders_form(): void
	{
		$response = $this->get(route('loanoption.create'));

		$response
			->assertStatus(200)
			->assertViewIs('loanoption.create');
	}

	/**
	 ** @test
	 **
	 ** Posting valid data to store should create a new loan option and redirect to index.
	 **/
	public function test_store_creates_loan_option(): void
	{
		$response = $this->post(route('loanoption.store'), [
			'name' => 'Housing Support'
		]);

		$response->assertRedirect(route('loanoption.index'));
		$this->assertDatabaseHas('loan_options', [
			'name'       => 'Housing Support',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** The edit route should display the form for editing an existing loan option.
	 **/
	public function test_edit_renders_form(): void
	{
		$option = LoanOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('loanoption.edit', $option));

		$response
			->assertStatus(200)
			->assertViewIs('loanoption.edit');
	}

	/**
	 ** @test
	 **
	 ** Sending valid update data should modify the loan option and redirect to index.
	 **/
	public function test_update_modifies_loan_option(): void
	{
		$option = LoanOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->put(route('loanoption.update', $option), [
			'name' => 'Updated Option'
		]);

		$response->assertRedirect(route('loanoption.index'));
		$this->assertDatabaseHas('loan_options', [
			'id'   => $option->id,
			'name' => 'Updated Option',
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy route should delete the specified loan option and redirect to index.
	 **/
	public function test_destroy_deletes_loan_option(): void
	{
		$option = LoanOption::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('loanoption.destroy', $option));

		$response->assertRedirect(route('loanoption.index'));
		$this->assertDatabaseMissing('loan_options', ['id' => $option->id]);
	}
}
