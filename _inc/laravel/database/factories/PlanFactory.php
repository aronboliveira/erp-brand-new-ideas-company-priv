<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
	protected $model = Plan::class;

	public function definition(): array
	{
		return [
			'query_key'    => Str::uuid()->toString(),
			'name'         => fake()->unique()->word() . ' Plan ' . Str::random(6),
			'price'        => fake()->randomFloat(2, 0, 999.99),
			'duration'     => fake()->randomElement(['lifetime', 'month', 'semimonthly', 'quarterly', 'semiannual', 'year']),
			'max_users'    => fake()->numberBetween(1, 100),
			'max_customers' => fake()->numberBetween(1, 500),
			'max_vendors'  => fake()->numberBetween(1, 200),
			'max_clients'  => fake()->numberBetween(1, 300),
			'description'  => fake()->sentence(),
			'image'        => null,
			'crm'          => fake()->boolean() ? 1 : 0,
			'hrm'          => fake()->boolean() ? 1 : 0,
			'account'      => fake()->boolean() ? 1 : 0,
			'project'      => fake()->boolean() ? 1 : 0,
			'pos'          => fake()->boolean() ? 1 : 0,
			'chatgpt'      => fake()->boolean() ? 1 : 0,
			'storage_limit' => fake()->numberBetween(50, 10000),
		];
	}

	public function free(): static
	{
		return $this->state(fn(array $attributes) => [
			'name'  => 'Free',
			'price' => 0,
		]);
	}
}
