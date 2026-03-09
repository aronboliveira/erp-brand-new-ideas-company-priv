<?php

namespace Database\Factories;

use App\Models\{Branch, Department};
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
	protected $model = Department::class;

	public function definition(): array
	{
		return [
			'name'      => $this->faker->unique()->word(),
			'branch_id' => Branch::factory(),
		];
	}
}
