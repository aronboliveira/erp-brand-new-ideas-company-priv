<?php

namespace Database\Factories;

use App\Models\Revenue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenue>
 */
class RevenueFactory extends Factory
{
    protected $model = Revenue::class;

    public function definition(): array
    {
        return [
            'date'           => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'amount'         => $this->faker->randomFloat(2, 500, 50000),
            'payment_method' => 0,
            'description'    => $this->faker->sentence(),
        ];
    }
}
