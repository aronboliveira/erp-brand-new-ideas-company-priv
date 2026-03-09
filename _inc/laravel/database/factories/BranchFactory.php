<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
	protected $model = Branch::class;

	public function definition(): array
	{
		return [
			'name'          => $this->faker->unique()->company(),
			'country'       => 'Brazil',
			'state'         => 'SP',
			'city'          => 'São Paulo',
			'administrator' => null,
			'manager'       => null,
			'founder'       => null,
		];
	}
}
