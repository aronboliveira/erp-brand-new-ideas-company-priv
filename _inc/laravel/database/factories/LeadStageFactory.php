<?php

namespace Database\Factories;

use App\Models\LeadStage;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LeadStageFactory extends Factory
{
	protected $model = LeadStage::class;

	public function definition(): array
	{
		return [
			'id'                               => (string) Str::uuid(),
			'name'                             => $this->faker->word(),
			'pipeline_id'                      => Pipeline::factory(),
			'order'                            => $this->faker->numberBetween(0, 10),
			'notes'                            => $this->faker->optional()->sentence(),
			'estimated_chance_of_continuation' => $this->faker->numberBetween(0, 100),
			'is_critical'                      => $this->faker->boolean(20),
		];
	}
}
