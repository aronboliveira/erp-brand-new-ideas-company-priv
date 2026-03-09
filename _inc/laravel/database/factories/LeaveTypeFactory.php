<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
	protected $model = LeaveType::class;

	public function definition(): array
	{
		return [
			'title' => $this->faker->unique()->word() . ' Leave',
			'days'  => $this->faker->numberBetween(1, 30),
		];
	}
}
