<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_id' => 'INV-' . $this->faker->unique()->numerify('######'),
            'issue_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'due_date'   => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'send_date'  => null,
            'status'     => 0,
        ];
    }
}
