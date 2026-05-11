<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
	protected $model = Branch::class;

	public function definition(): array
	{
		return [
			'name'          => 'Branch ' . (string) Str::uuid(),
			'country'       => 'Brazil',
			'state'         => 'SP',
			'city'          => 'São Paulo',
			'administrator' => null,
			'manager'       => null,
			'founder'       => null,
		];
	}
}
