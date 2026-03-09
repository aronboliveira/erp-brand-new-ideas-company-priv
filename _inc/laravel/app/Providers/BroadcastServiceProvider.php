<?php

namespace App\Providers;

use Throwable;
use Illuminate\Support\Facades\{Broadcast, Log};
use Illuminate\Support\ServiceProvider;

final class BroadcastServiceProvider extends ServiceProvider
{
    private const CHANNELS_PATH = 'routes/channels.php';

    public function register(): void {}

    public function boot(): void
    {
        try {
            Broadcast::routes();
            $path = base_path(self::CHANNELS_PATH);
            if (!file_exists($path))
                Log::warning(__CLASS__ . '::boot — channels file not found', ['path' => $path]);
            else
                require $path;
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::boot failed', ['message' => $e->getMessage()]);
        }
    }

    /** @return array<int, string> */
    public function provides(): array
    {
        return [];
    }
}
