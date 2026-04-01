<?php
/**
 * ▓ Roleplay: Backend Developer — Raw Query Pattern Detector
 * Dev backend: escaneia código-fonte para queries raw sem binding.
 * Uso: php raw_query_detector.php [diretório]
 * PULL REQUEST START
 */

$root = $argv[1] ?? realpath(__DIR__ . '/../../../../../../app');

echo "[BACKEND-DEV] Raw Query Detector v1.0\n";
echo "[BACKEND-DEV] Raiz: $root\n";
echo str_repeat("═", 50) . "\n";

if (!is_dir($root)) {
    echo "[ERRO] Diretório não encontrado: $root\n";
    exit(1);
}

// Padrões inseguros a procurar
$patterns = [
    [
        'label'    => 'DB::raw() com variável interpolada',
        'regex'    => '/DB::raw\s*\(\s*["\'].*\$\w+/',
        'severity' => 'CRITICAL',
    ],
    [
        'label'    => 'whereRaw() com variável',
        'regex'    => '/whereRaw\s*\(\s*["\'].*\$\w+/',
        'severity' => 'CRITICAL',
    ],
    [
        'label'    => 'selectRaw() com variável',
        'regex'    => '/selectRaw\s*\(\s*["\'].*\$\w+/',
        'severity' => 'HIGH',
    ],
    [
        'label'    => 'havingRaw() com variável',
        'regex'    => '/havingRaw\s*\(\s*["\'].*\$\w+/',
        'severity' => 'HIGH',
    ],
    [
        'label'    => 'orderByRaw() com variável',
        'regex'    => '/orderByRaw\s*\(\s*["\'].*\$\w+/',
        'severity' => 'MEDIUM',
    ],
    [
        'label'    => 'PDO::query() com concatenação',
        'regex'    => '/->query\s*\(\s*["\'].*\.\s*\$/',
        'severity' => 'CRITICAL',
    ],
    [
        'label'    => 'mysqli_query com variável',
        'regex'    => '/mysqli_query\s*\(.*\$/',
        'severity' => 'CRITICAL',
    ],
];

$findings = [];
$totalFiles = 0;

/**
 * Escaneia recursivamente arquivos PHP.
 */
function scanDirectory(string $dir, array $patterns, array &$findings, int &$totalFiles): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getRealPath();

        // Ignora vendor e node_modules
        if (strpos($path, '/vendor/') !== false || strpos($path, '/node_modules/') !== false) {
            continue;
        }

        $totalFiles++;
        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        foreach ($patterns as $pattern) {
            foreach ($lines as $lineNum => $line) {
                if (preg_match($pattern['regex'], $line)) {
                    $findings[] = [
                        'file'     => $path,
                        'line'     => $lineNum + 1,
                        'pattern'  => $pattern['label'],
                        'severity' => $pattern['severity'],
                        'code'     => trim(substr($line, 0, 120)),
                    ];
                }
            }
        }
    }
}

scanDirectory($root, $patterns, $findings, $totalFiles);

echo "[BACKEND-DEV] $totalFiles arquivos PHP escaneados\n";

if (empty($findings)) {
    echo "\n  [✓] Nenhum padrão inseguro encontrado\n";
} else {
    echo "\n  [✗] " . count($findings) . " padrões inseguros:\n";
    foreach ($findings as $f) {
        $relPath = str_replace($root . '/', '', $f['file']);
        echo "    [{$f['severity']}] {$relPath}:{$f['line']}\n";
        echo "      {$f['pattern']}\n";
        echo "      " . substr($f['code'], 0, 100) . "\n";
    }
}

echo "\n" . str_repeat("═", 50) . "\n";
echo "[BACKEND-DEV] Total: " . count($findings) . " findings em $totalFiles arquivos\n";

$reportPath = '/tmp/backend-dev-raw-query.json';
file_put_contents($reportPath, json_encode([
    'actor'    => 'backend-dev',
    'tool'     => 'raw_query_detector',
    'root'     => $root,
    'files'    => $totalFiles,
    'findings' => $findings,
], JSON_PRETTY_PRINT));
echo "[BACKEND-DEV] Relatório: $reportPath\n";
// PULL REQUEST END
