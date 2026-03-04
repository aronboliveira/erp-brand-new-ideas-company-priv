<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'bill_id'   => (string) Str::uuid(),
            'bill_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'due_date'  => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status'    => 0,
        ];
    }
}
