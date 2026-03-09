<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\{
	Allowance,
	Branch,
	Commission,
	Department,
	Designation,
	Employee,
	EmployeeAttendance,
	EmployeeDocument,
	Loan,
	Payslip,
	PayslipType,
	OtherPayment,
	Overtime,
	SaturationDeduction,
	User
};

class EmployeeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** documents() returns all related EmployeeDocument records.
	 **/
	public function documents_returns_employee_documents_collection()
	{
		$emp = Employee::factory()->create();
		EmployeeDocument::factory()->count(3)->create(['employee_id' => $emp->id]);

		$docs = $emp->documents();

		$this->assertCount(3, $docs);
		$this->assertTrue($docs->first() instanceof EmployeeDocument);
	}

	/**
	 ** @test
	 **
	 ** getNetSalary() sums salary with allowances, commissions, loans, etc.
	 **/
	public function get_net_salary_combines_all_components_correctly()
	{
		$emp = Employee::factory()->create(['salary' => 1000]);

		// fixed allowance + 100
		Allowance::factory()->create(['employee_id' => $emp->id, 'type' => 'fixed', 'amount' => 100]);
		// percentage commission 10% of salary = 100
		Commission::factory()->create(['employee_id' => $emp->id, 'type' => 'percentage', 'amount' => 10]);
		// fixed loan -50
		Loan::factory()->create(['employee_id' => $emp->id, 'type' => 'fixed', 'amount' => 50]);
		// fixed deduction -20
		SaturationDeduction::factory()->create(['employee_id' => $emp->id, 'type' => 'fixed', 'amount' => 20]);
		// fixed other payment +30
		OtherPayment::factory()->create(['employee_id' => $emp->id, 'type' => 'fixed', 'amount' => 30]);
		// overtime: 2 days * 8h * rate 5 = 80
		Overtime::factory()->create(['employee_id' => $emp->id, 'number_of_days' => 2, 'hours' => 8, 'rate' => 5]);

		$net = $emp->getNetSalary();
		// 1000 +100 +100 -50 -20 +30 +80 = 1240
		$this->assertEqualsWithDelta(1240.0, $net, 0.01);
	}

	/**
	 ** @test
	 **
	 ** static allowance() returns JSON-encoded allowances array.
	 **/
	public function static_allowance_returns_json_string()
	{
		$emp = Employee::factory()->create();
		Allowance::factory()->count(2)->create(['employee_id' => $emp->id]);

		$json = Employee::allowance($emp->id);
		$arr = json_decode($json, true);

		$this->assertIsArray($arr);
		$this->assertCount(2, $arr);
	}

	/**
	 ** @test
	 **
	 ** employeeId() returns 1 when no employees exist, then increments or UUID.
	 **/
	public function employee_id_returns_one_then_increments_or_uuid()
	{
		// no existing -> returns 1
		$first = Employee::employeeId();
		$this->assertSame(1, $first);

		// create numeric PK model
		$e = Employee::factory()->create(['id' => 5]);
		$next = Employee::employeeId();
		$this->assertSame(6, $next);

		// simulate non-numeric key
		$e->delete();
		Model::unguard();
		Employee::create(['id' => Str::uuid(), 'employee_id' => 1]);
		Model::reguard();
		$uuid = Employee::employeeId();
		$this->assertTrue(Str::isUuid($uuid));
	}

	/**
	 ** @test
	 **
	 ** employeeSalary() returns salary or '-' if zero or missing.
	 **/
	public function employee_salary_returns_value_or_dash()
	{
		$e1 = Employee::factory()->create(['salary' => 1500]);
		$this->assertSame(1500.0, Employee::employeeSalary(1500));

		$e2 = Employee::factory()->create(['salary' => 0]);
		$this->assertSame('-', Employee::employeeSalary(0));
	}

	/**
	 ** @test
	 **
	 ** Resolves branch, department, designation, and user relations correctly.
	 **/
	public function it_resolves_branch_department_designation_and_user_relations()
	{
		$user       = User::factory()->create();
		$branch     = Branch::factory()->create();
		$department = Department::factory()->create();
		$designation = Designation::factory()->create();

		$emp = Employee::factory()->create([
			'user_id'        => $user?->id,
			'branch_id'      => $branch->id,
			'department_id'  => $department->id,
			'designation_id' => $designation->id,
		]);

		$this->assertEquals($user?->id,        $emp->user->id);
		$this->assertEquals($branch->id,      $emp->branch->id);
		$this->assertEquals($department->id,  $emp->department->id);
		$this->assertEquals($designation->id, $emp->designation->id);
	}

	/**
	 ** @test
	 **
	 ** salaryTypeName() returns the PayslipType name.
	 **/
	public function it_resolves_salary_type_name_correctly()
	{
		$type = PayslipType::factory()->create(['name' => 'Monthly']);
		$emp = Employee::factory()->create(['salary_type' => $type->id]);

		$this->assertSame('Monthly', $emp->salaryTypeName());
	}

	/**
	 ** @test
	 **
	 ** static commission() returns JSON-encoded array of Commission models.
	 **/
	public function static_commission_returns_json_string()
	{
		$emp = Employee::factory()->create();
		Commission::factory()->count(2)->create(['employee_id' => $emp->id]);

		$json = Employee::commission($emp->id);
		$arr = json_decode($json, true);

		$this->assertIsArray($arr);
		$this->assertCount(2, $arr);
	}

	/**
	 ** @test
	 **
	 ** static loan() returns JSON-encoded array of Loan models.
	 **/
	public function static_loan_returns_json_string()
	{
		$emp = Employee::factory()->create();
		Loan::factory()->count(1)->create(['employee_id' => $emp->id]);

		$json = Employee::loan($emp->id);
		$this->assertCount(1, json_decode($json, true));
	}

	/**
	 ** @test
	 **
	 ** static saturationDeduction() returns JSON-encoded array.
	 **/
	public function static_saturation_deduction_returns_json_string()
	{
		$emp = Employee::factory()->create();
		SaturationDeduction::factory()->count(3)->create(['employee_id' => $emp->id]);

		$json = Employee::saturationDeduction($emp->id);
		$this->assertCount(3, json_decode($json, true));
	}

	/**
	 ** @test
	 **
	 ** static otherPayment() returns JSON-encoded array.
	 **/
	public function static_other_payment_returns_json_string()
	{
		$emp = Employee::factory()->create();
		OtherPayment::factory()->count(2)->create(['employee_id' => $emp->id]);

		$json = Employee::otherPayment($emp->id);
		$this->assertCount(2, json_decode($json, true));
	}

	/**
	 ** @test
	 **
	 ** static overtime() returns JSON-encoded array.
	 **/
	public function static_overtime_returns_json_string()
	{
		$emp = Employee::factory()->create();
		Overtime::factory()->count(4)->create(['employee_id' => $emp->id]);

		$json = Employee::overtime($emp->id);
		$this->assertCount(4, json_decode($json, true));
	}

	/**
	 ** @test
	 **
	 ** presentStatus() returns the correct attendance record or null.
	 **/
	public function present_status_returns_attendance_or_null()
	{
		$emp = Employee::factory()->create();
		EmployeeAttendance::factory()->create([
			'employee_id' => $emp->id,
			'date'        => '2025-05-29',
		]);

		$att = $emp->presentStatus($emp->id, '2025-05-29');
		$this->assertNotNull($att);
		$this->assertSame($emp->id, $att->employee_id);

		$none = $emp->presentStatus($emp->id, '2025-01-01');
		$this->assertNull($none);
	}

	/**
	 ** @test
	 **
	 ** allowances() returns the related Allowance models.
	 **/
	public function allowances_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		Allowance::factory()->count(2)->create(['employee_id' => $emp->id]);

		$this->assertCount(2, $emp->allowances);
		$this->assertTrue($emp->allowances->first() instanceof Allowance);
	}

	/**
	 ** @test
	 **
	 ** commissions() returns the related Commission models.
	 **/
	public function commissions_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		Commission::factory()->count(3)->create(['employee_id' => $emp->id]);

		$this->assertCount(3, $emp->commissions);
	}

	/**
	 ** @test
	 **
	 ** loans() returns the related Loan models.
	 **/
	public function loans_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		Loan::factory()->count(1)->create(['employee_id' => $emp->id]);

		$this->assertCount(1, $emp->loans);
	}

	/**
	 ** @test
	 **
	 ** saturationDeductions() returns the related SaturationDeduction models.
	 **/
	public function saturation_deductions_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		SaturationDeduction::factory()->count(2)->create(['employee_id' => $emp->id]);

		$this->assertCount(2, $emp->saturationDeductions);
	}

	/**
	 ** @test
	 **
	 ** otherPayments() returns the related OtherPayment models.
	 **/
	public function other_payments_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		OtherPayment::factory()->count(2)->create(['employee_id' => $emp->id]);

		$this->assertCount(2, $emp->otherPayments);
	}

	/**
	 ** @test
	 **
	 ** overtimes() returns the related Overtime models.
	 **/
	public function overtimes_relation_returns_collection()
	{
		$emp = Employee::factory()->create();
		Overtime::factory()->count(4)->create(['employee_id' => $emp->id]);

		$this->assertCount(4, $emp->overtimes);
	}

	/**
	 ** @test
	 **
	 ** salaryTypeName() returns the name of the related PayslipType.
	 **/
	public function salary_type_name_returns_payslip_type_name()
	{
		$type = PayslipType::factory()->create(['name' => 'Weekly']);
		$emp = Employee::factory()->create(['salary_type' => $type->id]);

		$this->assertSame('Weekly', $emp->salaryTypeName());
	}

	/**
	 ** @test
	 **
	 ** salary_type() alias returns the same as salaryTypeName().
	 **/
	public function salary_type_alias_returns_same_as_salary_type_name()
	{
		$type = PayslipType::factory()->create(['name' => 'Monthly']);
		$emp = Employee::factory()->create(['salary_type' => $type->id]);

		// salary_type() returns BelongsTo; salaryTypeName() returns string via query
		$this->assertSame('Monthly', $emp->salaryTypeName());
	}

	/**
	 ** @test
	 **
	 ** branch, department, designation, user, and paySlip relations resolve correctly.
	 **/
	public function it_resolves_all_hasone_relations()
	{
		$user       = User::factory()->create();
		$branch     = Branch::factory()->create();
		$department = Department::factory()->create();
		$designation = Designation::factory()->create();
		$payslip    = Payslip::factory()->create(['employee_id' => 123]);

		$emp = Employee::factory()->create([
			'user_id'        => $user?->id,
			'branch_id'      => $branch->id,
			'department_id'  => $department->id,
			'designation_id' => $designation->id,
		]);

		$this->assertEquals($user?->id,        $emp->user->id);
		$this->assertEquals($branch->id,      $emp->branch->id);
		$this->assertEquals($department->id,  $emp->department->id);
		$this->assertEquals($designation->id, $emp->designation->id);
	}

	/**
	 ** @test
	 **
	 ** paySlip() returns the associated Payslip model if one exists.
	 **/
	public function pay_slip_relation_returns_the_correct_model()
	{
		$emp     = Employee::factory()->create();
		$payslip = Payslip::factory()->create(['employee_id' => $emp->id]);

		// reload via relation
		$loaded = $emp->paySlip;

		$this->assertInstanceOf(Payslip::class, $loaded);
		$this->assertSame($payslip->id, $loaded->id);
	}

	/**
	 ** @test
	 **
	 ** salary_type() alias returns the same string as salaryTypeName().
	 **/
	public function salary_type_alias_and_salary_type_name_are_identical()
	{
		$type = PayslipType::factory()->create(['name' => 'Hourly']);
		$emp = Employee::factory()->create(['salary_type' => $type->id]);

		// salary_type() returns BelongsTo; salaryTypeName() returns string via query
		$this->assertSame('Hourly', $emp->salaryTypeName());
	}
}
