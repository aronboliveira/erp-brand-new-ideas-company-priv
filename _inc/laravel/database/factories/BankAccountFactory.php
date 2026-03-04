<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'holder_name'    => $this->faker->name(),
            'bank_name'      => $this->faker->company() . ' Bank',
            'account_number' => $this->faker->unique()->bankAccountNumber(),
            'contact_number' => $this->faker->phoneNumber(),
            'bank_address'   => $this->faker->address(),
            'opening_balance' => $this->faker->randomFloat(2, 100, 50000),
        ];
    }
}
