<?php

namespace Database\Factories;

use App\Models\ProductServiceUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductServiceUnitFactory extends Factory
{
	protected $model = ProductServiceUnit::class;

	public function definition(): array
	{
		return [
			'name'             => $this->faker->word(),
			'code'             => strtoupper($this->faker->unique()->lexify('PSU-????')),
			'status'           => 'active',
			'measurement_unit' => $this->faker->randomElement(['kg', 'unit', 'litre', 'metre']),
			'purchase_index'   => $this->faker->numberBetween(0, 100),
			'base_price'       => $this->faker->randomFloat(4, 1, 1000),
			'discount'         => 0.0000,
			'currency_id'      => 'BRL',
		];
	}
}
