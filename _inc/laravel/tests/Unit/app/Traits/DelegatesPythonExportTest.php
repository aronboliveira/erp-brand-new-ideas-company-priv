<?php

declare(strict_types=1);

namespace Tests\Unit\app\Traits;

use App\Traits\DelegatesPythonExport;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use Tests\TestCase;

#[Group('traits')]
class DelegatesPythonExportTest extends TestCase
{
	/* ══════════ expose trait statics ══════════ */

	private static function invoke(string $method, ...$args): mixed
	{
		return (new class {
			use DelegatesPythonExport {
				_exporterToScriptName as public;
				_resolvePythonBinary as public;
				_resolvePythonScript as public;
				_generateOutputPath as public;
				_prepareCurrencySymbol as public;
			}
		})::$method(...$args);
	}

	/* ══════════ _exporterToScriptName ══════════ */

	#[Test]
	#[DataProvider('exporterNameProvider')]
	public function exporter_to_script_name_converts_correctly(string $input, string $expected): void
	{
		$this->assertSame($expected, self::invoke('_exporterToScriptName', $input));
	}

	public static function exporterNameProvider(): array
	{
		return [
			'simple' => ['AttendanceExport', 'attendance_exporter.py'],
			'multi-word' => ['ProductServiceExport', 'product_service_exporter.py'],
			'no-export-suffix' => ['Sales', 'sales_exporter.py'],
			'empty' => ['', '_exporter.py'],
			'just-export' => ['Export', '_exporter.py'],
			'lowercase' => ['invoiceExport', 'invoice_exporter.py'],
			'already-snake' => ['already_snake_Export', 'already_snake__exporter.py'],
			'all-caps' => ['ABCExport', 'abc_exporter.py'],
		];
	}

	/* ══════════ _resolvePythonBinary ══════════ */

	#[Test]
	public function resolve_python_binary_returns_default(): void
	{
		$result = self::invoke('_resolvePythonBinary');
		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	#[Test]
	public function resolve_python_binary_respects_config(): void
	{
		config(['exports.python_binary' => '/usr/bin/python3.11']);
		$result = self::invoke('_resolvePythonBinary');
		$this->assertSame('/usr/bin/python3.11', $result);
		config(['exports.python_binary' => null]); // cleanup
	}

	/* ══════════ _generateOutputPath ══════════ */

	#[Test]
	public function generate_output_path_returns_string(): void
	{
		$result = self::invoke('_generateOutputPath', 'test_prefix');
		$this->assertIsString($result);
		$this->assertStringContainsString('test_prefix', $result);
		$this->assertStringEndsWith('.xlsx', $result);
	}

	#[Test]
	public function generate_output_path_custom_extension(): void
	{
		$result = self::invoke('_generateOutputPath', 'report', 'csv');
		$this->assertStringEndsWith('.csv', $result);
	}

	#[Test]
	public function generate_output_path_contains_date(): void
	{
		$result = self::invoke('_generateOutputPath', 'data');
		$this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $result);
	}

	/* ══════════ _prepareCurrencySymbol ══════════ */

	#[Test]
	public function prepare_currency_symbol_with_method(): void
	{
		$user = new class {
			public function currencySymbol(): string
			{
				return 'R$';
			}
		};
		$this->assertSame('R$', self::invoke('_prepareCurrencySymbol', $user));
	}

	#[Test]
	public function prepare_currency_symbol_with_property(): void
	{
		$user = new class {
			public string $currency_symbol = '$';
		};
		$this->assertSame('$', self::invoke('_prepareCurrencySymbol', $user));
	}

	#[Test]
	public function prepare_currency_symbol_null_user(): void
	{
		$this->assertSame('', self::invoke('_prepareCurrencySymbol', null));
	}

	#[Test]
	public function prepare_currency_symbol_string_user(): void
	{
		$this->assertSame('', self::invoke('_prepareCurrencySymbol', 'not-an-object'));
	}

	/* ══════════ performance ══════════ */

	#[Test]
	public function exporter_name_conversion_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			self::invoke('_exporterToScriptName', 'SomeExporterExport');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(100, $elapsed, '1000 name conversions should be < 100ms');
	}
}
