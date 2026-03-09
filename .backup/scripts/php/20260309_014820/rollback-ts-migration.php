#!/usr/bin/env php
<?php
/**
 * rollback-ts-migration.php — Revert TypeScript migration changes.
 *
 * Usage:
 *   php .backup/scripts/php/20260309_014820/rollback-ts-migration.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv);

function logMsg(string $msg): void {
    echo "[rollback] {$msg}\n";
}

function findWorkspace(): string {
    $dir = __DIR__;
    for ($i = 0; $i < 10; $i++) {
        if (is_dir($dir . '/_inc')) {
            return $dir;
        }
        $dir = dirname($dir);
    }
    throw new RuntimeException('Cannot find workspace root');
}

function rmDirRecursive(string $dir): void {
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }
    rmdir($dir);
}

function findLangFiles(string $dir): array {
    $result = [];
    if (!is_dir($dir)) return $result;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if (preg_match('#/lang/[^/]+\.ts$#', $path)) {
            $result[] = $path;
        }
    }
    return $result;
}

// Main
$workspace = findWorkspace();
$tsDir = $workspace . '/_inc/laravel/ts';

logMsg("Workspace: {$workspace}");
logMsg("TS dir:    {$tsDir}");
if ($dryRun) logMsg("** DRY RUN — no changes will be made **");

$removed = 0;

// 1. dist/ and dist-iife/
foreach (['dist', 'dist-iife'] as $d) {
    $target = "{$tsDir}/{$d}";
    if (is_dir($target)) {
        logMsg("Removing {$d}/...");
        if (!$dryRun) rmDirRecursive($target);
        $removed++;
    }
}

// 2. Lang TS files
$routesDir = "{$tsDir}/src/public/assets/js/routes";
$langFiles = findLangFiles($routesDir);
logMsg("Found " . count($langFiles) . " lang TS files");
if (!$dryRun) {
    foreach ($langFiles as $f) {
        unlink($f);
    }
}
$removed += count($langFiles);

// 3. Core singletons
$coreDir = "{$tsDir}/src/public/assets/js/core";
if (is_dir($coreDir)) {
    logMsg("Removing core TS singletons...");
    if (!$dryRun) rmDirRecursive($coreDir);
    $removed++;
}

// 4. Integration tests
$integDir = "{$tsDir}/tests/integration";
if (is_dir($integDir)) {
    logMsg("Removing integration tests...");
    if (!$dryRun) rmDirRecursive($integDir);
    $removed++;
}

// 5. ESM→IIFE script
$esmScript = "{$tsDir}/scripts/esm-to-iife.cjs";
if (is_file($esmScript)) {
    logMsg("Removing ESM→IIFE script...");
    if (!$dryRun) unlink($esmScript);
    $removed++;
}

logMsg("");
logMsg("Rollback complete. {$removed} items removed.");
logMsg("Original JS files in public/assets/js/routes/ are untouched.");
