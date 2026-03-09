<?php

namespace Database\Factories;

use App\Models\UserContact;
use App\Enums\ContactRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserContactFactory extends Factory
{
	protected $model = UserContact::class;

	public function definition(): array
	{
		return [
			'name' => $this->faker->name(),
			'email' => $this->faker->safeEmail(),
			'phone' => $this->faker->phoneNumber(),
			'company' => $this->faker->company(),
			'role' => $this->faker->randomElement(array_column(ContactRole::cases(), 'value')),
		];
	}
}
