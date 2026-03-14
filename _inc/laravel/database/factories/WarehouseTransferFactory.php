<?php

namespace Database\Factories;

use App\Models\WarehouseTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseTransferFactory extends Factory
{
    protected $model = WarehouseTransfer::class;

    public function definition(): array
    {
        return [
            'product_id'     => \App\Models\Product::factory(),
            'from_warehouse' => (string) \Illuminate\Support\Str::uuid(),
            'to_warehouse'   => (string) \Illuminate\Support\Str::uuid(),
            'quantity'       => $this->faker->numberBetween(1, 100),
        ];
    }
}