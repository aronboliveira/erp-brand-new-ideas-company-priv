<?php

namespace Database\Factories;

use App\Models\{Employee, Payslip};
use Illuminate\Database\Eloquent\Factories\Factory;

class PayslipFactory extends Factory
{
	protected $model = Payslip::class;

	public function definition(): array
	{
		return [
			'employee_id'  => Employee::factory(),
			'net_payable'  => $this->faker->numberBetween(2000, 10000),
			'salary_month' => date('Y-m'),
			'pay_day'      => $this->faker->date(),
			'status'       => 0,
			'gross_salary' => $this->faker->randomFloat(2, 3000, 15000),
			'net_salary'   => $this->faker->randomFloat(2, 2000, 12000),
		];
	}
}
