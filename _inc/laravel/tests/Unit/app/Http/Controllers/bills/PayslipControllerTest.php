<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, Employee, Payslip};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

final class PayslipControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private Employee $employee;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create(['type' => 'Company']);
		$this->actingAs($this->user);
		$this->employee = Employee::factory()->create([
			'created_by'   => $this->user->creatorId(),
			'salary'       => 1000,
			'company_doj'  => now()->subMonths(6)->startOfMonth(),
		]);
	}

	/**
	 ** @test
	 **
	 ** The index route should render the payslip listing view.
	 **/
	public function test_index_renders_view(): void
	{
		$response = $this->get(route('payslip.index'));
		$response
			->assertStatus(200)
			->assertViewIs('payslip.index');
	}

	/**
	 ** @test
	 **
	 ** Storing for the current month/year should generate payslip records for employees.
	 **/
	public function test_store_creates_payslips(): void
	{
		$response = $this->post(route('payslip.store'), [
			'month' => now()->format('m'),
			'year'  => now()->format('Y'),
		]);

		$response->assertRedirect(route('payslip.index'));
		$this->assertDatabaseHas('payslips', [
			'employee_id' => $this->employee->id,
			'created_by'  => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Deleting a payslip should remove it from the database.
	 **/
	public function test_destroy_deletes_payslip(): void
	{
		$payslip = Payslip::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->delete(route('payslip.destroy', $payslip->id));

		$response->assertSuccessful();
		$this->assertDatabaseMissing('payslips', ['id' => $payslip->id]);
	}

	/**
	 ** @test
	 **
	 ** Showing a specific payslip should render the detail view.
	 **/
	public function test_show_employee_payslip(): void
	{
		$payslip = Payslip::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->get(route('payslip.show', $payslip->id));

		$response
			->assertStatus(200)
			->assertViewIs('payslip.show');
	}

	/**
	 ** @test
	 **
	 ** Searching payslips via JSON should return an array structure.
	 **/
	public function test_searchJson_returns_expected_structure(): void
	{
		Payslip::factory()->create([
			'created_by'    => $this->user->creatorId(),
			'salary_month'  => now()->format('Y-m'),
		]);

		$response = $this->postJson(route('payslip.search.json'), [
			'datePicker' => now()->format('Y-m'),
		]);

		$response
			->assertStatus(200)
			->assertJsonIsArray();
	}

	/**
	 ** @test
	 **
	 ** Paying salary for an employee and month should mark payslip as paid.
	 **/
	public function test_pay_salary_sets_status_paid(): void
	{
		$month = now()->format('Y-m');
		$payslip = Payslip::factory()->create([
			'created_by'   => $this->user->creatorId(),
			'employee_id'  => $this->employee->id,
			'salary_month' => $month,
			'status'       => 0,
		]);

		$response = $this->get(route('payslip.paySalary', [
			'id'   => $this->employee->id,
			'date' => $month,
		]));

		$response->assertRedirect(route('payslip.index'));
		$this->assertDatabaseHas('payslips', [
			'id'     => $payslip->id,
			'status' => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** The bulk-pay creation view should list unpaid payslips for the month.
	 **/
	public function test_bulk_pay_create_renders_view(): void
	{
		$date = now()->format('Y-m');
		Payslip::factory()->create([
			'created_by'   => $this->user->creatorId(),
			'salary_month' => $date,
		]);

		$response = $this->get(route('payslip.bulk.pay.create', $date));
		$response
			->assertStatus(200)
			->assertViewIs('payslip.bulkcreate');
	}

	/**
	 ** @test
	 **
	 ** Submitting bulk payment should mark all unpaid payslips as paid.
	 **/
	public function test_bulk_payment_updates_unpaid(): void
	{
		$date = now()->format('Y-m');
		$payslip = Payslip::factory()->create([
			'created_by'   => $this->user->creatorId(),
			'salary_month' => $date,
			'status'       => 0,
		]);

		$response = $this->post(route('payslip.bulk.payment', $date));
		$response->assertRedirect(route('payslip.index'));

		$this->assertDatabaseHas('payslips', [
			'id'     => $payslip->id,
			'status' => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** The employee-specific payslip listing should render correctly.
	 **/
	public function test_employee_payslip_renders_view(): void
	{
		Payslip::factory()->create([
			'created_by'  => $this->user->creatorId(),
			'employee_id' => $this->employee->id,
		]);

		$response = $this->get(route('payslip.employeePayslip'));
		$response
			->assertStatus(200)
			->assertViewIs('payslip.employeepayslip');
	}

	/**
	 ** @test
	 **
	 ** Rendering the payslip PDF view for an employee and month should succeed.
	 **/
	public function test_pdf_renders_pdf_view(): void
	{
		$month = now()->format('Y-m');
		Payslip::factory()->create([
			'created_by'   => $this->user->creatorId(),
			'salary_month' => $month,
			'employee_id'  => $this->employee->id,
		]);

		$response = $this->get(route('payslip.pdf', [
			'id'    => $this->employee->id,
			'month' => $month,
		]));

		$response
			->assertStatus(200)
			->assertViewIs('payslip.pdf');
	}

	/**
	 ** @test
	 **
	 ** The salary edit form for an individual payslip should render correctly.
	 **/
	public function test_edit_employee_salary_renders_view(): void
	{
		$payslip = Payslip::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('payslip.editEmployee', $payslip->id));
		$response
			->assertStatus(200)
			->assertViewIs('payslip.salaryEdit');
	}

	/**
	 ** @test
	 **
	 ** Exporting payslips should return an Excel download response.
	 **/
	public function test_export_returns_excel_download(): void
	{
		$response = $this->get(route('payslip.export'));

		$response
			->assertStatus(200)
			->assertHeader('content-disposition');
	}
}
