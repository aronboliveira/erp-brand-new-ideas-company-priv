<?php

namespace Database\Factories;

use App\Models\PosProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PosProductFactory extends Factory
{
	protected $model = PosProduct::class;

	public function definition(): array
	{
		return [
			'product_id'  => Str::uuid()->toString(),
			'pos_id'      => Str::uuid()->toString(),
			'quantity'     => fake()->numberBetween(1, 50),
			'tax'          => fake()->randomFloat(2, 0, 25),
			'discount'     => fake()->randomFloat(2, 0, 50),
			'price'        => fake()->randomFloat(2, 1, 999.99),
			'description'  => fake()->optional(0.7)->sentence(),
		];
	}
}
