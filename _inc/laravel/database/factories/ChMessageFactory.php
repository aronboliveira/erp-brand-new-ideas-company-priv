<?php

namespace Database\Factories;

use App\Models\ChMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChMessageFactory extends Factory
{
    protected $model = ChMessage::class;

    public function definition(): array
    {
        return [
            'from_id' => User::factory(),
            'to_id'   => User::factory(),
            'message' => $this->faker->sentence(),
            'seen'    => 0,
        ];
    }
}
