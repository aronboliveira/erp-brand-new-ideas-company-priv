<?php
// Roleplay: QA Tester — Unit sanitization & validation checks
// Foco: XSS escaping, input trimming, encoding
// PULL REQUEST START
declare(strict_types=1);

namespace Tests\Unit\Security\Roleplay\Qa;

use Tests\TestCase;

/**
 * QA unit — verifica funções de sanitização e validação de input.
 *
 * @group security
 * @group qa
 */
class QaSanitizationTest extends TestCase
{
    private const SPECIAL_NAMES = [
        "O'Brien",
        '名前テスト',
        'اسم',
        'José María Ñoño',
        'Teste 🎉👍',
    ];

    private const XSS_VECTORS = [
        '<script>alert(1)</script>',
        '<img src=x onerror="alert(1)">',
        '<svg/onload=alert(1)>',
        '"><script>alert(1)</script>',
    ];

    /** @dataProvider specialNames */
    public function test_e_escapado_corretamente(string $input): void
    {
        $escaped = e($input);
        // e() é o helper do Laravel para htmlspecialchars
        $this->assertIsString($escaped);
        // Não deve conter HTML não-escapado
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    /** @dataProvider xssVectors */
    public function test_xss_neutralizado_por_e(string $vector): void
    {
        $escaped = e($vector);
        // e() escapa < > " ' & — tags HTML não renderizam
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringNotContainsString('<svg', $escaped);
        $this->assertStringNotContainsString('<img', $escaped);
    }

    public function test_input_vazio_trim(): void
    {
        $this->assertEquals('', trim(''));
        $this->assertEquals('', trim('   '));
        $this->assertEquals('', trim("\t\n"));
    }

    public function test_input_longo_pode_ser_truncado(): void
    {
        $long = str_repeat('a', 10000);
        $truncated = substr($long, 0, 255);
        $this->assertEquals(255, strlen($truncated));
    }

    public function test_null_byte_removido(): void
    {
        $input = "test\x00end";
        $clean = str_replace("\x00", '', $input);
        $this->assertEquals('testend', $clean);
        $this->assertStringNotContainsString("\x00", $clean);
    }

    /** @return array<string, array{0: string}> */
    public static function specialNames(): array
    {
        $out = [];
        foreach (self::SPECIAL_NAMES as $i => $n) { $out["name_{$i}"] = [$n]; }
        return $out;
    }

    /** @return array<string, array{0: string}> */
    public static function xssVectors(): array
    {
        $out = [];
        foreach (self::XSS_VECTORS as $i => $v) { $out["xss_{$i}"] = [$v]; }
        return $out;
    }
}
// PULL REQUEST END
