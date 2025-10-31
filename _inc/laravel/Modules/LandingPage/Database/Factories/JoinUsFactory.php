<?php

namespace Modules\LandingPage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\{Facades\Auth, Str};
use App\Config\Constants\DatabaseConstants;
use Modules\LandingPage\Entities\JoinUs;

class JoinUsFactory extends Factory
{
	protected $model = JoinUs::class;

	public function definition(): array
	{
		$user = Auth::user();
		do $candidateId = (string) Str::uuid();
		while (JoinUs::where('id', $candidateId)->exists());
		do $candidateQueryKey = (string) Str::uuid();
		while (JoinUs::where('query_key', $candidateQueryKey)->exists());
		return [
			'id' => $candidateId,
			'query_key' => $candidateQueryKey,
			'email' => $this->faker->unique()->safeEmail(),
			DatabaseConstants::TABLE_CREATOR => $user?->id ?? DatabaseConstants::DEFAULT_UUID,
		];
	}
}
