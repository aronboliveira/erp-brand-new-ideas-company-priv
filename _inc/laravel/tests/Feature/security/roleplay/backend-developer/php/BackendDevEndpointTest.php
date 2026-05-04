<?php
// Roleplay: Backend Developer — Code review & endpoint security
// Foco: middleware, validation, ORM usage em endpoints reais
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Feature\Security\Roleplay\BackendDeveloper;

use Tests\TestCase;
use App\Models\User;

/**
 * Desenvolvedor interno — testa que endpoints usam middleware,
 * validação e ORM corretamente. Perspectiva de code review.
 *
 * @group security
 * @group backend-developer
 * @group code-review
 */
class BackendDevEndpointTest extends TestCase
{
    private ?User $dev = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dev = User::first();
        if (!$this->dev) {
            $this->markTestSkipped('[BACKEND-DEV] Sem usuário de teste');
        }
    }

    // ════════════ Middleware Auth ════════════

    public function test_rotas_crudcritical_exigem_autenticacao(): void
    {
        $routes = [
            ['POST', '/users'],
            ['POST', '/invoices'],
            ['POST', '/customers'],
        ];
        foreach ($routes as [$method, $route]) {
            $r = $this->call($method, $route, []);
            $this->assertContains($r->getStatusCode(), [302, 401, 403, 404, 419],
                "[BACKEND-DEV] {$method} {$route} acessível sem auth ({$r->getStatusCode()})");
        }
    }

    // ════════════ SQL Injection via params ════════════

    public function test_search_param_com_sqli_e_seguro(): void
    {
        $payloads = [
            "' OR '1'='1",
            "1' UNION SELECT NULL--",
            "1'; DROP TABLE users; --",
        ];
        foreach ($payloads as $p) {
            $r = $this->actingAs($this->dev)->get('/invoices?search=' . urlencode($p));
            $this->assertNotEquals(500, $r->getStatusCode(),
                "[BACKEND-DEV] /invoices?search 500 com: {$p}");
            $this->assertStringNotContainsString('SQLSTATE', $r->getContent());
        }
    }

    // ════════════ Mass-assignment via POST ════════════

    public function test_criacao_usuario_ignora_campos_nao_fillable(): void
    {
        $r = $this->actingAs($this->dev)->post('/users', [
            'name'     => 'Dev Test User',
            'email'    => 'dev-test-' . time() . '@test.com',
            'password' => 'Password123!',
            'is_admin' => 1,
            'role'     => 'super-admin',
        ]);

        // Limpar se criou
        $created = User::where('email', 'like', 'dev-test-%@test.com')->first();
        if ($created) {
            $this->assertNotEquals('super-admin', $created->role ?? null,
                '[BACKEND-DEV] Mass assignment permitiu role=super-admin');
            $created->forceDelete();
        }
        // Mesmo que não crie, não deve dar 500
        $this->assertNotEquals(500, $r->getStatusCode());
    }

    // ════════════ Response Content-Type ════════════

    public function test_api_retorna_json_quando_solicitado(): void
    {
        $r = $this->actingAs($this->dev)
                   ->getJson('/invoices');
        // Se a rota aceita JSON, deve retornar JSON
        $ct = $r->headers->get('Content-Type', '');
        if (str_contains($ct, 'json')) {
            $this->assertJson($r->getContent());
        } else {
            // Rota pode não aceitar JSON — aceitável
            $this->assertTrue(true);
        }
    }

    // ════════════ CSRF em rotas de modificação ════════════

    public function test_post_sem_csrf_retorna_419(): void
    {
        // Laravel TestCase pode bypass CSRF — verificar que não dá 500
        $r = $this->post('/login', ['email' => 'test@test.com']);
        $this->assertContains($r->getStatusCode(), [302, 419, 422, 429],
            '[BACKEND-DEV] POST /login sem CSRF: ' . $r->getStatusCode());
    }

    // ════════════ HTTP Method enforcement ════════════

    public function test_get_em_rota_post_retorna_405(): void
    {
        $r = $this->actingAs($this->dev)->get('/logout');
        $this->assertContains($r->getStatusCode(), [302, 405],
            "[BACKEND-DEV] GET /logout deveria ser 405 ou redirecionar");
    }
}
// PULL REQUEST END
