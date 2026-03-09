<?php

namespace App\Traits;

use Symfony\Component\Console\Output\ConsoleOutput;

trait ConsoleOutputs
{
    protected function consoleOutput(string $message, string $level = 'info'): void
    {
        $output = new ConsoleOutput();
        $tag = strtoupper($level);
        $output->writeln("## {$tag}: {$message}");
    }
}
