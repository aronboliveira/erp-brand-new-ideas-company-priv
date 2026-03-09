<?php

namespace Database\Factories;

use App\Models\JobApplicationNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobApplicationNoteFactory extends Factory
{
	protected $model = JobApplicationNote::class;

	public function definition(): array
	{
		return [
			'author' => $this->faker->name(),
			'note' => $this->faker->sentence(),
			'reviewer' => $this->faker->name(),
		];
	}
}
