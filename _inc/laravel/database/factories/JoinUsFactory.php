<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Config\Constants\DatabaseConstants;
use Modules\LandingPage\Entities\JoinUs;

class JoinUsFactory extends Factory
{
	protected $model = JoinUs::class;

	public function definition(): array
	{
		return [
			'id' => (string) Str::uuid(),
			'email' => $this->faker->unique()->safeEmail(),
			DatabaseConstants::COL_TABLE_CREATOR => null,
		];
	}
}
