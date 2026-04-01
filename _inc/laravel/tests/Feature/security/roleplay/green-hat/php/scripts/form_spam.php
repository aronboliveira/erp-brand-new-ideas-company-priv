<?php
/**
 * ▓ Roleplay: Green Hat — Form Spam Bot ▓
 * Simula envio massivo de formulários com dados falsos.
 * Técnica ingênua que servidores bem configurados bloqueiam facilmente.
 */
// PULL REQUEST START

$target = $argv[1] ?? 'http://127.0.0.1:8000';

echo "[GREEN-HAT] Form Spam Bot\n";
echo "Alvo: {$target}\n";
echo str_repeat("═", 40) . "\n";

// Dados de spam
$spamData = [
    ['name' => 'Buy Cheap V1agra', 'email' => 'spam@bot.test', 'message' => 'Click here for free stuff!!!'],
    ['name' => 'WINNER!!!', 'email' => 'scam@test.test', 'message' => 'You won $1,000,000'],
    ['name' => 'TestBot', 'email' => 'bot@bot.bot', 'message' => str_repeat('SPAM ', 100)],
    ['name' => '<script>alert(1)</script>', 'email' => 'xss@test.com', 'message' => '<img src=x onerror=alert(1)>'],
    ['name' => "' OR '1'='1", 'email' => 'sqli@test.com', 'message' => "'; DROP TABLE messages;--"],
];

$endpoints = ['/contact', '/api/messages', '/api/feedback', '/comments'];

$results = ['accepted' => 0, 'rejected' => 0, 'error' => 0];

foreach ($spamData as $data) {
    foreach ($endpoints as $ep) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $target . $ep,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'User-Agent: SpamBot/1.0',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 0) {
            $results['error']++;
        } elseif ($httpCode >= 400) {
            $results['rejected']++;
            echo "  [✓] Rejeitado: {$ep} ({$data['name']}) -> HTTP {$httpCode}\n";
        } else {
            $results['accepted']++;
            echo "  [!] Aceito: {$ep} ({$data['name']}) -> HTTP {$httpCode}\n";
        }
    }
}

echo "\n── Resumo ──\n";
echo "  Aceitos: {$results['accepted']}\n";
echo "  Rejeitados: {$results['rejected']}\n";
echo "  Erros: {$results['error']}\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
echo "[GREEN-HAT] Form Spam completo\n";
// PULL REQUEST END
