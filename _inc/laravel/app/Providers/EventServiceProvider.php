<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\{Event, Log};
use App\Helpers\SafeConsoleOutput;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        SafeConsoleOutput::make()->writeln('Booting main ' . class_basename(self::class));
        Log::debug(__METHOD__ . ' invoked');
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        SafeConsoleOutput::make()->writeln('Returning about discovering events in main ' . class_basename(self::class));
        Log::debug(__METHOD__ . ' invoked');
        return false;
    }
}
