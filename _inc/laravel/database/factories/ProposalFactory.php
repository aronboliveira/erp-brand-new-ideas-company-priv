<?php

namespace Database\Factories;

use App\Models\{Customer, Proposal};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProposalFactory extends Factory
{
	protected $model = Proposal::class;

	public function definition(): array
	{
		return [
			'title'       => $this->faker->sentence(3),
			'proposal_id' => (string) Str::uuid(),
			'amount'      => 1000,
			'issue_date'  => now(),
			'status'      => 0,
			'customer_id' => Customer::factory(),
		];
	}
}
