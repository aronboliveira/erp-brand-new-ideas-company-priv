<?php
// Roleplay: QA Tester — Client-side edge cases & form validation
// Foco: inputs inesperados, caracteres especiais, campos vazios, UX
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Feature\Security\Roleplay\Qa;

use Tests\TestCase;
use App\Models\User;

/**
 * QA Tester — testa como um usuário final persistente.
 * Usa inputs inesperados, edge cases, caracteres especiais.
 * Verifica que mensagens de erro são amigáveis (sem stack trace).
 *
 * @group security
 * @group qa
 * @group edge-cases
 */
class QaEdgeCaseTest extends TestCase
{
    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        if (!$this->user) {
            $this->markTestSkipped('[QA] Sem usuário de teste');
        }
    }

    // ════════════ Caracteres especiais em nomes ════════════

    /** @dataProvider specialNamePayloads */
    public function test_busca_com_nome_especial_nao_crashou(string $input): void
    {
        $r = $this->actingAs($this->user)->get('/invoices?search=' . urlencode($input));
        $this->assertNotEquals(500, $r->getStatusCode(),
            "[QA] Busca crashou com: {$input}");
    }

    /** @return array<string, array{0: string}> */
    public static function specialNamePayloads(): array
    {
        return [
            'apostrofo_nome'  => ["O'Brien"],
            'unicode_japones' => ['名前テスト'],
            'unicode_arabe'   => ['اسم'],
            'acentos_pt'      => ['José María Ñoño'],
            'emoji'           => ['Teste 🎉👍'],
            'tags_html'       => ['<b>negrito</b>'],
            'script_xss'      => ['<script>alert(1)</script>'],
            'backslash'       => ['test\\path\\file'],
            'null_byte'       => ["test\x00end"],
            'tabs_newlines'   => ["test\ttab\nnewline"],
        ];
    }

    // ════════════ Campos vazios / somente espaços ════════════

    public function test_login_com_campos_vazios(): void
    {
        $r = $this->post('/login', [
            'email'    => '',
            'password' => '',
        ]);
        $this->assertContains($r->getStatusCode(), [302, 419, 422],
            "[QA] Login com campos vazios retornou {$r->getStatusCode()}");
    }

    public function test_login_com_somente_espacos(): void
    {
        $r = $this->post('/login', [
            'email'    => '   ',
            'password' => '   ',
        ]);
        $this->assertContains($r->getStatusCode(), [302, 419, 422],
            "[QA] Login com espaços retornou {$r->getStatusCode()}");
    }

    // ════════════ Input muito longo ════════════

    public function test_busca_com_input_gigante(): void
    {
        $longInput = str_repeat('a', 5000);
        $r = $this->actingAs($this->user)->get('/invoices?search=' . $longInput);
        $this->assertNotEquals(500, $r->getStatusCode(),
            "[QA] Input de 5000 chars causou 500");
    }

    public function test_login_com_email_gigante(): void
    {
        $r = $this->post('/login', [
            'email'    => str_repeat('a', 2000) . '@test.com',
            'password' => str_repeat('b', 2000),
        ]);
        $this->assertNotEquals(500, $r->getStatusCode(),
            "[QA] Login com email gigante causou 500");
    }

    // ════════════ Formulários repetidos (duplo submit) ════════════

    public function test_duplo_submit_login_nao_crashou(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $r = $this->post('/login', [
                'email'    => 'nonexistent@test.com',
                'password' => 'wrongpassword',
            ]);
            $this->assertNotEquals(500, $r->getStatusCode(),
                "[QA] Duplo submit #{$i} causou 500");
        }
    }

    // ════════════ URL Manipulation ════════════

    public function test_url_com_parametros_extras(): void
    {
        $r = $this->actingAs($this->user)
                   ->get('/invoices?search=test&admin=1&debug=true&role=super');
        $this->assertNotEquals(500, $r->getStatusCode(),
            "[QA] Parâmetros extras causaram 500");
    }

    public function test_url_com_path_traversal(): void
    {
        $r = $this->actingAs($this->user)->get('/invoices/../../../etc/passwd');
        $this->assertNotEquals(500, $r->getStatusCode());
        $this->assertStringNotContainsString('root:x', $r->getContent());
    }

    // ════════════ Mensagens de erro amigáveis ════════════

    public function test_erro_404_nao_mostra_stack_trace(): void
    {
        $r = $this->actingAs($this->user)->get('/pagina-que-nao-existe-qa');
        $content = $r->getContent();
        $this->assertStringNotContainsString('vendor/', $content,
            "[QA] Stack trace visível na 404");
        $this->assertStringNotContainsString('SQLSTATE', $content,
            "[QA] Erro SQL visível na 404");
    }

    // ════════════ Content-Type abuse ════════════

    public function test_login_com_content_type_json(): void
    {
        $r = $this->postJson('/login', [
            'email'    => 'test@test.com',
            'password' => 'password',
        ]);
        // Não deve causar 500 mesmo com JSON em endpoint de form
        $this->assertNotEquals(500, $r->getStatusCode());
    }
}
// PULL REQUEST END
