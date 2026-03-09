<?php

namespace Database\Factories;

use App\Models\{BankAccount, Customer, Revenue};
use Illuminate\Database\Eloquent\Factories\Factory;

class RevenueFactory extends Factory
{
	protected $model = Revenue::class;

	public function definition(): array
	{
		return [
			'date'        => $this->faker->date(),
			'amount'      => $this->faker->randomFloat(2, 10, 10000),
			'account_id'  => BankAccount::factory(),
			'customer_id' => Customer::factory(),
			'description' => $this->faker->sentence(),
			'reference'   => $this->faker->word(),
			'status'      => 'undefined',
		];
	}
}
