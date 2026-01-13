<?php

use Carbon\Carbon;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\ConsoleOutput;

error_log('Starting the server...');
define('LARAVEL_START', microtime(true));
if (file_exists(__DIR__ . '/../storage/framework/maintenance.php'))
    require __DIR__ . '/../storage/framework/maintenance.php';
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$output = new ConsoleOutput();
$msg = 'Tapping the Main Kernel to handle the Request...';
app()->runningInConsole()
    ? $output->writeln('<question> ' . $msg . ' </question> ')
    : $output->writeln($msg);
$response = null;
$request = null;
try {
    error_log('Tapping the Landing Page Kernel at ' . Carbon::now()->toDateTimeString());
    // ! MAIN POINT
    $response = tap($kernel->handle(
        $request = Request::capture()
    ))->send();
    // ! =========
    $output->writeln('No issues tapping the kernel');
    $statusMsg = "[HttpResponse] Status: {$response->getStatusCode()}";
    if ($response->getStatusCode() >= 400)
        app()->runningInConsole() ?
            $output->writeln('<error> ' . $statusMsg . ' </error>') :
            $output->writeln("## HTTP ERROR: {$statusMsg}");
    else
        app()->runningInConsole() ?
            $output->writeln('<info> ' . $statusMsg . ' </info>') :
            $output->writeln("## HTTP SUCCESS: {$statusMsg}");
    $contentMsg = "[HttpResponse] Content length: " . strlen($response->getContent()) . " bytes";
    app()->runningInConsole() ?
        $output->writeln('<info> ' . $contentMsg . ' </info>') :
        $output->writeln("## HTTP INFO: {$contentMsg}");
    $headers = $response?->headers?->all();
    foreach (['content-type', 'cache-control', 'location'] as $headerName)
        if (isset($headers[$headerName])) {
            $headerMsg = "[HttpResponse] Header {$headerName}: " . implode(', ', $headers[$headerName]);
            app()->runningInConsole() ?
                $output->writeln('<comment> ' . $headerMsg . ' </comment>') :
                $output->writeln("## HTTP HEADER: {$headerMsg}");
        }
    if ($response->getStatusCode() >= 400 && $response->getContent()) {
        $len =  strlen($response->getContent());
        $errorContentHead = substr($response->getContent(), 0, 100) .
            ($len > 100 ? '...' : '');
        $errorContentTail = substr(
            $response->getContent(),
            $len > 201
                ? strlen($response->getContent()) - 101
                : strlen($response->getContent()) - (strlen($response->getContent()) * 0.01 - 1),
            strlen($response->getContent())
        );
        $errorMsg = "[HttpResponse]\n\nError content head: {$errorContentHead}\n...\nError content tail: {$errorContentTail}";
        app()->runningInConsole() ?
            $output->writeln('<error> ' . $errorMsg . ' </error>') :
            $output->writeln("## HTTP ERROR: {$errorMsg}");
    }
} catch (\Exception $e) {
    $msg = 'Tapping failed: ' . $e->getMessage();
    $output->writeln($msg);
    error_log($msg);
}
// $output->writeln('<info> Loaded Providers: </info>');
// (new ConsoleOutput)
//     ->writeln(implode(', ', array_keys(app()->getLoadedProviders())));
$msg = 'Done tapping the Main Kernel';
app()->runningInConsole()
    ? $output->writeln('<info> ' . $msg . ' </info>')
    : $output->writeln($msg);
$kernel->terminate($request, $response);
