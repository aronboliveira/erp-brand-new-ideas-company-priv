<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\{Arr, Str};
use Symfony\Component\Console\Output\ConsoleOutput;

class ViewList extends Command
{
    protected $signature = 'view:files';
    protected $description = 'CLI to return all main views';
    public function handle()
    {
        $finder = $this->laravel['view']->getFinder();
        $allPaths = array_merge(
            $finder->getPaths(),
            Arr::flatten($finder->getHints())
        );
        $hints = $finder->getHints();
        $views = collect($allPaths)
            ->flatMap(
                fn ($path) =>
                collect($this->laravel['files']->allFiles($path))
                    ->map(function ($file) use ($path, $hints) {
                        $full = $file->getPathname();
                        $ns = null;
                        foreach ($hints as $namespace => $paths)
                            foreach ($paths as $hintPath) {
                                if (Str::startsWith($full, $hintPath . DIRECTORY_SEPARATOR)) {
                                    $ns = $namespace;
                                    $path = $hintPath;
                                    break 2;
                                }
                            }
                        $relative = Str::after($full, $path . DIRECTORY_SEPARATOR);
                        $viewKey = str_replace(
                            ['.blade.php', DIRECTORY_SEPARATOR],
                            ['', '.'],
                            $relative
                        );
                        return $ns
                            ? "{$ns}::{$viewKey}"
                            : $viewKey;
                    })
            )
            ->sort()
            ->unique();

        foreach ($views as $view) {
            $this->line($view);
        }

        (new ConsoleOutput)
            ->writeln('<info>There are a total of ' . $views->count() . ' views</info>');
    }
}
