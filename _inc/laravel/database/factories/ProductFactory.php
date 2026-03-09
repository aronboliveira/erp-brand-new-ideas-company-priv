<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
	protected $model = Product::class;

	public function definition(): array
	{
		return [
			'name'        => $this->faker->words(2, true),
			'price'       => $this->faker->randomFloat(2, 1, 5000),
			'quantity'    => $this->faker->numberBetween(0, 1000),
			'description' => $this->faker->sentence(),
			'type'        => $this->faker->randomElement(['product', 'service', 'inventory']),
		];
	}
}
