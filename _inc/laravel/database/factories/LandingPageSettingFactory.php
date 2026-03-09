<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\LandingPage\Entities\LandingPageSetting;

class LandingPageSettingFactory extends Factory
{
	protected $model = LandingPageSetting::class;
	public function definition(): array
	{
		return [
			'query_key' => $this->faker->uuid(),
			'name'  => $this->faker->unique()->word,
			'value' => $this->faker->sentence,
		];
	}
}
