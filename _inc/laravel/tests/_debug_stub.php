<?php
// Debug script: verify what the TestCase guard would write
$chatifyBase = dirname(__DIR__) . '/vendor/munafio/chatify/src';
$chatifyProvider = $chatifyBase . '/ChatifyServiceProvider.php';

echo "Provider path: $chatifyProvider\n";
echo "Exists: " . (file_exists($chatifyProvider) ? 'yes' : 'no') . "\n";

// Read the guard heredoc from TestCase.php
$tc = file_get_contents(__DIR__ . '/TestCase.php');

// Count heredocs
$count = substr_count($tc, "<<<'STUB'");
echo "Heredoc count: $count\n";

// Find the first heredoc after 'chatifyProvider'
$providerPos = strpos($tc, '$chatifyProvider');
echo "chatifyProvider var at pos: $providerPos\n";

// Find the put_contents call
$putPos = strpos($tc, 'file_put_contents($chatifyProvider', $providerPos);
echo "file_put_contents at pos: $putPos\n";

// Extract the heredoc
$heredocStart = strpos($tc, "<<<'STUB'", $putPos);
$contentStart = strpos($tc, "\n", $heredocStart) + 1;
$heredocEnd = strpos($tc, "\nSTUB)", $contentStart);
echo "Heredoc content offset: $contentStart to $heredocEnd\n";

$content = substr($tc, $contentStart, $heredocEnd - $contentStart);
echo "Content length: " . strlen($content) . "\n";
echo "=== START CONTENT ===\n";
echo $content;
echo "\n=== END CONTENT ===\n";

// Check if it contains register()
echo "Contains 'register': " . (str_contains($content, 'register') ? 'YES' : 'NO') . "\n";
echo "Contains 'loadRoutesFrom': " . (str_contains($content, 'loadRoutesFrom') ? 'YES' : 'NO') . "\n";
