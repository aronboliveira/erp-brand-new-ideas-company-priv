<?php

namespace Database\Factories;

use App\Models\TaskStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaskStageFactory extends Factory
{
    protected $model = TaskStage::class;

    public function definition(): array
    {
        return [
            'id'          => (string) Str::uuid(),
            'name'        => $this->faker->randomElement(['Todo', 'In Progress', 'Review', 'Done']),
            'description' => $this->faker->optional()->sentence(),
            'status'      => 'pending',
            'progress'    => 0,
            'complete'    => false,
            'color'       => $this->faker->hexColor(),
            'order'       => $this->faker->numberBetween(0, 20),
            'priority'    => $this->faker->randomElement(['none', 'low', 'medium', 'high']),
        ];
    }
}
