#!/usr/bin/env php
<?php
/**
 * mysql_check.php — PHP-native MySQL health check for erp_prestech.
 *
 * Can be run standalone or integrated into artisan via:
 *   php utils/scripts/php/mysql_check.php [--seeds|--tables|--full]
 *
 * Also provides a programmatic check usable from tests:
 *   require_once __DIR__ . '/mysql_check.php';
 *   $report = mysql_health_report();
 */

$host   = getenv('DB_HOST') ?: '127.0.0.1';
$port   = (int)(getenv('DB_PORT') ?: 3306);
$user   = getenv('DB_USER') ?: 'admin_prestech';
$pass   = getenv('DB_PASS') ?: '76562f3A*@prestech';
$dbname = getenv('DB_NAME') ?: 'erp_prestech';

$mode = $argv[1] ?? '--full';

function mysql_health_report(): array {
    global $host, $port, $user, $pass, $dbname;

    $report = [
        'connection' => false,
        'version' => null,
        'tables' => [],
        'seeds' => [],
        'errors' => [],
    ];

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
        );
        $report['connection'] = true;
        $report['version'] = $pdo->query("SELECT VERSION()")->fetchColumn();

        // Table inventory
        $stmt = $pdo->prepare("
            SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH, ENGINE, AUTO_INCREMENT
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ");
        $stmt->execute([$dbname]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tbl = $row['TABLE_NAME'];
            // Get accurate count
            $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
            $report['tables'][$tbl] = [
                'rows' => $cnt,
                'data_bytes' => (int)$row['DATA_LENGTH'],
                'index_bytes' => (int)$row['INDEX_LENGTH'],
                'engine' => $row['ENGINE'],
                'auto_increment' => $row['AUTO_INCREMENT'],
            ];
        }

        // Migrations
        $migCount = (int)$pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        $report['migration_count'] = $migCount;

        // Seed expectations (core tables)
        $expectedSeeded = [
            'users', 'settings', 'languages', 'pipelines',
            'stages', 'project_stages', 'labels', 'sources',
        ];

        foreach ($expectedSeeded as $tbl) {
            $cnt = $report['tables'][$tbl]['rows'] ?? null;
            $report['seeds'][$tbl] = [
                'expected' => true,
                'exists' => isset($report['tables'][$tbl]),
                'rows' => $cnt ?? 0,
                'ok' => ($cnt !== null && $cnt > 0),
            ];
        }

    } catch (\Throwable $e) {
        $report['errors'][] = $e->getMessage();
    }

    return $report;
}

function print_report(array $report, string $mode): void {
    $g = "\033[32m"; $r = "\033[31m"; $y = "\033[33m"; $c = "\033[36m"; $x = "\033[0m";

    if (!$report['connection']) {
        echo "{$r}[FAIL]{$x} Cannot connect to MySQL\n";
        foreach ($report['errors'] as $err) echo "  {$err}\n";
        exit(1);
    }

    echo "\n{$c}═══ MySQL Health Report ═══{$x}\n";
    echo "{$g}[PASS]{$x} Connected — MySQL {$report['version']}\n";
    echo "{$c}[INFO]{$x} Tables: " . count($report['tables']) . "\n";
    echo "{$c}[INFO]{$x} Migrations: {$report['migration_count']}\n\n";

    if ($mode === '--tables' || $mode === '--full') {
        echo "{$c}═══ Tables ═══{$x}\n";
        printf("  %-30s %8s %10s %10s %s\n", 'Table', 'Rows', 'Data', 'Index', 'Engine');
        printf("  %-30s %8s %10s %10s %s\n", str_repeat('─', 30), str_repeat('─', 8), str_repeat('─', 10), str_repeat('─', 10), str_repeat('─', 8));
        foreach ($report['tables'] as $tbl => $info) {
            printf("  %-30s %8d %9.1fK %9.1fK %s\n",
                $tbl, $info['rows'],
                $info['data_bytes'] / 1024,
                $info['index_bytes'] / 1024,
                $info['engine']
            );
        }
        echo "\n";
    }

    if ($mode === '--seeds' || $mode === '--full') {
        echo "{$c}═══ Seed Verification ═══{$x}\n";
        $pass = $fail = 0;
        foreach ($report['seeds'] as $tbl => $info) {
            if ($info['ok']) {
                echo "  {$g}[PASS]{$x} {$tbl}: {$info['rows']} rows\n";
                $pass++;
            } elseif (!$info['exists']) {
                echo "  {$r}[FAIL]{$x} {$tbl}: TABLE MISSING\n";
                $fail++;
            } else {
                echo "  {$y}[WARN]{$x} {$tbl}: EMPTY (0 rows)\n";
                $fail++;
            }
        }
        echo "\n  Seeded: {$pass}/" . count($report['seeds']) . "\n\n";
    }

    if ($report['errors']) {
        echo "{$r}═══ Errors ═══{$x}\n";
        foreach ($report['errors'] as $err) echo "  {$r}[ERROR]{$x} {$err}\n";
    }
}

// Run if called directly
if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    $report = mysql_health_report();
    print_report($report, $mode);
    $hasFail = false;
    foreach ($report['seeds'] as $s) {
        if (!$s['ok']) { $hasFail = true; break; }
    }
    exit($hasFail ? 1 : 0);
}
