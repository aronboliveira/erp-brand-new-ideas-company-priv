<?php

namespace App\Providers;

use Chatify\ChatifyServiceProvider as BaseChatifyServiceProvider;

/**
 * Custom Chatify provider that skips vendor migrations.
 *
 * The project already has its own ch_messages migration in database/migrations/
 * with UUID columns and proper guards. Loading the vendor migration causes
 * "Table already exists" errors in parallel test databases.
 */
class ChatifyServiceProvider extends BaseChatifyServiceProvider
{
    public function boot(): void
    {
        // Load views and routes only — skip loadMigrationsFrom()
        $this->loadViewsFrom(
            dirname((new \ReflectionClass(BaseChatifyServiceProvider::class))->getFileName()) . '/views',
            'Chatify'
        );
        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Chatify\Console\InstallChatify::class,
            ]);
        }
    }
}
