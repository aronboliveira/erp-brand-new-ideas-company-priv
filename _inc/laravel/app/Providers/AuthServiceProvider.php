<?php

namespace App\Providers;

use Illuminate\Support\Facades\{Gate, Log};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Helpers\SafeConsoleOutput;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        SafeConsoleOutput::make()->writeln('Booting main ' . class_basename(self::class) . ' for registering policies...');
        Log::debug(__METHOD__ . ' invoked');
        $this->registerPolicies();
    }
}
