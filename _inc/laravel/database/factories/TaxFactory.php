<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'rate' => fake()->randomFloat(2, 0, 25),
        ];
    }
}
