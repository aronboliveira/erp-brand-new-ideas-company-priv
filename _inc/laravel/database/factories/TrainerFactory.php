<?php

namespace Database\Factories;

use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainerFactory extends Factory
{
    protected $model = Trainer::class;

    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname'  => $this->faker->lastName(),
            'contact'   => $this->faker->phoneNumber(),
            'email'     => $this->faker->unique()->safeEmail(),
            'branch'    => (string) \Illuminate\Support\Str::uuid(),
        ];
    }
}