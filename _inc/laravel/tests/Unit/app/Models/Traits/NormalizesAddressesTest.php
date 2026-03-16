<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\NormalizesAddresses;
use App\Traits\NormalizesArrays;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
#[Group('models-traits')]
class NormalizesAddressesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/* ═══════════ helper: anonymous class using the trait ═══════════ */

	private static function host(): object
	{
		return new class {
			use NormalizesAddresses, NormalizesArrays;
		};
	}

	/* ══════════════ normalizeEmail ══════════════ */

	#[Test]
	#[DataProvider('emailProvider')]
	public function normalize_email_returns_expected(?string $input, ?string $expected): void
	{
		$result = self::host()::normalizeEmail($input, 'test', 'owner-1');
		$this->assertSame($expected, $result);
	}

	public static function emailProvider(): array
	{
		return [
			'valid lowercase' => ['user@example.com', 'user@example.com'],
			'uppercase' => ['USER@EXAMPLE.COM', 'user@example.com'],
			'trimmed' => ['  user@example.com  ', 'user@example.com'],
			'null' => [null, null],
			'empty' => ['', null],
			'whitespace only' => ['   ', null],
			'missing @' => ['userexample.com', 'userexample.com'], // invalid but returned as-is
			'missing tld' => ['user@example', 'user@example'], // invalid but returned as-is
		];
	}

	/* ══════════════ normalizePhone ══════════════ */

	#[Test]
	#[DataProvider('phoneProvider')]
	public function normalize_phone_returns_expected(?string $input, ?string $expected): void
	{
		$result = self::host()::normalizePhone($input, 'test', 'owner-1', false);
		$this->assertSame($expected, $result);
	}

	public static function phoneProvider(): array
	{
		return [
			'valid br' => ['+5521999990000', '+5521999990000'],
			'valid digits' => ['5521999990000', '5521999990000'],
			'short' => ['123', null], // fewer than 7 digits
			'null' => [null, null],
			'empty' => ['', null],
			'whitespace' => ['  ', null],
			'with dashes' => ['+55-21-99999-0000', '+55-21-99999-0000'],
		];
	}

	/* ══════════════ normalizeZip ══════════════ */

	#[Test]
	#[DataProvider('zipProvider')]
	public function normalize_zip_returns_expected(?string $zip, ?string $country, ?string $expected): void
	{
		$result = self::host()::normalizeZip($zip, $country, 'test', 'owner-1');
		$this->assertSame($expected, $result);
	}

	public static function zipProvider(): array
	{
		return [
			'br 8 digits' => ['01311000', 'brazil', '01311-000'],
			'br already formatted' => ['01311-000', 'brazil', '01311-000'],
			'br null country defaults' => ['01311000', null, '01311-000'],
			'null zip' => [null, 'brazil', null],
			'empty zip' => ['', 'brazil', null],
			'us zip' => ['10001', 'US', '10001'],
			'us zip with dash' => ['10001-1234', 'US', '10001-1234'],
		];
	}

	/* ══════════════ normalizeContactKey ══════════════ */

	#[Test]
	#[DataProvider('contactKeyProvider')]
	public function normalize_contact_key_returns_expected(string $input, string $expected): void
	{
		$this->assertSame($expected, self::host()::normalizeContactKey($input));
	}

	public static function contactKeyProvider(): array
	{
		return [
			'simple' => ['Email', 'email'],
			'with spaces' => ['Phone Number', 'phone_number'],
			'with dashes' => ['e-mail', 'e_mail'],
			'with dots' => ['home.phone', 'home_phone'],
			'multiple separators' => ['phone--number', 'phone_number'],
			'leading trailing spaces' => ['  phone  ', 'phone'],
		];
	}

	/* ══════════════ keyIsEmail / keyIsPhone / keyIsGenericContact ══════════════ */

	#[Test]
	public function key_is_email_returns_true_for_email_keys(): void
	{
		$host = self::host();
		// ContactKeyType::EMAIL_KEYS should contain 'email'
		$this->assertTrue($host::keyIsEmail('email'));
	}

	#[Test]
	public function key_is_phone_returns_true_for_phone_keys(): void
	{
		$host = self::host();
		$this->assertTrue($host::keyIsPhone('phone'));
	}

	#[Test]
	public function key_is_generic_returns_true_for_contact_keys(): void
	{
		$host = self::host();
		$this->assertTrue($host::keyIsGenericContact('contact'));
	}

	#[Test]
	public function key_is_email_returns_false_for_non_email(): void
	{
		$this->assertFalse(self::host()::keyIsEmail('address'));
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function normalize_email_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$host::normalizeEmail("USER{$i}@EXAMPLE.COM", 'perf', $i);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '5000 normalizeEmail should be < 500ms');
	}

	#[Test]
	public function normalize_zip_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 3000; $i++) {
			$host::normalizeZip('01311000', 'brazil', 'perf', $i);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '3000 normalizeZip should be < 500ms');
	}

	#[Test]
	public function normalize_contact_key_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 10000; $i++) {
			$host::normalizeContactKey('Phone Number');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '10000 normalizeContactKey should be < 200ms');
	}
}
