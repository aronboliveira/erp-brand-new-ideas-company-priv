<?php

namespace Database\Factories;

use App\Models\Pos;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Str;

class PosFactory extends Factory
{
    protected $model = Pos::class;

    public function definition(): array
    {
        return [
            'pos_id' => 'POS-' . Str::upper(Str::random(8)),
        ];
    }
}
