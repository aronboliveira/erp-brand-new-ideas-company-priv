<?php

namespace Database\Factories;

use App\Models\{Customer, Invoice};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
	protected $model = Invoice::class;

	public function definition(): array
	{
		return [
			'invoice_id'  => (string) Str::uuid(),
			'issue_date'  => $this->faker->date(),
			'due_date'    => $this->faker->date(),
			'send_date'   => $this->faker->date(),
			'status'      => 0,
			'amount'      => $this->faker->randomFloat(2, 100, 10000),
			'currency_id' => 'BRL',
			'customer_id' => Customer::factory(),
		];
	}
}
