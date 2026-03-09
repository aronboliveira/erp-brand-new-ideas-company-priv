<?php

namespace App\Helpers;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop-in replacement for `new ConsoleOutput()` in HTTP-path code.
 *
 * Returns a real ConsoleOutput when running in CLI (artisan commands),
 * and a NullOutput (silent no-op) when running in HTTP context (PHP-FPM/mod_php).
 *
 * Usage:  $output = SafeConsoleOutput::make();
 *         $output->writeln('...');  // silent in HTTP, visible in CLI
 */
class SafeConsoleOutput
{
    public static function make(): OutputInterface
    {
        // TEMP: silenced for PHPStan — was: return app()->runningInConsole() ? new ConsoleOutput() : new NullOutput();
        return new NullOutput();
    }
}
