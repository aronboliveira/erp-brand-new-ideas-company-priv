<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
	protected $model = Vendor::class;

	public function definition(): array
	{
		return [
			'vendor_id'       => User::factory(),
			'name'            => $this->faker->company(),
			'email'           => $this->faker->unique()->safeEmail(),
			'password'        => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
			'contact'         => $this->faker->phoneNumber(),
			'is_active'       => 1,
			'balance'         => 0.00,
		];
	}
}
