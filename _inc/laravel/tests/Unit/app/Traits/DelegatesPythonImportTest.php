<?php

declare(strict_types=1);

namespace Tests\Unit\app\Traits;

use App\Traits\DelegatesPythonImport;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use Tests\TestCase;

#[Group('traits')]
class DelegatesPythonImportTest extends TestCase
{
	private static function invoke(string $method, ...$args): mixed
	{
		return (new class {
			use DelegatesPythonImport {
				_importerToScriptName as public;
				_resolveImportPythonBinary as public;
				_resolveImportPythonScript as public;
			}
		})::$method(...$args);
	}

	/* ══════════ _importerToScriptName ══════════ */

	#[Test]
	#[DataProvider('importerNameProvider')]
	public function importer_to_script_name_converts_correctly(string $input, string $expected): void
	{
		$this->assertSame($expected, self::invoke('_importerToScriptName', $input));
	}

	public static function importerNameProvider(): array
	{
		return [
			'simple' => ['AttendanceImport', 'attendance_importer.py'],
			'multi-word' => ['ProductServiceImport', 'product_service_importer.py'],
			'no-import-suffix' => ['Sales', 'sales_importer.py'],
			'empty' => ['', '_importer.py'],
			'just-import' => ['Import', '_importer.py'],
		];
	}

	/* ══════════ _resolveImportPythonBinary ══════════ */

	#[Test]
	public function resolve_import_python_binary_returns_default(): void
	{
		$result = self::invoke('_resolveImportPythonBinary');
		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	#[Test]
	public function resolve_binary_respects_config(): void
	{
		config(['exports.python_binary' => '/opt/python3']);
		$result = self::invoke('_resolveImportPythonBinary');
		$this->assertSame('/opt/python3', $result);
		config(['exports.python_binary' => null]);
	}

	/* ══════════ performance ══════════ */

	#[Test]
	public function importer_name_conversion_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			self::invoke('_importerToScriptName', 'SomeImporterImport');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(100, $elapsed, '1000 name conversions should be < 100ms');
	}
}
