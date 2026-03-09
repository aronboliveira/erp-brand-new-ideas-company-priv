<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BillPaymentFactory extends Factory
{
    protected $model = BillPayment::class;

    public function definition(): array
    {
        return [
            'id'             => (string) Str::uuid(),
            'code'           => (string) Str::uuid(),
            'bill_id'        => Bill::factory(),
            'date'           => $this->faker->date(),
            'amount'         => $this->faker->randomFloat(2, 10, 5000),
            'account_id'     => BankAccount::factory(),
            'payment_method' => 0,
            'status'         => 'pending',
            'description'    => $this->faker->optional()->sentence(),
            'currency_id'    => 'BRL',
        ];
    }
}
