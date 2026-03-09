<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContractFactory extends Factory
{
	protected $model = Contract::class;

	public function definition(): array
	{
		return [
			'title'           => $this->faker->sentence(3),
			'subject'         => $this->faker->sentence(5),
			'value'           => (string) $this->faker->randomFloat(2, 500, 50000),
			'currency'        => 'BRL',
			'description'     => $this->faker->paragraph(),
			'status'          => 'draft',
			'start_date'      => $this->faker->date(),
			'end_date'        => $this->faker->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
		];
	}
}
