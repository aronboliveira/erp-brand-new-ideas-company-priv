<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->sentence(3),
            'date'        => $this->faker->date(),
            'time'        => $this->faker->time('H:i'),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
