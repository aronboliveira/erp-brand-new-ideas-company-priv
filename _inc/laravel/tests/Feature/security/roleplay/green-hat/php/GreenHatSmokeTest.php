<?php
// Roleplay: Green Hat (Script Kiddie) — Smoke test ingênuo de SQLi
// "Vi num tutorial do YouTube que isso funciona..."
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Feature\Security\Roleplay\GreenHat;

use Tests\TestCase;
use App\Models\User;

/**
 * @group security
 * @group green-hat
 */
class GreenHatSmokeTest extends TestCase
{
    // Payloads que um script kiddie colaria da internet
    private const CLASSIC_PAYLOADS = [
        "' OR '1'='1",
        "admin'--",
        "1 OR 1=1",
        "' OR ''='",
        "password' OR 1=1 --",
    ];

    public function test_login_com_or_1_eq_1(): void
    {
        // "Será que o admin' OR '1'='1 funciona?"
        $r = $this->post('/login', [
            'email'    => "' OR '1'='1",
            'password' => "' OR '1'='1",
        ]);
        // 302 (redirect back) ou 419 (CSRF) — never 500
        $this->assertContains($r->getStatusCode(), [302, 419, 422]);
    }

    public function test_login_com_admin_dash_dash(): void
    {
        $r = $this->post('/login', [
            'email'    => "admin'--",
            'password' => 'password',
        ]);
        $this->assertContains($r->getStatusCode(), [302, 419, 422]);
    }

    /** @dataProvider classicPayloads */
    public function test_search_nao_retorna_500(string $payload): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Sem usuários no banco');
        }
        $r = $this->actingAs($user)->get('/invoices?search=' . urlencode($payload));
        $this->assertNotEquals(500, $r->getStatusCode(), "Deu 500 com: {$payload}");
    }

    /** @return array<string, array{0: string}> */
    public static function classicPayloads(): array
    {
        $cases = [];
        foreach (self::CLASSIC_PAYLOADS as $i => $p) {
            $cases["payload_{$i}"] = [$p];
        }
        return $cases;
    }

    public function test_url_com_aspas_simples(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('Sem usuários');
        }
        // "E se eu colocar aspas na URL?"
        $r = $this->actingAs($user)->get("/invoices/' OR '1'='1");
        $this->assertNotEquals(500, $r->getStatusCode());
    }
}
// PULL REQUEST END
