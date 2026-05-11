<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
	protected $model = Customer::class;

	public function definition(): array
	{
		return [
			'name'          => $this->faker->name(),
			'email'         => 'customer-' . (string) Str::uuid() . '@example.test',
			'contact'       => $this->faker->phoneNumber(),
			'is_active'     => 1,
			'balance'       => 0.00,
			'phone'         => $this->faker->phoneNumber(),
			'billing_email'  => $this->faker->safeEmail(),
			'shipping_email' => $this->faker->safeEmail(),
			'billing_phone'  => $this->faker->phoneNumber(),
			'shipping_phone' => $this->faker->phoneNumber(),
		];
	}
}
