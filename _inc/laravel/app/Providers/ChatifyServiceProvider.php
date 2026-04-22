<?php

namespace App\Providers;

use Chatify\ChatifyServiceProvider as BaseChatifyServiceProvider;
use Illuminate\Support\Facades\Route;

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
        // Load views only — skip loadMigrationsFrom()
        // Wrap in try-catch so a missing views directory never prevents route registration.
        try {
            $viewsPath = dirname((new \ReflectionClass(BaseChatifyServiceProvider::class))->getFileName()) . '/views';
            if (is_dir($viewsPath)) {
                $this->loadViewsFrom($viewsPath, 'Chatify');
            }
        } catch (\Throwable) {
            // Silently ignore — views are not required for the messaging routes.
        }

        // Defer route registration until after ALL service providers have booted.
        // This ensures we are not subject to the RouteServiceProvider's URI-pluralisation
        // loop which iterates over routes already in the collection at that moment.
        $this->app->booted(function () {
            $this->loadRoutes();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Chatify\Console\InstallChatify::class,
            ]);
        }
    }

    /**
     * Override the base loadRoutes() to use Laravel 10-compatible fully-qualified
     * class names instead of the upstream namespace-prefixed string controllers
     * (which are not supported in Laravel 8+).
     */
    protected function loadRoutes(): void
    {
        $prefix = config('chatify.routes.prefix', 'chats');
        $mw     = config('chatify.routes.middleware', ['web', 'auth']);

        // Pusher auth must be accessible without the auth middleware so that
        // unauthenticated requests receive a 401 instead of a 302 redirect.
        Route::post("{$prefix}/chat/auth", [\App\Http\Controllers\MessagesController::class, 'pusherAuth'])
            ->middleware('web');

        Route::group(['prefix' => $prefix, 'middleware' => $mw], function () {
            Route::get('/{id?}', [\App\Http\Controllers\MessagesController::class, 'index'])
                ->where('id', '[0-9]+');
            Route::post('/id-info',           [\App\Http\Controllers\MessagesController::class, 'idFetchData']);
            Route::get('/downloads/{file}',   [\App\Http\Controllers\MessagesController::class, 'download']);
            Route::post('/send-message',      [\App\Http\Controllers\MessagesController::class, 'send']);
            Route::post('/fetch-messages',    [\App\Http\Controllers\MessagesController::class, 'fetch']);
            Route::post('/make-seen',         [\App\Http\Controllers\MessagesController::class, 'seen']);
            Route::post('/star',              [\App\Http\Controllers\MessagesController::class, 'favorite']);
            Route::get('/favorites',          [\App\Http\Controllers\MessagesController::class, 'getFavorites']);
            Route::post('/search',            [\App\Http\Controllers\MessagesController::class, 'search']);
            Route::get('/shared',             [\App\Http\Controllers\MessagesController::class, 'sharedPhotos']);
            Route::post('/delete-conversation', [\App\Http\Controllers\MessagesController::class, 'deleteConversation']);
            Route::post('/update-settings',   [\App\Http\Controllers\MessagesController::class, 'updateSettings']);
            Route::post('/set-active-status', [\App\Http\Controllers\MessagesController::class, 'setActiveStatus']);
            Route::get('/get-contacts',       [\App\Http\Controllers\MessagesController::class, 'getContacts']);
            Route::post('/update-contacts',   [\App\Http\Controllers\MessagesController::class, 'updateContactItem']);
        });
    }
}
