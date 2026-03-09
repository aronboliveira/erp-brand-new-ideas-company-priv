<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
	protected $model = Employee::class;

	public function definition(): array
	{
		return [
			'name'        => $this->faker->name(),
			'email'       => $this->faker->unique()->safeEmail(),
			'employee_id' => 'EMP' . substr(md5(uniqid((string) mt_rand(), true)), 0, 10),
			'phone'       => $this->faker->phoneNumber(),
			'gender'      => 'Male',
			'salary'      => 5000,
		];
	}
}
