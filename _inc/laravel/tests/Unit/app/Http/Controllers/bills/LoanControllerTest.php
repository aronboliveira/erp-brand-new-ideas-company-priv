<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, Employee, Loan, LoanOption};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoanControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected Employee $employee;
	protected LoanOption $loanOption;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		$this->employee  = Employee::factory()->create(['created_by' => $this->user->creatorId()]);
		$this->loanOption = LoanOption::factory()->create(['created_by' => $this->user->creatorId()]);
	}

	/**
	 ** @test
	 **
	 ** The create method should render the loan creation form
	 ** when given a valid employee ID.
	 **/
	public function test_loan_create_form_renders(): void
	{
		$response = $this->get(route('loan.create', $this->employee->id));
		$response->assertStatus(200)->assertViewIs('loan.create');
	}

	/**
	 ** @test
	 **
	 ** The store method should persist a new loan record
	 ** with the provided data and redirect the user.
	 **/
	public function test_store_creates_loan(): void
	{
		$response = $this->post(route('loan.store'), [
			'employee_id' => $this->employee->id,
			'loan_option' => $this->loanOption->id,
			'title'       => 'Laptop Installment',
			'amount'      => 2000,
			'reason'      => 'Equipment purchase',
			'type'        => 'monthly',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('loans', [
			'employee_id' => $this->employee->id,
			'amount'      => 2000,
			'title'       => 'Laptop Installment',
		]);
	}

	/**
	 ** @test
	 **
	 ** The edit method should render the loan edit form
	 ** for a loan belonging to the authenticated user.
	 **/
	public function test_edit_form_renders(): void
	{
		$loan = Loan::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->get(route('loan.edit', $loan->id));
		$response->assertStatus(200)->assertViewIs('loan.edit');
	}

	/**
	 ** @test
	 **
	 ** The update method should apply changes to an existing loan
	 ** and redirect the user back.
	 **/
	public function test_update_modifies_loan(): void
	{
		$loan = Loan::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->put(route('loan.update', $loan), [
			'loan_option' => $this->loanOption->id,
			'title'       => 'Updated Title',
			'amount'      => 1500,
			'reason'      => 'New Reason',
			'type'        => 'monthly',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('loans', [
			'id'     => $loan->id,
			'title'  => 'Updated Title',
			'amount' => 1500,
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy method should delete the specified loan
	 ** and redirect the user back to the loan index.
	 **/
	public function test_destroy_deletes_loan(): void
	{
		$loan = Loan::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);

		$response = $this->delete(route('loan.destroy', $loan));
		$response->assertRedirect();
		$this->assertDatabaseMissing('loans', ['id' => $loan->id]);
	}
}
