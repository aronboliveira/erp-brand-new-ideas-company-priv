<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'email'       => $this->faker->unique()->safeEmail(),
            'phone'       => $this->faker->phoneNumber(),
            'dob'         => $this->faker->date('Y-m-d', '-20 years'),
            'gender'      => $this->faker->randomElement(['male', 'female']),
            'address'     => $this->faker->address(),
            'salary'      => $this->faker->randomFloat(2, 2000, 15000),
            'salary_type' => $this->faker->randomElement(['Monthly', 'Hourly']),
            'is_active'   => 1,
        ];
    }
}
