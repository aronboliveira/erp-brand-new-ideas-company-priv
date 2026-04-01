<?php
// Roleplay: White Hat (Ethical Pentester) — Unit-level binding & ORM audit
// Ref: OWASP Testing Guide v4 — OTG-INPVAL-005
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Unit\Security\Roleplay\WhiteHat;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * Auditoria pentester ético: verifica que bindings e ORM estão corretos
 * antes de atingir o banco. Sem HTTP — puramente unit.
 *
 * @group security
 * @group white-hat
 * @group owasp
 */
class WhiteHatBindingTest extends TestCase
{
    // Payloads por categoria
    private const ERROR_BASED = [
        "' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--",
        "' AND UPDATEXML(1,CONCAT(0x7e,version()),1)--",
    ];

    private const UNION_BASED = [
        "' UNION SELECT NULL--",
        "' UNION SELECT NULL,NULL,NULL--",
        "1' UNION SELECT username,password FROM users--",
    ];

    private const BOOLEAN_BLIND = [
        "1' AND 1=1--",
        "1' AND 1=2--",
    ];

    private const ALL_PAYLOADS = [
        ...self::ERROR_BASED,
        ...self::UNION_BASED,
        ...self::BOOLEAN_BLIND,
    ];

    /** @dataProvider allPayloads */
    public function test_query_builder_usa_placeholder(string $payload): void
    {
        $sql = DB::table('users')->where('email', $payload)->toSql();
        $this->assertStringContainsString('?', $sql, "[WHITE-HAT] Faltou placeholder no SQL");
        $this->assertStringNotContainsString($payload, $sql, "[WHITE-HAT] Payload literal no SQL");
    }

    /** @dataProvider allPayloads */
    public function test_pdo_quote_escapa_payload(string $payload): void
    {
        $pdo = DB::connection()->getPdo();
        $quoted = $pdo->quote($payload);
        // O valor quoted deve ser diferente — aspas foram escapadas
        $this->assertNotEquals("'{$payload}'", $quoted, "[WHITE-HAT] PDO::quote não escapou corretamente");
    }

    /** @dataProvider allPayloads */
    public function test_whereRaw_com_binding_usa_placeholder(string $payload): void
    {
        // Simula uso correto de whereRaw com binding
        $builder = DB::table('users')->whereRaw('email = ?', [$payload]);
        $sql = $builder->toSql();
        $this->assertStringContainsString('?', $sql);
        $bindings = $builder->getBindings();
        $this->assertContains($payload, $bindings, "[WHITE-HAT] Payload deveria estar nos bindings, não no SQL");
    }

    public function test_eloquent_where_nunca_interpolate(): void
    {
        // Teste de regressão: Eloquent where() ALWAYS uses bindings
        foreach (self::ALL_PAYLOADS as $payload) {
            $builder = \App\Models\User::where('email', $payload)->getQuery();
            $sql = $builder->toSql();
            $this->assertStringNotContainsString($payload, $sql, "[WHITE-HAT] Eloquent interpolou payload no SQL");
            $this->assertContains($payload, $builder->getBindings());
        }
    }

    public function test_find_in_set_usa_binding(): void
    {
        // Regressão específica: DashboardController usava interpolação com find_in_set
        $payload = "' UNION SELECT 1--";
        $builder = DB::table('users')
            ->whereRaw('FIND_IN_SET(?, role_ids)', [$payload]);
        $sql = $builder->toSql();
        $this->assertStringContainsString('?', $sql);
        $this->assertStringNotContainsString($payload, $sql);
    }

    /** @return array<string, array{0: string}> */
    public static function allPayloads(): array
    {
        $out = [];
        foreach (self::ALL_PAYLOADS as $i => $p) {
            $label = 'owasp_' . $i;
            $out[$label] = [$p];
        }
        return $out;
    }
}
// PULL REQUEST END
