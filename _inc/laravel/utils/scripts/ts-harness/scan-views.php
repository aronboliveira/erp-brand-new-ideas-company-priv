<?php
/**
 * Scan blade views and extract JavaScript dependencies
 * Outputs JSON mapping of view paths to their JS file references
 * 
 * Usage: php scan-views.php [output.json]
 */

$baseDir = dirname(__DIR__, 3) . '/_inc/laravel';

$viewDirs = [
    $baseDir . '/resources/views',
];

// Also scan Module views
$moduleDirs = glob($baseDir . '/Modules/*/Resources/views');
$viewDirs = array_merge($viewDirs, $moduleDirs);

$results = [];

/**
 * Extract JS references from a blade file
 */
function extractJsReferences(string $content, string $filePath): array {
    $refs = [];
    
    // Pattern for asset() calls with .js files
    if (preg_match_all('/asset\s*\(\s*[\'"]([^\'"]+\.js)[\'"]\s*\)/i', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $refs[] = ['type' => 'asset', 'path' => $match, 'source' => 'asset()'];
        }
    }
    
    // Pattern for @vite() calls
    if (preg_match_all('/@vite\s*\(\s*\[?\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $refs[] = ['type' => 'vite', 'path' => $match, 'source' => '@vite'];
        }
    }
    
    // Pattern for mix() calls
    if (preg_match_all('/mix\s*\(\s*[\'"]([^\'"]+\.js)[\'"]\s*\)/i', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $refs[] = ['type' => 'mix', 'path' => $match, 'source' => 'mix()'];
        }
    }
    
    // Pattern for direct <script src=""> with route patterns
    if (preg_match_all('/<script[^>]+src=[\'"]([^\'"]*(?:routes|pages|assets\/js)[^\'"]*\.js)[\'"][^>]*>/i', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $refs[] = ['type' => 'direct', 'path' => $match, 'source' => '<script>'];
        }
    }
    
    // Extract @push('scripts') blocks for inline script references
    if (preg_match_all("/@push\s*\(\s*['\"]scripts['\"]\s*\)(.*?)@endpush/s", $content, $matches)) {
        foreach ($matches[1] as $block) {
            // Look for asset calls in push block
            if (preg_match_all('/asset\s*\(\s*[\'"]([^\'"]+\.js)[\'"]\s*\)/i', $block, $assetMatches)) {
                foreach ($assetMatches[1] as $match) {
                    $refs[] = ['type' => 'push_asset', 'path' => $match, 'source' => '@push(scripts)'];
                }
            }
            // Look for route references in URL patterns
            if (preg_match_all('/[\'"]\/?(assets\/js\/[^\'"]+\.js)[\'"]/i', $block, $routeMatches)) {
                foreach ($routeMatches[1] as $match) {
                    $refs[] = ['type' => 'push_path', 'path' => $match, 'source' => '@push(scripts)'];
                }
            }
        }
    }
    
    return $refs;
}

/**
 * Get route name from view path
 */
function getRouteName(string $viewPath, string $baseDir): string {
    $relative = str_replace($baseDir . '/', '', $viewPath);
    $relative = str_replace(['resources/views/', 'Resources/views/'], '', $relative);
    $relative = str_replace('.blade.php', '', $relative);
    return str_replace('/', '.', $relative);
}

/**
 * Recursively scan directory for blade files
 */
function scanDirectory(string $dir, string $baseDir, array &$results): void {
    $files = glob($dir . '/*.blade.php');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $refs = extractJsReferences($content, $file);
        
        if (!empty($refs)) {
            $routeName = getRouteName($file, $baseDir);
            $relativePath = str_replace($baseDir . '/', '', $file);
            
            $results[$relativePath] = [
                'route' => $routeName,
                'path' => $relativePath,
                'js_refs' => $refs,
            ];
        }
    }
    
    // Recurse into subdirectories
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        scanDirectory($subdir, $baseDir, $results);
    }
}

// Scan all view directories
foreach ($viewDirs as $dir) {
    if (is_dir($dir)) {
        scanDirectory($dir, $baseDir, $results);
    }
}

// Output results
$output = [
    'generated_at' => date('c'),
    'total_views_with_js' => count($results),
    'views' => $results,
];

$outputFile = $argv[1] ?? 'php://stdout';
file_put_contents($outputFile, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($outputFile !== 'php://stdout') {
    echo "Wrote " . count($results) . " views to $outputFile\n";
}
