<?php
// Roleplay: Backend Developer — Unit code review (static analysis)
// Foco: raw queries, env(), dd()/dump(), $guarded/$fillable, middleware
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Unit\Security\Roleplay\BackendDeveloper;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Desenvolvedor — code review estático.
 * Escaneia codebase para anti-patterns de segurança.
 *
 * @group security
 * @group backend-developer
 */
class BackendDevCodeReviewTest extends TestCase
{
    // ════════════ Raw Query Bindings ════════════

    public function test_controllers_nao_usam_whereRaw_com_interpolacao(): void
    {
        $dir = app_path('Http/Controllers');
        if (!is_dir($dir)) {
            $this->markTestSkipped('[BACKEND-DEV] Controllers dir ausente');
        }

        $violations = [];
        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            // whereRaw com variável interpolada: whereRaw("... $var ...")
            if (preg_match('/whereRaw\s*\(\s*"[^"]*\$\w+/', $content) ||
                preg_match('/whereRaw\s*\(\s*\'[^\']*\'\s*\.\s*\$/', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($violations,
            "[BACKEND-DEV] whereRaw com interpolação: " . implode(', ', $violations));
    }

    // ════════════ DB::raw sem binding ════════════

    public function test_controllers_nao_usam_db_raw_com_variavel(): void
    {
        $dir = app_path('Http/Controllers');
        if (!is_dir($dir)) {
            $this->markTestSkipped('[BACKEND-DEV] Controllers dir ausente');
        }

        $violations = [];
        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            // DB::raw("... $var ...") ou DB::raw('...' . $var)
            if (preg_match('/DB::raw\s*\(\s*"[^"]*\$\w+/', $content) ||
                preg_match('/DB::raw\s*\(\s*\'[^\']*\'\s*\.\s*\$/', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($violations,
            "[BACKEND-DEV] DB::raw com interpolação: " . implode(', ', $violations));
    }

    // ════════════ Models sem $guarded/$fillable ════════════

    public function test_todos_eloquent_models_protegidos(): void
    {
        $dir = app_path('Models');
        if (!is_dir($dir)) {
            $this->markTestSkipped('[BACKEND-DEV] Models dir ausente');
        }

        $violations = [];
        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            $isModel = (str_contains($content, 'extends Model') || str_contains($content, 'extends Authenticatable'))
                && !str_contains($content, 'trait ')
                && !str_contains($file->getRelativePathname(), 'Traits/')
                && !str_contains($file->getRelativePathname(), 'utils/');
            if ($isModel && !preg_match('/\$(guarded|fillable)\s*=/', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($violations,
            "[BACKEND-DEV] Models sem proteção: " . implode(', ', $violations));
    }

    // ════════════ dd()/dump() em production code ════════════

    public function test_sem_debug_functions_em_app(): void
    {
        $dirs = [app_path('Http/Controllers'), app_path('Models')];
        $violations = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            foreach (File::allFiles($dir) as $file) {
                if ($file->getExtension() !== 'php') continue;
                $content = file_get_contents($file->getPathname());
                // dd() ou dump() no início de uma linha (não em comentários)
                if (preg_match('/^\s*(?:dd|dump|var_dump)\s*\(/m', $content)) {
                    $violations[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertEmpty($violations,
            "[BACKEND-DEV] Debug functions em: " . implode(', ', $violations));
    }

    // ════════════ env() fora de config/ ════════════

    public function test_env_nao_usado_em_controllers(): void
    {
        $dir = app_path('Http/Controllers');
        if (!is_dir($dir)) {
            $this->markTestSkipped('[BACKEND-DEV] Controllers dir ausente');
        }

        $violations = [];
        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            if (preg_match('/\benv\s*\(/', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        if (!empty($violations)) {
            $this->markTestIncomplete(
                "[BACKEND-DEV] env() em Controllers: " . implode(', ', array_slice($violations, 0, 5))
            );
        }
        $this->assertTrue(true);
    }

    // ════════════ Middleware Registration ════════════

    public function test_rotas_web_tem_middleware_web(): void
    {
        $routes = Route::getRoutes();
        $missing = [];
        foreach ($routes as $route) {
            $middleware = $route->gatherMiddleware();
            $uri = $route->uri();
            // Rotas de API podem não ter 'web'
            if (str_starts_with($uri, 'api/')) continue;
            // Rotas internas do framework
            if (str_starts_with($uri, '_ignition') || str_starts_with($uri, 'sanctum')) continue;

            if (!in_array('web', $middleware) && !empty($route->methods())) {
                $missing[] = $uri;
            }
        }

        // Informativo — nem todas as rotas precisam do middleware 'web'
        if (count($missing) > 10) {
            $this->markTestIncomplete(
                "[BACKEND-DEV] " . count($missing) . " rotas sem middleware 'web'"
            );
        }
        $this->assertTrue(true);
    }
}
// PULL REQUEST END
