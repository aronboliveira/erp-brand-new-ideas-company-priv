<?php

namespace Database\Factories;

use App\Models\Estimation;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstimationFactory extends Factory
{
	protected $model = Estimation::class;

	public function definition(): array
	{
		return [
			'amount' => $this->faker->randomFloat(2, 100, 10000),
			'discount' => 0,
			'description' => $this->faker->sentence(),
			'notes' => $this->faker->sentence(),
			'status' => 'draft',
		];
	}
}
