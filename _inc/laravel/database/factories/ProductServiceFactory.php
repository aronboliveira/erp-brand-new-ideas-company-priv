<?php

namespace Database\Factories;

use App\Models\ProductService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductServiceFactory extends Factory
{
	protected $model = ProductService::class;

	public function definition(): array
	{
		return [
			'name'           => $this->faker->word(),
			'sku'            => strtoupper(Str::random(8)),
			'sale_price'     => $this->faker->randomFloat(4, 10, 5000),
			'purchase_price' => $this->faker->randomFloat(4, 5, 3000),
			'type'           => $this->faker->randomElement(['product', 'service']),
			'description'    => $this->faker->sentence(),
			'quantity'       => $this->faker->numberBetween(0, 100),
		];
	}
}
