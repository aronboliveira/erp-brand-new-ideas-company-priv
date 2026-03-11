<?php

namespace Database\Factories;

use App\Models\ChFavorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChFavoriteFactory extends Factory
{
    protected $model = ChFavorite::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'favorite_id' => User::factory(),
        ];
    }
}
