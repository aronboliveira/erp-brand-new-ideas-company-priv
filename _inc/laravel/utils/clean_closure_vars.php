<?php
/**
 * Remove unused variables from closure use() clauses in controller files.
 *
 * Scans for closures that capture profiling variables ($method, $class, $cls,
 * $base, $meth, $func) but never reference them inside the closure body.
 *
 * Usage: php utils/clean_closure_vars.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv ?? []);
$baseDir = __DIR__ . '/../app/Http/Controllers';

// Variables that are commonly unused in closures after measureProfile refactor
$profilingVars = ['method', 'class', 'cls', 'base', 'meth', 'func'];

$filesModified = 0;
$totalCleaned = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

/**
 * Find the matching closing brace for a closure.
 */
function extractClosureBody(string $content, int $openBracePos): ?string
{
    $depth = 1;
    $len = strlen($content);
    $i = $openBracePos + 1;
    $inString = false;
    $stringChar = '';

    while ($i < $len && $depth > 0) {
        $ch = $content[$i];
        if ($inString) {
            if ($ch === '\\') { $i += 2; continue; }
            if ($ch === $stringChar) { $inString = false; }
        } else {
            if ($ch === '"' || $ch === "'") { $inString = true; $stringChar = $ch; }
            elseif ($ch === '{') { $depth++; }
            elseif ($ch === '}') { $depth--; }
        }
        $i++;
    }

    if ($depth === 0) {
        return substr($content, $openBracePos + 1, $i - $openBracePos - 2);
    }
    return null;
}

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') continue;
    if (str_contains($fileInfo->getPathname(), '_DEPRECATED')) continue;

    $content = file_get_contents($fileInfo->getPathname());
    $original = $content;
    $cleaned = 0;

    $pattern = '/function\s*\([^)]*\)\s*use\s*\(([^)]+)\)\s*\{/s';

    if (preg_match_all($pattern, $content, $allMatches, PREG_OFFSET_CAPTURE)) {
        $matchList = [];
        for ($m = 0; $m < count($allMatches[0]); $m++) {
            $matchList[] = [
                'full' => $allMatches[0][$m][0],
                'offset' => $allMatches[0][$m][1],
                'useClause' => $allMatches[1][$m][0],
            ];
        }

        // Process in reverse to preserve offsets
        $matchList = array_reverse($matchList);

        foreach ($matchList as $matchInfo) {
            $useClause = $matchInfo['useClause'];
            $fullMatch = $matchInfo['full'];
            $offset = $matchInfo['offset'];
            $bracePos = $offset + strlen($fullMatch) - 1;

            $body = extractClosureBody($content, $bracePos);
            if ($body === null) continue;

            $vars = array_map('trim', explode(',', $useClause));
            $removedIndices = [];

            foreach ($vars as $i => $var) {
                if (preg_match('/\$(\w+)/', $var, $vm)) {
                    $varName = $vm[1];
                    if (in_array($varName, $profilingVars)) {
                        // Check if var is used in closure body
                        if (!preg_match('/\$' . preg_quote($varName, '/') . '\b/', $body)) {
                            $removedIndices[] = $i;
                        }
                    }
                }
            }

            if (empty($removedIndices)) continue;

            $newVars = [];
            foreach ($vars as $i => $var) {
                if (!in_array($i, $removedIndices)) {
                    $newVars[] = trim($var);
                }
            }

            $cleaned += count($removedIndices);

            if (empty($newVars)) {
                $newMatch = preg_replace('/\s*use\s*\([^)]+\)/', '', $fullMatch);
            } else {
                $newUseStr = implode(', ', $newVars);
                $newMatch = preg_replace('/use\s*\([^)]+\)/', "use ({$newUseStr})", $fullMatch);
            }

            $content = substr_replace($content, $newMatch, $offset, strlen($fullMatch));
        }
    }

    if ($content !== $original) {
        $relPath = str_replace(__DIR__ . '/../', '', $fileInfo->getPathname());
        if ($dryRun) {
            echo "  DRY-RUN: {$relPath} — {$cleaned} vars removed\n";
        } else {
            file_put_contents($fileInfo->getPathname(), $content);
            echo "  UPDATED: {$relPath} — {$cleaned} vars removed\n";
        }
        $filesModified++;
        $totalCleaned += $cleaned;
    }
}

echo "\n{$filesModified} files " . ($dryRun ? "would be " : "") . "modified, {$totalCleaned} closure vars " . ($dryRun ? "would be " : "") . "removed\n";
