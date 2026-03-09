<?php

namespace Database\Factories;

use App\Models\{Product, StockReport};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StockReportFactory extends Factory
{
	protected $model = StockReport::class;

	public function definition(): array
	{
		return [
			'code'        => 'SR-' . strtoupper(Str::random(6)),
			'title'       => $this->faker->words(3, true),
			'type'        => $this->faker->randomElement([
				'pos',
				'purchase',
				'warehouse',
				'sales',
				'inventory',
				'financial',
				'accounting',
				'payroll',
			]),
			'type_id'     => (string) Str::uuid(),
			'quantity'    => $this->faker->numberBetween(1, 500),
			'description' => $this->faker->sentence(),
			'product_id'  => Product::factory(),
		];
	}
}
