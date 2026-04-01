<?php
// Roleplay: Green Hat (Script Kiddie) — Unit smoke test
// "Será que a função de escape funciona?"
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Unit\Security\Roleplay\GreenHat;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * @group security
 * @group green-hat
 */
class GreenHatUnitTest extends TestCase
{
    // "Peguei esses payloads de um PDF"
    private const PAYLOADS = [
        "' OR '1'='1",
        "admin'--",
        "1 OR 1=1",
    ];

    public function test_pdo_escapa_aspas_simples(): void
    {
        // "Se eu usar PDO direto, ele escapa, né?"
        $pdo = DB::connection()->getPdo();
        foreach (self::PAYLOADS as $payload) {
            $quoted = $pdo->quote($payload);
            $this->assertNotEquals($payload, $quoted, "PDO não escapou: {$payload}");
        }
    }

    public function test_query_builder_nao_aceita_payload_cru(): void
    {
        // Usando query builder com binding — o payload vira parâmetro seguro
        foreach (self::PAYLOADS as $payload) {
            $sql = DB::table('users')->where('email', $payload)->toSql();
            $this->assertStringContainsString('?', $sql, 'Deveria ter placeholder');
            $this->assertStringNotContainsString($payload, $sql, "Payload apareceu no SQL");
        }
    }

    public function test_payload_nao_contem_resultado_valido(): void
    {
        // "Se eu buscar com payload, não deveria achar ninguém"
        foreach (self::PAYLOADS as $payload) {
            $count = DB::table('users')->where('email', $payload)->count();
            $this->assertEquals(0, $count, "Achou algo com payload: {$payload}");
        }
    }
}
// PULL REQUEST END
