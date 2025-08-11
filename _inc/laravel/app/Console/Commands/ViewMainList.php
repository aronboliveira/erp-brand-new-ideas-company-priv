<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class ViewMainList extends Command
{
    protected $signature = 'view:mainfiles';
    protected $description = 'CLI to return all main views';
    public function handle()
    {
        $views = collect($this->laravel['view']->getFinder()->getPaths())
            ->flatMap(
                fn ($path) => collect($this->laravel['files']->allFiles($path))
                    ->map(fn ($file) => preg_replace(
                        [
                            '/^.*views[\/\\\\]/',
                            '/\.blade\.php$/',
                            '/[\/\\\\]/'
                        ],
                        [
                            '',
                            '',
                            '.'
                        ],
                        $file->getPathname()
                    ))
            )
            ->sort()
            ->unique();
        foreach ($views as $view) $this->line($view);
        (new ConsoleOutput)->writeln('<info>There are a total of ' . $views->count() . ' views</info>');
    }
}
