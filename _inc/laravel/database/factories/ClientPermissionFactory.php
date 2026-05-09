<?php

namespace Database\Factories;

use App\Models\ClientPermission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientPermissionFactory extends Factory
{
    protected $model = ClientPermission::class;

    public function definition(): array
    {
        // client_id is NOT NULL; (client_id, type) has a unique constraint.
        // Generate a fresh UUID per factory call so concurrent factory
        // creations don't collide on the default `type = 'user_client'`.
        return [
            'client_id'   => (string) Str::uuid(),
            'permissions' => '[]',
        ];
    }
}
