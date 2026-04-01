<?php
/**
 * ▓ Roleplay: QA — Session Edge Cases ▓
 * Testa cenários de borda de sessão para validar robustez
 */
// PULL REQUEST START

$target = $argv[1] ?? 'http://127.0.0.1:8000';

echo "[QA] Session Edge Cases Tester\n";
echo "Alvo: {$target}\n";
echo str_repeat("═", 45) . "\n";

// ── Cenários de teste de sessão ──
$testCases = [
    [
        'name' => 'Sessão expirada',
        'cookie' => 'laravel_session=EXPIRED_TOKEN_12345',
        'expected' => [401, 302, 419],
    ],
    [
        'name' => 'Cookie vazio',
        'cookie' => 'laravel_session=',
        'expected' => [401, 302, 419],
    ],
    [
        'name' => 'Cookie com null bytes',
        'cookie' => "laravel_session=abc%00def",
        'expected' => [400, 401, 302, 419],
    ],
    [
        'name' => 'Cookie extremamente longo',
        'cookie' => 'laravel_session=' . str_repeat('A', 8192),
        'expected' => [400, 413, 431],
    ],
    [
        'name' => 'Múltiplos cookies de sessão',
        'cookie' => 'laravel_session=AAA; laravel_session=BBB',
        'expected' => [401, 302, 419],
    ],
    [
        'name' => 'Cookie com caracteres especiais',
        'cookie' => 'laravel_session=<script>alert(1)</script>',
        'expected' => [400, 401, 302, 419],
    ],
    [
        'name' => 'CSRF token inválido',
        'cookie' => 'XSRF-TOKEN=INVALID',
        'expected' => [419, 403, 401],
    ],
    [
        'name' => 'Sessão de outro domínio (fixation)',
        'cookie' => 'PHPSESSID=attacker_fixed_session_123',
        'expected' => [401, 302, 419],
    ],
];

$results = ['pass' => 0, 'fail' => 0, 'error' => 0];

foreach ($testCases as $tc) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $target . '/api/profile',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => [
            'Cookie: ' . $tc['cookie'],
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        $results['error']++;
        echo "  [?] {$tc['name']}: erro de conexão\n";
    } elseif (in_array($httpCode, $tc['expected'], true)) {
        $results['pass']++;
        echo "  [✓] {$tc['name']}: HTTP {$httpCode} (esperado)\n";
    } else {
        $results['fail']++;
        echo "  [✗] {$tc['name']}: HTTP {$httpCode} (esperado: " .
            implode('|', $tc['expected']) . ")\n";
    }
}

echo "\n── Resumo ──\n";
echo "  Pass: {$results['pass']}\n";
echo "  Fail: {$results['fail']}\n";
echo "  Erro: {$results['error']}\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
echo "[QA] Session Edge Cases completo\n";
// PULL REQUEST END
