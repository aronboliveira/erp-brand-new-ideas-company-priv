<?php

namespace Database\Factories;

use App\Models\SupportReply;
use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SupportReplyFactory extends Factory
{
	protected $model = SupportReply::class;

	public function definition(): array
	{
		return [
			'id'          => (string) Str::uuid(),
			'code'        => (string) Str::uuid(),
			'support_id'  => Support::factory(),
			'user'        => User::factory(),
			'description' => $this->faker->optional()->paragraph(),
			'sent_at'     => $this->faker->optional()->dateTime(),
			'is_read'     => $this->faker->boolean(30),
			'read_at'     => null,
		];
	}
}
