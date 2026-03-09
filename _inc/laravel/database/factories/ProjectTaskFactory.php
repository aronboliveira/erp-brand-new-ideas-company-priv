<?php

namespace Database\Factories;

use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTaskFactory extends Factory
{
	protected $model = ProjectTask::class;

	public function definition(): array
	{
		return [
			'name'        => $this->faker->sentence(3),
			'description' => $this->faker->paragraph(),
			'priority'    => 'medium',
			'start_date'  => now(),
			'end_date'    => now()->addDays(7),
			'project_id'  => null,
		];
	}
}
