<?php

namespace App\Console\Commands;

use ReflectionMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ViewRouteList extends Command
{
    protected $signature  = 'view:routes';
    protected $description = 'List all routes whose controller methods return view()';

    public function handle()
    {
        $allRoutes = Route::getRoutes();
        $fileCache = [];
        $matches  = [];
        foreach ($allRoutes as $route) {
            $action = $route->getActionName();
            if (!Str::contains($action, '@')) continue;
            [$class, $method] = explode('@', $action, 2);
            if (!class_exists($class) || !method_exists($class, $method)) continue;
            $ref = new ReflectionMethod($class, $method);
            $file = $ref->getFileName();
            if (!isset($fileCache[$file]))
                $fileCache[$file] = file($file, FILE_IGNORE_NEW_LINES);
            $lines = $fileCache[$file];
            $start = $ref->getStartLine() - 1;
            $end  = $ref->getEndLine() - 1;
            $body = array_slice($lines, $start, $end - $start + 1);
            $text = implode("\n", $body);
            if (Str::contains($text, 'return view('))
                $matches[$route->uri()] = ['action' => $action, 'method' => $route->methods()[0]];
        }
        ksort($matches);
        foreach ($matches as $uri => $routeInfo) {
            $action = $routeInfo['action'];
            $method   = $routeInfo['method'];
            $this->line(sprintf(
                "%-8s ........... %-40s ........... %s",
                $method,
                $uri,
                $action,
            ));
        }
    }
}
