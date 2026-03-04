<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->company(),
            'email'           => $this->faker->unique()->safeEmail(),
            'contact'         => $this->faker->phoneNumber(),
            'billing_address' => $this->faker->address(),
            'billing_city'    => $this->faker->city(),
            'billing_state'   => $this->faker->state(),
            'billing_country' => 'Brazil',
            'billing_zip'     => $this->faker->postcode(),
        ];
    }
}
