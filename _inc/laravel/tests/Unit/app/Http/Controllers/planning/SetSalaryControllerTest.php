<?php

namespace Tests\Feature;

use App\Models\{
	Allowance,
	AllowanceOption,
	Commission,
	DeductionOption,
	Employee,
	Loan,
	LoanOption,
	Overtime,
	PayslipType,
	SaturationDeduction,
	SalaryType,
	OtherPayment,
	User
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Gate, Log};
use Tests\TestCase;

class SetSalaryControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employeeUser;
	private Employee $employee;
	private PayslipType $payslipType;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permissions
		Gate::before(fn () => true);

		// macro so creatorId() returns the user's own ID
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

		// spy on logs and stub transactions
		Log::spy();
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();

		// create a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// create an employee record under that company
		$this->employee = Employee::factory()->create([
			'created_by' => $this->company->creatorId(),
			'salary_type' => 'monthly',
			'salary'      => 1000,
		]);

		// create a payslip type
		$this->payslipType = PayslipType::factory()->create([
			'created_by' => $this->company->creatorId(),
			'name'       => 'Monthly',
		]);

		// also prepare an Employee user for the "Employee" type tests
		$this->employeeUser = User::factory()->create(['type' => 'Employee']);
		Employee::factory()->create([
			'user_id'    => $this->employeeUser->id,
			'created_by' => $this->company->creatorId(),
		]);
	}

	/** @test */
	public function index_displays_all_employees_with_salaryType()
	{
		Employee::factory()->count(2)->create([
			'created_by' => $this->company->creatorId(),
		]);

		$response = $this->get(route('setsalary.index'));

		$response->assertOk()
			->assertViewIs('setsalary.index')
			->assertViewHas('employees', fn ($emps) => $emps->first()->relationLoaded('salary_type'));
	}

	/** @test */
	public function edit_shows_edit_form_for_company_user()
	{
		$response = $this->get(route('setsalary.edit', $this->employee->id));

		$response->assertOk()
			->assertViewIs('setsalary.edit')
			->assertViewHasAll([
				'employee', 'payslipTypes', 'allowanceOptions',
				'loanOptions', 'deductionOptions', 'commissions',
				'saturationDeductions', 'otherPayments', 'overtimes',
				'allowances'
			]);
	}

	/** @test */
	public function edit_shows_employee_salary_view_for_employee_user()
	{
		$this->actingAs($this->employeeUser);

		$response = $this->get(route('setsalary.edit', $this->employee->id));

		$response->assertOk()
			->assertViewIs('setsalary.employee_salary');
	}

	/** @test */
	public function employee_update_salary_validates_and_saves()
	{
		$payload = [
			'salary_type' => $this->payslipType->id,
			'salary'      => 2000,
		];

		$response = $this->post(
			route('setsalary.employeeUpdateSalary', $this->employee->id),
			$payload
		);

		$response->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('employees', [
			'id'          => $this->employee->id,
			'salary'      => 2000,
			'salary_type' => (string)$this->payslipType->id,
		]);
	}

	/** @test */
	public function employee_update_salary_redirects_back_on_validation_failure()
	{
		$response = $this->post(
			route('setsalary.employeeUpdateSalary', $this->employee->id),
			['salary' => -100]
		);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/** @test */
	public function employee_salary_lists_only_own_record_for_employee_user()
	{
		$own = Employee::factory()->create([
			'user_id'    => $this->employeeUser->id,
			'created_by' => $this->company->creatorId(),
		]);

		Employee::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->employeeUser);

		$response = $this->get(route('setsalary.employeeSalary'));

		$response->assertOk()
			->assertViewIs('setsalary.index')
			->assertViewHas('employees', fn ($emps) => $emps->pluck('id')->all() === [$own->id]);
	}

	/** @test */
	public function employee_salary_redirects_company_user_to_index()
	{
		$response = $this->get(route('setsalary.employeeSalary'));

		$response->assertRedirect(route('setsalary.index'));
	}

	/** @test */
	public function employee_basic_salary_shows_basic_salary_form()
	{
		$response = $this->get(route('setsalary.employeeBasicSalary', $this->employee->id));

		$response->assertOk()
			->assertViewIs('setsalary.basic_salary')
			->assertViewHasAll(['employee', 'payslipTypes']);
	}

	/** @test */
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$resp = $this->get(route('setsalary.show', ['id' => 1]));
		$resp->assertRedirect();
	}

	/** @test */
	public function permission_denied_returns_403()
	{
		Gate::before(fn () => false);

		$resp = $this->actingAs($this->company)
			->get(route('setsalary.show', ['id' => $this->employee->id]));

		$resp->assertStatus(403);
	}
}
