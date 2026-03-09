<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BillFactory extends Factory
{
	protected $model = Bill::class;

	public function definition(): array
	{
		return [
			'bill_id'     => (string) Str::uuid(),
			'bill_date'   => $this->faker->date(),
			'due_date'    => $this->faker->date(),
			'send_date'   => $this->faker->date(),
			'status'      => 0,
			'type'        => 'Other',
			'amount'      => $this->faker->randomFloat(2, 100, 10000),
			'description' => $this->faker->sentence(),
			'order_id'    => null,
			'vendor_id'   => null,
			'category_id' => null,
		];
	}
}
