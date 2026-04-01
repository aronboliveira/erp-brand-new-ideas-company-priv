<?php
/**
 * ▓ Roleplay: CISO — CSRF Protection Audit
 * Auditor executivo: valida que toda rota POST/PUT/DELETE tem proteção CSRF.
 * Uso: php csrf_audit.php [URL]
 * PULL REQUEST START
 */

$server = $argv[1] ?? getenv('APP_URL') ?: 'http://127.0.0.1:8000';

echo "[CISO] CSRF Protection Audit v1.0\n";
echo "[CISO] Alvo: $server\n";
echo str_repeat("═", 50) . "\n";

// Rotas que devem ter proteção CSRF
$routes = [
    ['POST',   '/login'],
    ['POST',   '/register'],
    ['POST',   '/logout'],
    ['POST',   '/invoices'],
    ['PUT',    '/invoices/1'],
    ['DELETE', '/invoices/1'],
    ['POST',   '/customers'],
    ['PUT',    '/customers/1'],
    ['DELETE', '/customers/1'],
    ['POST',   '/proposals'],
    ['POST',   '/settings'],
];

$pass = 0;
$fail = 0;
$results = [];

foreach ($routes as [$method, $path]) {
    $url = "$server$path";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => 'test=x',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html',
        ],
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 419 = CSRF token mismatch (Laravel)
    // 302 = redirect para login (auth middleware antes do CSRF)
    // 405 = Method not allowed
    $protected = in_array($code, [419, 302, 405, 403]);

    if ($protected) {
        $pass++;
        echo "  [✓] $method $path → $code (PROTEGIDO)\n";
    } else {
        $fail++;
        echo "  [✗] $method $path → $code (SEM CSRF)\n";
    }

    $results[] = [
        'method'    => $method,
        'path'      => $path,
        'status'    => $code,
        'protected' => $protected,
    ];
}

echo "\n" . str_repeat("═", 50) . "\n";
$total = $pass + $fail;
$score = $total > 0 ? intval($pass * 100 / $total) : 0;
echo "[CISO] $pass/$total rotas protegidas ($score%)\n";

$reportPath = '/tmp/ciso-csrf-audit.json';
file_put_contents($reportPath, json_encode([
    'actor'   => 'ciso',
    'tool'    => 'csrf_audit',
    'target'  => $server,
    'pass'    => $pass,
    'fail'    => $fail,
    'score'   => $score,
    'results' => $results,
], JSON_PRETTY_PRINT));
echo "[CISO] Relatório: $reportPath\n";
// PULL REQUEST END
