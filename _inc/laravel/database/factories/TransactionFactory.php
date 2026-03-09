<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
	protected $model = Transaction::class;

	public function definition(): array
	{
		return [
			'code'                 => 'TRS-' . (string) Str::uuid() . '-' . time(),
			'account'              => null,
			'type'                 => 'credit',
			'amount'               => $this->faker->randomFloat(2, 10, 1000),
			'description'          => $this->faker->sentence(),
			'date'                 => $this->faker->date(),
			'category'             => 'Sales',
			'status'               => 'undefined',
			'payment_method_label' => 'other',
			'payment_method'       => 0,
			'payment_type'         => 'other',
			'currency_id'          => 'BRL',
		];
	}
}
