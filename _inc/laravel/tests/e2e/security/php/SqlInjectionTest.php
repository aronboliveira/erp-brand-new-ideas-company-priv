<?php
// tests/Security/Feature/SqlInjectionTest.php
// Auditoria de segurança — Teste de injeção SQL (Feature)
// Simula tentativas de invasão SQL contra rotas HTTP autenticadas e públicas.
// Função: CISO / Pentester — valida que o sistema rejeita ou neutraliza payloads maliciosos.
// PULL REQUEST START

declare(strict_types=1);

namespace Tests\Security\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * SQL Injection — HTTP Feature Tests (CISO/Red Team simulation)
 *
 * Cada teste envia payloads SQL maliciosos através de parâmetros HTTP
 * e verifica que nenhum deles causa erro 500 ou vazamento de dados.
 *
 * @group security
 * @group sqli
 */
class SqlInjectionTest extends TestCase
{
    use WithFaker;

    /** Payloads clássicos de SQL injection */
    private const SQLI_PAYLOADS = [
        "' OR '1'='1",
        "' OR '1'='1' --",
        "' OR '1'='1' /*",
        "'; DROP TABLE users; --",
        "1; DROP TABLE settings; --",
        "' UNION SELECT NULL,NULL,NULL --",
        "' UNION SELECT username,password,NULL FROM users --",
        "1' AND 1=1 --",
        "1' AND 1=0 --",
        "admin'--",
        "1' ORDER BY 100 --",
        "1' WAITFOR DELAY '0:0:5' --",
        "1'; EXEC xp_cmdshell('whoami'); --",
        "' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT version())))--",
        "' AND (SELECT * FROM (SELECT COUNT(*),CONCAT((SELECT user()),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)--",
        "1' AND SLEEP(3) --",
        "1' AND BENCHMARK(5000000,SHA1('test'))--",
        "-1 OR 1=1",
        "1 AND 1=1",
        "' OR '' = '",
        "1' AND SUBSTRING(@@version,1,1)='5' --",
        "1%27%20OR%20%271%27%3D%271",
        "\\x27 OR 1=1 --",
    ];

    /** Payloads de segunda ordem ou via campos de busca */
    private const SEARCH_PAYLOADS = [
        "%' OR 1=1 --",
        "%'; DROP TABLE invoices; --",
        "%%",
        "%_%",
        "test' AND '1'='1",
        "' HAVING 1=1 --",
        "' GROUP BY 1 --",
    ];

