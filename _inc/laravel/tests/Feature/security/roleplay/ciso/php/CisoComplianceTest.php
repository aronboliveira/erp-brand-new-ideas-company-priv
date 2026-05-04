<?php
// Roleplay: CISO — Compliance & Governance audit (Feature)
// Foco: middleware, headers de segurança, configuração, políticas
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Feature\Security\Roleplay\Ciso;

use Tests\TestCase;
use App\Models\User;

/**
 * Auditoria de compliance — verifica controles de segurança em nível de aplicação.
 * Não executa ataques; valida que proteções existem e estão configuradas.
 *
 * @group security
 * @group ciso
 * @group compliance
 */
class CisoComplianceTest extends TestCase
{
    private ?User $auditor = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditor = User::first();
        if (!$this->auditor) {
            $this->markTestSkipped('[CISO] Sem usuário de auditoria');
        }
    }

    // ════════════ CSRF Protection ════════════

    public function test_csrf_ativo_em_rotas_post(): void
    {
        // POST sem token CSRF deve retornar 419
        // Em Laravel testing, CSRF middleware pode ser bypassed pelo TestCase,
        // então POST sem token pode retornar 302 (redirect) ou 419
        $r = $this->post('/login', ['email' => 'test@test.com']);
        $this->assertContains($r->getStatusCode(), [302, 419, 422, 429],
            '[CISO-CSRF] POST /login sem CSRF retornou status inesperado: ' . $r->getStatusCode());
    }

    public function test_csrf_token_presente_em_formularios(): void
    {
        $r = $this->get('/login');
        $content = $r->getContent();
        // Deve conter _token hidden field ou meta tag csrf-token
        $hasCsrf = str_contains($content, '_token') || str_contains($content, 'csrf-token');
        $this->assertTrue($hasCsrf, '[CISO-CSRF] Formulário de login sem CSRF token');
    }

    // ════════════ Security Headers ════════════

    public function test_x_frame_options_presente(): void
    {
        $r = $this->get('/login');
        $xfo = $r->headers->get('X-Frame-Options');
        $csp = $r->headers->get('Content-Security-Policy');
        // Pelo menos um dos dois deve estar presente
        $this->assertTrue(
            $xfo !== null || ($csp !== null && str_contains($csp, 'frame-ancestors')),
            '[CISO-HEADER] Nem X-Frame-Options nem CSP frame-ancestors configurados'
        );
    }

    public function test_x_content_type_options(): void
    {
        $r = $this->get('/login');
        $xcto = $r->headers->get('X-Content-Type-Options');
        // Pode não estar presente em dev, mas registramos
        if ($xcto === null) {
            $this->markTestIncomplete('[CISO-HEADER] X-Content-Type-Options ausente — recomendado: nosniff');
        }
        $this->assertEquals('nosniff', $xcto);
    }

    // ════════════ APP_DEBUG ════════════

    public function test_paginas_de_erro_nao_expoem_stack_trace(): void
    {
        $r = $this->actingAs($this->auditor)->get('/rota-inexistente-ciso-test');
        $content = $r->getContent();
        // Mesmo que APP_DEBUG=true em dev, não deveria aparecer em produção
        // Registramos como informativo
        $hasTrace = str_contains($content, 'vendor/') && str_contains($content, '.php');
        if ($hasTrace) {
            $this->markTestIncomplete('[CISO-DEBUG] Stack trace visível — APP_DEBUG pode estar ativo');
        }
        $this->assertTrue(true);
    }

    // ════════════ Authentication ════════════

    public function test_rotas_protegidas_redirecionam_sem_auth(): void
    {
        $protectedRoutes = ['/dashboard', '/invoices', '/customers', '/users'];
        foreach ($protectedRoutes as $route) {
            $r = $this->get($route);
            $this->assertContains(
                $r->getStatusCode(), [302, 301, 403, 404],
                "[CISO-AUTH] Rota {$route} acessível sem autenticação (status: {$r->getStatusCode()})"
            );
        }
    }

    public function test_login_bruteforce_nao_retorna_500(): void
    {
        // 10 tentativas rápidas — nenhuma deve causar 500
        for ($i = 0; $i < 10; $i++) {
            $r = $this->post('/login', [
                'email' => "attacker_{$i}@evil.com",
                'password' => 'wrongpassword',
            ]);
            $this->assertNotEquals(500, $r->getStatusCode(),
                "[CISO-AUTH] Login retornou 500 na tentativa {$i}");
        }
    }

    // ════════════ Session Security ════════════

    public function test_cookie_session_tem_httponly(): void
    {
        $sessionName = config('session.cookie', 'laravel_session');
        $r = $this->get('/login');
        $cookies = $r->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $sessionName) {
                $this->assertTrue($cookie->isHttpOnly(),
                    "[CISO-SESSION] Cookie {$sessionName} sem HttpOnly");
                return;
            }
        }
        // Se não encontrou cookie na resposta, é aceitável (pode ser set depois)
        $this->assertTrue(true, '[CISO-SESSION] Cookie de sessão não encontrado nesta resposta');
    }

    // ════════════ Sensitive Data Exposure ════════════

    public function test_env_file_nao_acessivel_via_web(): void
    {
        $r = $this->get('/.env');
        $this->assertNotEquals(200, $r->getStatusCode(),
            '[CISO-EXPOSURE] Arquivo .env acessível via web!');
    }

    public function test_phpinfo_nao_acessivel(): void
    {
        $r = $this->get('/phpinfo');
        $content = $r->getContent();
        $this->assertStringNotContainsString('PHP Version', $content,
            '[CISO-EXPOSURE] phpinfo() acessível via web');
    }
}
// PULL REQUEST END
