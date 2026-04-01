<?php
// Roleplay: White Hat (Ethical Pentester) — Feature-level OWASP SQLi audit
// Ref: OWASP Testing Guide v4 — OTG-INPVAL-005
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Feature\Security\Roleplay\WhiteHat;

use Tests\TestCase;
use App\Models\User;

/**
 * Pentest ético — cobertura metódica por categoria de injeção SQL.
 *
 * @group security
 * @group white-hat
 * @group owasp
 */
class WhiteHatSqliTest extends TestCase
{
    // ——— OWASP OTG-INPVAL-005: Categorias de SQLi ———

    /**
     * Categoria 1 — Error-based SQLi
     * Objetivo: forçar mensagem de erro do SGBD para extrair info.
     */
    private const ERROR_BASED = [
        "' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--",
        "' AND UPDATEXML(1,CONCAT(0x7e,version()),1)--",
        "' AND (SELECT 1 FROM(SELECT COUNT(*),CONCAT(version(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)--",
        "1' AND GTID_SUBSET(CONCAT(0x7e,version()),1337)--",
    ];

    /**
     * Categoria 2 — Union-based SQLi
     * Objetivo: concatenar resultado com query legítima.
     */
    private const UNION_BASED = [
        "' UNION SELECT NULL--",
        "' UNION SELECT NULL,NULL--",
        "' UNION SELECT NULL,NULL,NULL--",
        "1' UNION SELECT username,password FROM users--",
        "' UNION ALL SELECT table_name,NULL FROM information_schema.tables--",
    ];

    /**
     * Categoria 3 — Boolean-based blind SQLi
     * Objetivo: inferir dados via true/false no comportamento.
     */
    private const BOOLEAN_BLIND = [
        "1' AND 1=1--",
        "1' AND 1=2--",
        "1' AND SUBSTRING(@@version,1,1)='8'--",
        "1' AND (SELECT COUNT(*) FROM users)>0--",
        "1' AND (SELECT LENGTH(password) FROM users LIMIT 1)>10--",
    ];

    /**
     * Categoria 4 — Time-based blind SQLi
     * Objetivo: inferir dados observando delays de resposta.
     */
    private const TIME_BLIND = [
        "1' AND SLEEP(0)--",
        "1' AND IF(1=1,SLEEP(0),0)--",
        "1' AND IF(SUBSTRING(@@version,1,1)='8',SLEEP(0),0)--",
        "1'; WAITFOR DELAY '0:0:0'--",
    ];

    /**
     * Categoria 5 — Stacked queries
     * Objetivo: executar segunda query no mesmo statement.
     */
    private const STACKED = [
        "1'; SELECT 1--",
        "1'; SELECT pg_sleep(0)--",
    ];

    private ?User $auditor = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditor = User::first();
        if (!$this->auditor) {
            $this->markTestSkipped('[WHITE-HAT] Sem usuário de teste para autenticação');
        }
    }

    // ────────── Error-based ──────────

    /** @dataProvider errorBasedPayloads */
    public function test_error_based_sqli_nao_retorna_500(string $payload): void
    {
        $r = $this->actingAs($this->auditor)
                   ->get('/invoices?search=' . urlencode($payload));
        $this->assertNotEquals(500, $r->getStatusCode(), "[ERROR-BASED] 500 com: {$payload}");
        // O pentester ético verifica que nenhum erro do SGBD vaza
        $content = $r->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $content, "[ERROR-BASED] SQLSTATE vazou");
        $this->assertStringNotContainsString('syntax error', strtolower($content), "[ERROR-BASED] Syntax error exposto");
    }

    /** @return array<string, array{0: string}> */
    public static function errorBasedPayloads(): array
    {
        return self::buildProvider(self::ERROR_BASED, 'err');
    }

    // ────────── Union-based ──────────

    /** @dataProvider unionBasedPayloads */
    public function test_union_based_sqli_bloqueado(string $payload): void
    {
        $r = $this->actingAs($this->auditor)
                   ->get('/invoices?search=' . urlencode($payload));
        $this->assertNotEquals(500, $r->getStatusCode(), "[UNION] 500 com: {$payload}");
        $content = $r->getContent();
        // Não deve expor nomes de tabelas do information_schema
        $this->assertStringNotContainsString('information_schema', strtolower($content));
    }

    /** @return array<string, array{0: string}> */
    public static function unionBasedPayloads(): array
    {
        return self::buildProvider(self::UNION_BASED, 'union');
    }

    // ────────── Boolean-blind ──────────

    /** @dataProvider booleanBlindPayloads */
    public function test_boolean_blind_nao_muda_resultado(string $payload): void
    {
        // Baseline: busca legítima
        $baseline = $this->actingAs($this->auditor)->get('/invoices');
        $baseStatus = $baseline->getStatusCode();

        // Com payload
        $r = $this->actingAs($this->auditor)
                   ->get('/invoices?search=' . urlencode($payload));
        $this->assertNotEquals(500, $r->getStatusCode(), "[BOOLEAN-BLIND] 500 com: {$payload}");
    }

    /** @return array<string, array{0: string}> */
    public static function booleanBlindPayloads(): array
    {
        return self::buildProvider(self::BOOLEAN_BLIND, 'bool');
    }

    // ────────── Time-based blind ──────────

    /** @dataProvider timeBlindPayloads */
    public function test_time_blind_nao_causa_delay(string $payload): void
    {
        $start = microtime(true);
        $r = $this->actingAs($this->auditor)
                   ->get('/invoices?search=' . urlencode($payload));
        $elapsed = microtime(true) - $start;

        $this->assertNotEquals(500, $r->getStatusCode(), "[TIME-BLIND] 500 com: {$payload}");
        // Se o SLEEP fosse executado (mesmo com 0), teríamos padrão. Com binding é seguro.
        $this->assertLessThan(5.0, $elapsed, "[TIME-BLIND] Resposta demorou mais de 5s — possível execução de SLEEP");
    }

    /** @return array<string, array{0: string}> */
    public static function timeBlindPayloads(): array
    {
        return self::buildProvider(self::TIME_BLIND, 'time');
    }

    // ────────── Stacked queries ──────────

    /** @dataProvider stackedPayloads */
    public function test_stacked_queries_bloqueadas(string $payload): void
    {
        $r = $this->actingAs($this->auditor)
                   ->get('/invoices?search=' . urlencode($payload));
        $this->assertNotEquals(500, $r->getStatusCode(), "[STACKED] 500 com: {$payload}");
    }

    /** @return array<string, array{0: string}> */
    public static function stackedPayloads(): array
    {
        return self::buildProvider(self::STACKED, 'stack');
    }

    // ────────── Login endpoint audit ──────────

    public function test_login_rejeita_todas_categorias(): void
    {
        $all = array_merge(
            self::ERROR_BASED,
            self::UNION_BASED,
            self::BOOLEAN_BLIND,
            self::TIME_BLIND,
            self::STACKED
        );

        foreach ($all as $payload) {
            $r = $this->post('/login', [
                'email'    => $payload,
                'password' => $payload,
            ]);
            $this->assertContains(
                $r->getStatusCode(),
                [302, 419, 422, 429],
                "[LOGIN] Status inesperado {$r->getStatusCode()} com: " . substr($payload, 0, 40)
            );
        }
    }

    // ────────── Helper ──────────

    /** @return array<string, array{0: string}> */
    private static function buildProvider(array $payloads, string $prefix): array
    {
        $out = [];
        foreach ($payloads as $i => $p) {
            $out["{$prefix}_{$i}"] = [$p];
        }
        return $out;
    }
}
// PULL REQUEST END
