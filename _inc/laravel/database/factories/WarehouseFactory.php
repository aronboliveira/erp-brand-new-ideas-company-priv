<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->company(),
            'zip'     => $this->faker->postcode(),
            'city'    => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'country' => 'Brazil',
        ];
    }
}
