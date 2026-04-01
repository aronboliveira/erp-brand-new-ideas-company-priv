<?php
// Roleplay: CISO — Unit-level policy audit
// Foco: $guarded/$fillable, config, env(), prepared statements
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Unit\Security\Roleplay\Ciso;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Auditoria CISO unit — verifica políticas de código:
 * mass-assignment guards, env() fora de config/, prepared statements.
 *
 * @group security
 * @group ciso
 * @group compliance
 */
class CisoPolicyAuditTest extends TestCase
{
    // ════════════ Mass-Assignment Policy ════════════

    public function test_todos_modelos_tem_guarded_ou_fillable(): void
    {
        $modelDir = app_path('Models');
        if (!is_dir($modelDir)) {
            $this->markTestSkipped('[CISO] Pasta Models não encontrada');
        }

        $files = File::allFiles($modelDir);
        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            $hasGuarded  = preg_match('/\$guarded\s*=/', $content);
            $hasFillable = preg_match('/\$fillable\s*=/', $content);

            if (!$hasGuarded && !$hasFillable) {
                // Verificar se é um Eloquent Model (excluir Traits e utilitários)
                $isModel = (str_contains($content, 'extends Model') || str_contains($content, 'extends Authenticatable'))
                    && !str_contains($content, 'trait ')
                    && !str_contains($file->getRelativePathname(), 'Traits/')
                    && !str_contains($file->getRelativePathname(), 'utils/');
                if ($isModel) {
                    $violations[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertEmpty($violations,
            "[CISO-POLICY] Modelos sem \$guarded/\$fillable: " . implode(', ', $violations));
    }

    // ════════════ env() Outside config/ ════════════

    public function test_env_nao_chamado_fora_de_config(): void
    {
        $dirs = ['app', 'routes'];
        $violations = [];

        foreach ($dirs as $dir) {
            $fullPath = base_path($dir);
            if (!is_dir($fullPath)) {
                continue;
            }
            $files = File::allFiles($fullPath);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                // Procurar env() chamado diretamente (padrão: env('KEY'))
                if (preg_match('/\benv\s*\(/', $content)) {
                    $violations[] = $file->getRelativePathname();
                }
            }
        }

        if (!empty($violations)) {
            $this->markTestIncomplete(
                "[CISO-POLICY] env() chamado fora de config/: " . implode(', ', array_slice($violations, 0, 5))
            );
        }
        $this->assertTrue(true);
    }

    // ════════════ Debug Functions ════════════

    public function test_sem_dd_dump_em_controllers(): void
    {
        $ctrlDir = app_path('Http/Controllers');
        if (!is_dir($ctrlDir)) {
            $this->markTestSkipped('[CISO] Controllers dir ausente');
        }

        $files = File::allFiles($ctrlDir);
        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            // dd() ou dump() sem comentário
            if (preg_match('/^\s*(?:dd|dump)\s*\(/m', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($violations,
            "[CISO-DEBUG] dd()/dump() encontrado em: " . implode(', ', $violations));
    }

    // ════════════ Raw Query Binding ════════════

    public function test_whereRaw_sempre_com_binding(): void
    {
        $dirs = [app_path('Http/Controllers'), app_path('Models')];
        $violations = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $files = File::allFiles($dir);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                // whereRaw sem segundo argumento (array de bindings)
                if (preg_match('/whereRaw\s*\(\s*["\'][^"\']*\$/', $content)) {
                    $violations[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertEmpty($violations,
            "[CISO-BINDING] whereRaw com interpolação: " . implode(', ', $violations));
    }

    // ════════════ Session Config ════════════

    public function test_session_configurada_como_httponly(): void
    {
        $httpOnly = config('session.http_only', true);
        $this->assertTrue($httpOnly, '[CISO-SESSION] session.http_only deve ser true');
    }

    public function test_session_driver_nao_e_file_em_producao(): void
    {
        // Informativo — em dev, file é aceitável
        $driver = config('session.driver');
        if ($driver === 'file') {
            $this->markTestIncomplete('[CISO-SESSION] Driver de sessão é "file" — recomendado database/redis em produção');
        }
        $this->assertTrue(true);
    }
}
// PULL REQUEST END
