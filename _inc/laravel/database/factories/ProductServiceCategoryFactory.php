<?php

namespace Database\Factories;

use App\Models\ProductServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductServiceCategoryFactory extends Factory
{
	protected $model = ProductServiceCategory::class;

	public function definition(): array
	{
		return [
			'name' => $this->faker->word(),
			'type' => $this->faker->randomElement(['income', 'expense']),
			'code' => strtoupper($this->faker->lexify('????')),
		];
	}
}
