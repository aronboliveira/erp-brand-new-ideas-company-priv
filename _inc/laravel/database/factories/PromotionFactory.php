<?php

namespace Database\Factories;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'employee_id'    => User::factory(),
            'designation_id' => $this->faker->randomNumber(3),
            'promotion_title' => $this->faker->words(3, true),
            'promotion_date' => $this->faker->date(),
            'description'    => $this->faker->sentence(),
        ];
    }
}
