<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
	protected $model = BankAccount::class;

	public function definition(): array
	{
		return [
			'account_number' => $this->faker->unique()->bankAccountNumber(),
			'holder_name'    => $this->faker->company(),
			'contact_number' => $this->faker->phoneNumber(),
			'bank_name'      => $this->faker->company() . ' Bank',
			'bank_address'   => $this->faker->address(),
			'currency_id'    => 'BRL',
			'is_active'      => 1,
		];
	}
}
