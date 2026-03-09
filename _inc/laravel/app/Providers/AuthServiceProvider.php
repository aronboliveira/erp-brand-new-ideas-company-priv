<?php

namespace App\Providers;

use App\Config\Constants\{PermissionsConstants, UsersConstants};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super-admin bypass: grant all permissions to super admin users.
        // This mirrors the ChecksPermissions::guard() trait bypass so that
        // controllers using raw $user->can() also respect the super-admin role.
        Gate::before(function ($user, string $ability): ?bool {
            return ($user[UsersConstants::COL_TP] ?? null) === PermissionsConstants::SA
                ? true
                : null;
        });
    }
}
