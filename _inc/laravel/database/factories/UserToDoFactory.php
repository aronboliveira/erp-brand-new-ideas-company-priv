<?php

namespace Database\Factories;

use App\Models\UserToDo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserToDoFactory extends Factory
{
	protected $model = UserToDo::class;

	public function definition(): array
	{
		return [
			'id'          => (string) Str::uuid(),
			'title'       => $this->faker->sentence(3),
			'description' => $this->faker->optional()->paragraph(),
			'user_id'     => User::factory(),
			'priority'    => $this->faker->randomElement(['none', 'low', 'medium', 'high', 'critical']),
			'progress'    => $this->faker->randomFloat(2, 0, 100),
			'order'       => $this->faker->numberBetween(0, 50),
			'is_complete' => false,
			'is_favorite' => false,
		];
	}
}
