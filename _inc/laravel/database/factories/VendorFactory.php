<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

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