    /** Payloads de manipulação de tipo (type juggling) */
    private const TYPE_JUGGLING = [
        '0',
        'null',
        'undefined',
        'NaN',
        'true',
        'false',
        '[]',
        '{}',
        '-1',
        '99999999999999999999',
        '0x1',
        '0e1',
    ];

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('type', 'super admin')->first()
            ?? User::first();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 1 — Login / Auth (sem autenticação)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * @dataProvider loginSqliProvider
     */
    public function test_login_rejects_sqli_in_email(string $payload): void
    {
        $response = $this->post('/login', [
            'email'    => $payload,
            'password' => $payload,
            '_token'   => csrf_token(),
        ]);

        // Deve retornar redirect (302/422) ou validação, nunca 500
        $this->assertNotEquals(
            500,
            $response->getStatusCode(),
            "Servidor retornou 500 com payload SQLi no login: {$payload}"
        );

        // Nunca deve expor informação de banco de dados na resposta
        $content = $response->getContent();
        $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $content);
        $this->assertStringNotContainsStringIgnoringCase('syntax error', $content);
        $this->assertStringNotContainsStringIgnoringCase('Undefined table', $content);
        $this->assertStringNotContainsStringIgnoringCase('mysql_', $content);
    }

    public static function loginSqliProvider(): array
    {
        $cases = [];
        foreach (self::SQLI_PAYLOADS as $i => $p) {
            $cases["login_sqli_{$i}"] = [$p];
        }
        return $cases;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 2 — Rotas GET autenticadas com parâmetros query string
    // ═══════════════════════════════════════════════════════════════════

    /**
     * @dataProvider authenticatedGetSqliProvider
     */
    public function test_authenticated_get_routes_reject_sqli(
        string $route,
        string $param,
        string $payload
    ): void {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível para teste autenticado');
        }

        $response = $this->actingAs($this->user)->get("{$route}?{$param}=" . urlencode($payload));

        $status = $response->getStatusCode();
        $this->assertNotEquals(500, $status, "500 em GET {$route}?{$param}={$payload}");

        $content = $response->getContent();
        $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $content);
        $this->assertStringNotContainsStringIgnoringCase('QueryException', $content);
    }

    public static function authenticatedGetSqliProvider(): array
    {
        $routes = [
            ['/invoices', 'search'],
            ['/invoices', 'date'],
            ['/invoices', 'customer'],
            ['/bills', 'search'],
            ['/bills', 'vendor'],
            ['/revenues', 'date'],
            ['/revenues', 'account'],
            ['/revenues', 'customer'],
            ['/revenues', 'category'],
            ['/payments', 'search'],
            ['/employees', 'branch'],
            ['/employees', 'department'],
            ['/attendances', 'month'],
            ['/attendances', 'branch'],
            ['/attendances', 'department'],
            ['/attendances', 'type'],
            ['/attendances', 'date'],
            ['/reports/leave', 'month'],
            ['/reports/leave', 'branch'],
            ['/reports/leave', 'department'],
            ['/reports/leave', 'type'],
            ['/reports/leave', 'year'],
            ['/reports/monthly-pos', 'year'],
            ['/reports/monthly-pos', 'warehouse'],
            ['/transactions', 'start_month'],
            ['/transactions', 'end_month'],
            ['/transactions', 'account'],
            ['/transactions', 'category'],
            ['/customers', 'search'],
            ['/deals', 'search'],
            ['/leads', 'search'],
            ['/home', 'search'],
        ];

        $payloads = [
            "' OR '1'='1",
            "'; DROP TABLE users;--",
            "1 UNION SELECT NULL--",
            "1' AND SLEEP(3)--",
        ];

        $cases = [];
        foreach ($routes as [$r, $p]) {
            foreach ($payloads as $i => $pl) {
                $key = str_replace(['/', '-'], '_', ltrim($r, '/')) . "_{$p}_{$i}";
                $cases[$key] = [$r, $p, $pl];
            }
        }
        return $cases;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 3 — POST com payloads SQLi em campos de formulário
    // ═══════════════════════════════════════════════════════════════════

    /**
     * @dataProvider postFormSqliProvider
     */
    public function test_post_forms_reject_sqli(string $route, array $data): void
    {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível');
        }

        $response = $this->actingAs($this->user)->post($route, array_merge($data, [
            '_token' => csrf_token(),
        ]));

        $status = $response->getStatusCode();
        $this->assertNotEquals(500, $status, "500 em POST {$route} com SQLi payload");

        $content = $response->getContent();
        $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $content);
        $this->assertStringNotContainsStringIgnoringCase('Base table or view not found', $content);
    }

    public static function postFormSqliProvider(): array
    {
        $sqli = "' OR '1'='1' --";
        return [
            'customer_store' => ['/customers', [
                'name' => $sqli, 'email' => "sqli@test.local", 'phone' => $sqli,
                'billing_name' => $sqli, 'billing_address' => $sqli,
            ]],
            'vendor_store' => ['/vendors', [
                'name' => $sqli, 'email' => "sqli@test.local", 'phone' => $sqli,
                'billing_name' => $sqli, 'billing_address' => $sqli,
            ]],
            'employee_store' => ['/employees', [
                'name' => $sqli, 'email' => "sqli@test.local",
                'phone' => $sqli, 'address' => $sqli,
            ]],
            'department_store' => ['/departments', [
                'name' => $sqli, 'branch_id' => $sqli,
            ]],
            'leave_type_store' => ['/leave_types', [
                'title' => $sqli, 'days' => $sqli,
            ]],
            'product_service_store' => ['/product_services', [
                'name' => $sqli, 'sku' => $sqli,
                'sale_price' => $sqli, 'purchase_price' => $sqli,
            ]],
            'pipeline_store' => ['/pipelines', [
                'name' => $sqli,
            ]],
            'lead_stage_store' => ['/lead_stages', [
                'name' => $sqli, 'pipeline_id' => $sqli,
            ]],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 4 — Parâmetros de rota com injeção (path traversal + SQLi)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * @dataProvider routeParamSqliProvider
     */
    public function test_route_params_reject_sqli(string $route): void
    {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível');
        }

        $response = $this->actingAs($this->user)->get($route);
        $status = $response->getStatusCode();

        // Deve retornar 404 ou redirect, nunca 500
        $this->assertNotEquals(500, $status, "500 em {$route}");

        $content = $response->getContent();
        $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $content);
    }

    public static function routeParamSqliProvider(): array
    {
        return [
            'invoice_sqli_id' => ["/invoices/' OR '1'='1"],
            'invoice_numeric_overflow' => ['/invoices/99999999999999999999999'],
            'employee_sqli_id' => ["/employees/' UNION SELECT 1--"],
            'customer_sqli_id' => ["/customers/1' AND SLEEP(5)--"],
            'deal_sqli_id' => ["/deals/' OR 1=1--"],
            'project_sqli_id' => ["/projects/' OR 1=1--"],
            'bill_sqli_id' => ["/bills/' OR '1'='1"],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 5 — Testes de Type Juggling e Bypass via tipagem
    // ═══════════════════════════════════════════════════════════════════

    /**
     * @dataProvider typeJugglingProvider
     */
    public function test_type_juggling_does_not_cause_500(string $route, string $param, string $value): void
    {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível');
        }

        $response = $this->actingAs($this->user)->get("{$route}?{$param}=" . urlencode($value));
        $this->assertNotEquals(500, $response->getStatusCode(), "Type juggling causou 500: {$route}?{$param}={$value}");
    }

    public static function typeJugglingProvider(): array
    {
        $routes = [
            ['/invoices', 'page'],
            ['/employees', 'page'],
            ['/reports/leave', 'year'],
            ['/attendances', 'month'],
        ];

        $cases = [];
        foreach ($routes as [$r, $p]) {
            foreach (self::TYPE_JUGGLING as $i => $v) {
                $key = str_replace(['/', '-'], '_', ltrim($r, '/')) . "_{$p}_{$i}";
                $cases[$key] = [$r, $p, $v];
            }
        }
        return $cases;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 6 — LIKE wildcards sem escape (bypass de filtro)
    // ═══════════════════════════════════════════════════════════════════

    public function test_search_fields_handle_like_wildcards_safely(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível');
        }

        foreach (self::SEARCH_PAYLOADS as $payload) {
            $response = $this->actingAs($this->user)->get('/invoices?search=' . urlencode($payload));
            $this->assertNotEquals(500, $response->getStatusCode(), "LIKE wildcard causou 500: {$payload}");
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 7 — Blind SQLi via headers
    // ═══════════════════════════════════════════════════════════════════

    public function test_sqli_in_headers_does_not_cause_500(): void
    {
        $sqli = "'; DROP TABLE users; --";

        $response = $this->get('/login', [
            'User-Agent' => $sqli,
            'Referer' => $sqli,
            'X-Forwarded-For' => $sqli,
            'Cookie' => "session={$sqli}",
        ]);

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SEÇÃO 8 — JSON body com SQLi (API endpoints)
    // ═══════════════════════════════════════════════════════════════════

    public function test_json_api_rejects_sqli_payloads(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('Nenhum usuário disponível');
        }

        $routes = [
            '/customers',
            '/vendors',
            '/departments',
            '/pipelines',
        ];

        foreach ($routes as $route) {
            foreach (array_slice(self::SQLI_PAYLOADS, 0, 5) as $payload) {
                $response = $this->actingAs($this->user)
                    ->postJson($route, ['name' => $payload, '_token' => csrf_token()]);

                $this->assertNotEquals(500, $response->getStatusCode(),
                    "JSON POST {$route} retornou 500 com payload: {$payload}");

                $json = $response->getContent();
                $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $json);
            }
        }
    }
}
// PULL REQUEST END
