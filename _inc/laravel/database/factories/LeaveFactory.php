<?php

namespace Database\Factories;

use App\Models\{Employee, Leave, LeaveType};
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
	protected $model = Leave::class;

	public function definition(): array
	{
		return [
			'employee_id'      => Employee::factory(),
			'leave_type_id'    => LeaveType::factory(),
			'applied_on'       => $this->faker->date(),
			'start_date'       => $this->faker->date(),
			'end_date'         => $this->faker->date(),
			'total_leave_days' => (string) $this->faker->numberBetween(1, 10),
			'discount'         => 0,
			'leave_reason'     => $this->faker->sentence(),
			'status'           => 'Pending',
		];
	}
}
