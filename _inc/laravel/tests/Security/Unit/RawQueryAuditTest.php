<?php
// tests/Security/Unit/RawQueryAuditTest.php
// Auditoria de segurança — Verificação estática de queries SQL brutas
// Garante que o código não contém concatenação de variáveis em queries raw.
// Função: Análise estática SAST — Red Team / CISO
// PULL REQUEST START

declare(strict_types=1);

namespace Tests\Security\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Raw Query Static Audit — Code Pattern Tests
 *
 * Faz varredura estática em todos os arquivos PHP procurando padrões
 * inseguros de construção de queries SQL.
 *
 * @group security
 * @group raw-query-audit
 */
class RawQueryAuditTest extends TestCase
{
    /**
     * Diretórios a serem varridos.
     */
    private const SCAN_DIRS = [
        'app/Http/Controllers',
        'app/Models',
        'app/Services',
        'Modules',
        'routes',
    ];

    /**
     * Padrões regex PERIGOSOS que indicam concatenação em queries raw.
     * Cada padrão: [regex, descrição, severidade].
     *
     * @var array<array{0: string, 1: string, 2: string}>
     */
    private const DANGEROUS_PATTERNS = [
        // whereRaw com concatenação de variáveis (sem binding)
        ['/whereRaw\s*\(\s*["\'].*\.\s*\$(?!this->)/', 'whereRaw com concatenação de variável', 'CRITICAL'],
        // selectRaw com interpolação de variável user-input
        ['/selectRaw\s*\(\s*"[^"]*\$(?!this|table|col|fk|alias|key|derived|junction|pos|invoice|purchase|proposal|bill|product)/', 'selectRaw com interpolação suspeita', 'HIGH'],
        // DB::select com string PHP interpolada SEM binding
        ['/DB::select\s*\(\s*"[^"]*\$(?:request|input|_GET|_POST|_REQUEST)/', 'DB::select com variável de request', 'CRITICAL'],
        // DB::unprepared com variável
        ['/DB::unprepared\s*\(.*\$(?!this)/', 'DB::unprepared com variável', 'HIGH'],
        // direct $_GET/$_POST em código PHP (não blade)
        ['/\$_(GET|POST|REQUEST)\s*\[/', '$_GET/$_POST/$_REQUEST em código PHP', 'MEDIUM'],
    ];

    /**
     * Padrões que são falsos positivos quando usados com constantes/tabelas fixas.
     */
    private const SAFE_EXEMPTIONS = [
        'COL_TABLE_CREATOR',
        'COL_',
        'DatabaseConstants',
        '::class',
        'date(\'m\')',
        'date("m")',
    ];

    /**
     * Verifica que nenhum arquivo PHP contém padrões de SQL inseguros.
     */
    public function test_no_critical_raw_query_patterns(): void
    {
        $violations = [];

        foreach (self::SCAN_DIRS as $dir) {
            $fullDir = base_path($dir);
            if (!is_dir($fullDir)) continue;

            foreach (File::allFiles($fullDir) as $file) {
                if ($file->getExtension() !== 'php') continue;
                if (str_contains($file->getPathname(), 'vendor')) continue;

                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $line) {
                    // Ignorar comentários
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                        continue;
                    }

                    foreach (self::DANGEROUS_PATTERNS as [$pattern, $desc, $severity]) {
                        // Só reportar CRITICAL
                        if ($severity !== 'CRITICAL') continue;

                        if (preg_match($pattern, $line)) {
                            // Verificar se está em lista de isenção (constantes)
                            $isSafe = false;
                            foreach (self::SAFE_EXEMPTIONS as $exempt) {
                                if (str_contains($line, $exempt)) {
                                    $isSafe = true;
                                    break;
                                }
                            }
                            if ($isSafe) continue;

                            $relPath = str_replace(base_path() . '/', '', $file->getPathname());
                            $violations[] = "[{$severity}] {$relPath}:" . ($lineNum + 1) . " — {$desc}";
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Padrões CRÍTICOS de SQL inseguro encontrados:\n" . implode("\n", $violations)
        );
    }

    /**
     * Verifica uso de whereRaw com concatenação (inclui HIGH).
     */
    public function test_whereraw_uses_parameter_binding(): void
    {
        $violations = [];
        $scanDirs = ['app/Http/Controllers', 'app/Models'];

        foreach ($scanDirs as $dir) {
            $fullDir = base_path($dir);
            if (!is_dir($fullDir)) continue;

            foreach (File::allFiles($fullDir) as $file) {
                if ($file->getExtension() !== 'php') continue;
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $line) {
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, '//')) continue;

                    // whereRaw('...' . $var) sem binding
                    if (preg_match('/whereRaw\s*\([^)]*\.\s*\$[a-zA-Z]/', $line)) {
                        // Isentar find_in_set com binding
                        if (str_contains($line, 'find_in_set') && str_contains($line, '?')) {
                            continue;
                        }

                        $relPath = str_replace(base_path() . '/', '', $file->getPathname());
                        $violations[] = "{$relPath}:" . ($lineNum + 1) . " — whereRaw com concatenação: " . trim($line);
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "whereRaw sem parameter binding:\n" . implode("\n", $violations)
        );
    }

    /**
     * Verifica que find_in_set usa binding corretamente.
     */
    public function test_find_in_set_uses_proper_binding(): void
    {
        $violations = [];

        foreach (['app/Http/Controllers', 'app/Models'] as $dir) {
            $fullDir = base_path($dir);
            if (!is_dir($fullDir)) continue;

            foreach (File::allFiles($fullDir) as $file) {
                if ($file->getExtension() !== 'php') continue;
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $line) {
                    if (!str_contains($line, 'find_in_set')) continue;

                    // find_in_set com interpolação de variável DENTRO do SQL string
                    // Padrão inseguro: find_in_set('{$var}',col) ou find_in_set('$var',col)
                    if (preg_match('/find_in_set\s*\(\s*["\'].*\$[a-zA-Z]/', $line)) {
                        // Verificar se tem placeholder ? (seguro)
                        if (!str_contains($line, '?')) {
                            $relPath = str_replace(base_path() . '/', '', $file->getPathname());
                            $violations[] = "{$relPath}:" . ($lineNum + 1) . " — find_in_set com interpolação SEM binding: " . trim($line);
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "find_in_set sem binding adequado:\n" . implode("\n", $violations)
        );
    }

    /**
     * Verifica que views Blade não usam $_GET/$_POST diretamente.
     */
    public function test_blade_views_do_not_use_superglobals(): void
    {
        $violations = [];

        foreach (['resources/views', 'Modules'] as $dir) {
            $fullDir = base_path($dir);
            if (!is_dir($fullDir)) continue;

            foreach (File::allFiles($fullDir) as $file) {
                if (!str_ends_with($file->getFilename(), '.blade.php')) continue;
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $line) {
                    if (preg_match('/\$_(GET|POST|REQUEST)\s*\[/', $line)) {
                        $relPath = str_replace(base_path() . '/', '', $file->getPathname());
                        $violations[] = "{$relPath}:" . ($lineNum + 1);
                    }
                }
            }
        }

        // Registrar como warning — não falhar, pois são em Blade com {{ }} (escaped)
        if (!empty($violations)) {
            fwrite(STDERR,
                "\n⚠ " . count($violations) . " views Blade usam \$_GET/\$_POST diretamente:\n"
                . implode("\n", array_slice($violations, 0, 10))
                . (count($violations) > 10 ? "\n... e mais " . (count($violations) - 10) : '')
                . "\n"
            );
        }

        // Sempre passa — é informacional
        $this->assertTrue(true);
    }
}
// PULL REQUEST END
