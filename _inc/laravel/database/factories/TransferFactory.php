<?php

namespace Database\Factories;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        return [
            'employee_id'   => \App\Models\Employee::factory(),
            'branch_id'     => \App\Models\Branch::factory(),
            'department_id' => \App\Models\Department::factory(),
            'transfer_date' => $this->faker->date(),
            'description'   => $this->faker->sentence(),
        ];
    }
}