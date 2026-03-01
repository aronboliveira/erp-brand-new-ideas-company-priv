<?php

/**
 * Laravel Dev-Server Router
 *
 * Enhances php artisan serve with:
 *  - Cache-Control + Expires headers for static assets  (fixes e2e "cache headers" test)
 *  - Gzip Content-Encoding for compressible text assets (fixes e2e "GZIP compression" test)
 *
 * The Illuminate ServeCommand prefers `<project-root>/server.php` over the
 * vendor router, so placing this file here is sufficient — no framework hacks
 * needed.  When PHP's built-in server router returns false the file is served
 * "as-is" (no custom headers), so we serve static assets manually for the
 * routes we care about and fall back to the vendor router for everything else.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// ── MIME map ─────────────────────────────────────────────────────────────────
const STATIC_MIME = [
    'js'   => 'application/javascript',
    'mjs'  => 'application/javascript',
    'css'  => 'text/css',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'ico'  => 'image/x-icon',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2'=> 'font/woff2',
    'ttf'  => 'font/ttf',
    'eot'  => 'application/vnd.ms-fontobject',
    'map'  => 'application/json',
    'json' => 'application/json',
    'txt'  => 'text/plain',
    'xml'  => 'application/xml',
];

// Extensions eligible for gzip when client advertises Accept-Encoding: gzip
const COMPRESSIBLE = ['js', 'mjs', 'css', 'svg', 'json', 'map', 'xml', 'txt'];

if ($uri !== '/' && file_exists($file = $publicPath . $uri)) {

    $ext  = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    $mime = STATIC_MIME[$ext] ?? null;

    // Unknown extension: fall back to PHP's built-in serving (no custom headers)
    if ($mime === null) {
        return false;
    }

    // ── Cache headers ─────────────────────────────────────────────────────
    // 30-day immutable cache — mirrors the nginx production rule
    $maxAge = 30 * 24 * 3600; // 2592000 s
    header("Cache-Control: public, max-age={$maxAge}, immutable");
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge));
    header('Vary: Accept-Encoding');

    // ── Content-Type ──────────────────────────────────────────────────────
    header("Content-Type: {$mime}");

    $content = file_get_contents($file);

    // ── Gzip encoding ─────────────────────────────────────────────────────
    $acceptsGzip = str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip');

    if ($acceptsGzip && in_array($ext, COMPRESSIBLE, true)) {
        $compressed = gzencode($content, 6);
        if ($compressed !== false) {
            header('Content-Encoding: gzip');
            header('Content-Length: ' . strlen($compressed));
            echo $compressed;
            return true;
        }
    }

    header('Content-Length: ' . strlen($content));
    echo $content;
    return true;
}

// Dynamic PHP routes pass through Laravel as normal
require_once $publicPath . '/index.php';
