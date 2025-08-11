<?php

namespace App\Providers;

use Throwable;
use Illuminate\Support\Facades\{Broadcast, Log};
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

final class BroadcastServiceProvider extends ServiceProvider
{
    private const CHANNELS_PATH = 'routes/channels.php';

    public function register(): void
    {
        (new ConsoleOutput)->writeln('Registering main ' . class_basename(self::class));
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
    }

    public function boot(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        $output = new ConsoleOutput();
        try {
            Broadcast::routes();
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' registered broadcast routes');
            $path = base_path(self::CHANNELS_PATH);
            $output->writeln('Booting main ' . class_basename(self::class));
            if (!file_exists($path))
                Log::warning(
                    __CLASS__ . '::' . __FUNCTION__ . ' channels file not found',
                    ['path' => $path]
                );
            else
                require $path;
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' loaded channels file', ['path' => $path]);
        } catch (Throwable $e) {
            $output->writeln('Failed to boot main ' . class_basename(self::class));
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' called');
        return [];
    }
}
