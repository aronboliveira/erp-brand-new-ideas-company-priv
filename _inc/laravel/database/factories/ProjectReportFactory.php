<?php

namespace Database\Factories;

use App\Models\ProjectReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectReportFactory extends Factory
{
	protected $model = ProjectReport::class;

	public function definition(): array
	{
		return [
			'name'      => $this->faker->sentence(2),
			'file_path' => '/tmp/test.pdf',
			'extension' => 'pdf',
			'mime_type' => 'application/pdf',
		];
	}
}
