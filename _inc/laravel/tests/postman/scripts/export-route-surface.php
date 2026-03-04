#!/usr/bin/env php
<?php

declare(strict_types=1);

$laravelRoot = dirname(__DIR__, 3);
$outputPath = $argv[1] ?? null;
$artisan = $laravelRoot . DIRECTORY_SEPARATOR . 'artisan';

if (!is_file($artisan)) {
    fwrite(STDERR, "artisan not found at {$artisan}\n");
    exit(1);
}

$cwd = getcwd();
chdir($laravelRoot);
$raw = shell_exec('php artisan route:list --json 2>/dev/null');
chdir($cwd ?: $laravelRoot);

if (!is_string($raw) || trim($raw) === '') {
    fwrite(STDERR, "Could not read route list from artisan.\n");
    exit(1);
}

$start = strpos($raw, '[{"domain"');
$start = $start === false ? strpos($raw, '[{"') : $start;
$end = strrpos($raw, ']');
if ($start === false || $end === false || $end < $start) {
    fwrite(STDERR, "Could not isolate JSON route payload from artisan output.\n");
    exit(1);
}

$jsonPayload = substr($raw, $start, $end - $start + 1);
$routes = json_decode($jsonPayload, true);
if (!is_array($routes)) {
    fwrite(STDERR, "artisan route:list did not return valid JSON.\n");
    exit(1);
}

$interesting = array_values(array_filter($routes, static function (array $route): bool {
    $uri = (string) ($route['uri'] ?? '');
    return str_starts_with($uri, 'api')
        || str_contains($uri, 'login')
        || str_contains($uri, 'report')
        || str_contains($uri, 'export')
        || str_contains($uri, 'import');
}));

$payload = [
    'generated_at' => date(DATE_ATOM),
    'laravel_root' => $laravelRoot,
    'route_count' => count($routes),
    'interesting_routes' => $interesting,
];

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "Failed to encode route inventory.\n");
    exit(1);
}

if ($outputPath !== null) {
    file_put_contents($outputPath, $json . PHP_EOL);
    fwrite(STDOUT, "Wrote route inventory to {$outputPath}\n");
    exit(0);
}

fwrite(STDOUT, $json . PHP_EOL);
