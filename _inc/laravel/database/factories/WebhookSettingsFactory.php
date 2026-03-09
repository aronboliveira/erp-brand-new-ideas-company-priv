<?php

namespace Database\Factories;

use App\Models\WebhookSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookSettingsFactory extends Factory
{
	protected $model = WebhookSettings::class;

	public function definition(): array
	{
		return [
			'module' => $this->faker->word(),
			'url' => $this->faker->url(),
			'method' => $this->faker->randomElement(['GET', 'POST', 'PUT']),
			'created_by' => 1,
		];
	}
}
