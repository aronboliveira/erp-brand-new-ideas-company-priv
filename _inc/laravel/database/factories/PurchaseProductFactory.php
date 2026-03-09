<?php

namespace Database\Factories;

use App\Models\PurchaseProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseProductFactory extends Factory
{
	protected $model = PurchaseProduct::class;

	public function definition(): array
	{
		return [
			'product_id'  => Str::uuid()->toString(),
			'purchase_id' => Str::uuid()->toString(),
			'quantity'     => fake()->numberBetween(1, 50),
			'tax'          => fake()->randomFloat(2, 0, 25),
			'discount'     => fake()->randomFloat(2, 0, 50),
			'total'        => fake()->randomFloat(2, 10, 5000),
		];
	}
}
