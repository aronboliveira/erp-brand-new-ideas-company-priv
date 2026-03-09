<?php

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);
$successes = [];
// TEMP: silenced for PHPStan — was: $output = new ConsoleOutput();
$output = new \Symfony\Component\Console\Output\NullOutput();
$msg = 'Instiating Http Kernel Singleton...';
app()->runningInConsole()
    ? $output->writeln('<question> ' . $msg . ' </question>')
    : $output->writeln($msg);
try {
    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    $successes[] = [Illuminate\Contracts\Http\Kernel::class => true];
} catch (\Throwable $e) {
    Log::error('Failed to instantiate HTTP Kernel singleton', [
        'exception' => $e,
    ]);
    $msg = 'HTTP Kernel error: ' . $e->getMessage();
    app()->runningInConsole()
        ? $output->writeln('<error> ' . $msg . ' </error>')
        : $output->writeln('## KERNEL ERROR: ' . $msg);
    $successes[] = [Illuminate\Contracts\Http\Kernel::class => false];
}
try {
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    $successes[] = [Illuminate\Contracts\Console\Kernel::class => true];
} catch (\Throwable $e) {
    Log::error('Failed to instantiate Console Kernel singleton', [
        'exception' => $e,
    ]);
    $msg = 'Console Kernel error: ' . $e->getMessage();
    app()->runningInConsole()
        ? $output->writeln('<error> ' . $msg . ' </error>')
        : $output->writeln('## KERNEL ERROR: ' . $msg);
    $successes[] = [Illuminate\Contracts\Console\Kernel::class => false];
}

$msg = 'Instiating Exception Handler Singleton...';
app()->runningInConsole()
    ? $output->writeln('<question> ' . $msg . ' </question>')
    : $output->writeln($msg);
try {
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );
    $successes[] = [Illuminate\Contracts\Debug\ExceptionHandler::class => true];
} catch (\Throwable $e) {
    Log::error('Failed to instantiate Exception Handler singleton', [
        'exception' => $e,
    ]);
    $msg = 'Exception Handler error: ' . $e->getMessage();
    app()->runningInConsole()
        ? $output->writeln('<error> ' . $msg . ' </error>')
        : $output->writeln('## KERNEL ERROR: ' . $msg);
    $successes[] = [Illuminate\Contracts\Debug\ExceptionHandler::class => false];
}
if (collect($successes)->some(fn ($s) => !$s)) {
    $msg = 'Failed creating singletons. Aborting.';
    app()->runningInConsole()
        ? $output->writeln('<error> ' . $msg . ' </error>')
        : $output->writeln('## KERNEL ERROR: ' . $msg);
    $output->writeln(json_encode($successes));
    die;
};
$msg = 'Done creating Core App Singletons';
app()->runningInConsole()
    ? $output->writeln('<info> ' . $msg . ' </info> ')
    : $output->writeln($msg);
return $app;
