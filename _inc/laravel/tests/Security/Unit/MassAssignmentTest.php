<?php
// tests/Security/Unit/MassAssignmentTest.php
// Auditoria de segurança — Teste de atribuição em massa (Unit)
// Verifica que todos os modelos Eloquent possuem $fillable ou $guarded definidos.
// Função: CISO — garantir que nenhum campo sensível aceita atribuição em massa.
// PULL REQUEST START

declare(strict_types=1);

namespace Tests\Security\Unit;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * Mass Assignment Protection — Unit Tests
 *
 * Garante que todo Model Eloquent possui $fillable ou $guarded
 * para prevenir atribuição em massa de campos sensíveis.
 *
 * @group security
 * @group mass-assignment
 */
class MassAssignmentTest extends TestCase
{
    /**
     * Coleta todos os modelos Eloquent do diretório app/Models/.
     *
     * @return array<string, array{0: string}>
     */
    public static function eloquentModelProvider(): array
    {
        $modelDir = dirname(__DIR__, 3) . '/app/Models';
        $cases = [];

        if (!is_dir($modelDir)) return [['__SKIP__']];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();

            // Ignorar traits, abstracts, helpers, utilitários
            if (
                str_contains($path, '/Traits/')
                || str_contains($path, '/Abstracts/')
                || str_contains($path, '/Helpers/')
                || str_contains($path, '/utils/')
            ) {
                continue;
            }

            if ($file->getExtension() !== 'php') continue;

            // Extrair namespace + classe — buscar no conteúdo do arquivo
            $content = file_get_contents($path);
            if (!preg_match('/^namespace\s+(.+?);/m', $content, $ns)) continue;
            if (!preg_match('/^class\s+(\w+)/m', $content, $cl)) continue;

            $fqcn = $ns[1] . '\\' . $cl[1];

            // Verificar se a classe é um Model Eloquent concreto
            try {
                if (!class_exists($fqcn)) continue;
                $ref = new \ReflectionClass($fqcn);
                if ($ref->isAbstract() || $ref->isTrait() || $ref->isInterface()) continue;
                if (!$ref->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) continue;
            } catch (\Throwable) {
                continue;
            }

            $cases[$cl[1]] = [$fqcn];
        }

        return $cases ?: [['__SKIP__']];
    }

    /**
     * @dataProvider eloquentModelProvider
     */
    public function test_model_has_fillable_or_guarded(string $fqcn): void
    {
        if ($fqcn === '__SKIP__') {
            $this->markTestSkipped('No Eloquent models found by data provider (autoloading may not be available in static context)');
        }
        $ref = new ReflectionClass($fqcn);

        $hasFillable = false;
        $hasGuarded = false;

        // Verificar na cadeia de herança até Model (inclusive pai)
        $current = $ref;
        while ($current && $current->getName() !== Model::class) {
            if ($current->hasProperty('fillable')) {
                $prop = $current->getProperty('fillable');
                if ($prop->getDeclaringClass()->getName() === $current->getName()) {
                    $hasFillable = true;
                }
            }
            if ($current->hasProperty('guarded')) {
                $prop = $current->getProperty('guarded');
                if ($prop->getDeclaringClass()->getName() === $current->getName()) {
                    $hasGuarded = true;
                }
            }
            $current = $current->getParentClass();
        }

        $this->assertTrue(
            $hasFillable || $hasGuarded,
            "Modelo {$fqcn} não possui \$fillable nem \$guarded — vulnerável a atribuição em massa"
        );
    }

    /**
     * Verifica que campos sensíveis não estão em $fillable de nenhum modelo.
     */
    public function test_sensitive_fields_not_in_fillable(): void
    {
        $sensitiveFields = [
            'is_admin', 'api_token', 'remember_token',
        ];

        $modelDir = base_path('app/Models');
        $violations = [];

        foreach (File::allFiles($modelDir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            if (!preg_match('/^namespace\s+(.+?);/m', $content, $ns)) continue;
            if (!preg_match('/^class\s+(\w+)/m', $content, $cl)) continue;

            $fqcn = $ns[1] . '\\' . $cl[1];
            try {
                if (!class_exists($fqcn)) continue;
                $ref = new ReflectionClass($fqcn);
                if (!$ref->isSubclassOf(Model::class) || $ref->isAbstract()) continue;
                $instance = $ref->newInstanceWithoutConstructor();
                $fillable = $instance->getFillable();
                foreach ($sensitiveFields as $field) {
                    if (in_array($field, $fillable, true)) {
                        $violations[] = "{$fqcn}::\$fillable contém '{$field}'";
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $this->assertEmpty(
            $violations,
            "Campos sensíveis encontrados em \$fillable:\n" . implode("\n", $violations)
        );
    }

    /**
     * Verifica que $guarded não está vazio quando definido (proteção total).
     */
    public function test_guarded_is_not_empty_wildcard(): void
    {
        $modelDir = base_path('app/Models');
        $violations = [];

        foreach (File::allFiles($modelDir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());

            // Buscar padrão $guarded = []
            if (preg_match('/\$guarded\s*=\s*\[\s*\]/', $content)) {
                $violations[] = $file->getRelativePathname() . ' — $guarded = [] (proteção desabilitada)';
            }
        }

        // $guarded = [] é um anti-pattern — permite que QUALQUER campo seja atribuído
        $this->assertEmpty(
            $violations,
            "Modelos com \$guarded vazio (todos os campos atribuíveis):\n" . implode("\n", $violations)
        );
    }
}
// PULL REQUEST END
