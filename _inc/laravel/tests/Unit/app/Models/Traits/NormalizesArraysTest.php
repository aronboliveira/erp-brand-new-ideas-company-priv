<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\NormalizesArrays;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use PHPUnit\Framework\TestCase;

#[Group('models-traits')]
class NormalizesArraysTest extends TestCase
{
	/* ════════════ helper: expose trait statics ════════════ */

	private static function host(): object
	{
		return new class {
			use NormalizesArrays;

			public array $attributes = [];

			public function publicNormalizeStringList(mixed $v): ?array
			{
				return $this->normalizeStringList($v);
			}

			public function publicEncodeJsonAttribute(string $k, mixed $v): void
			{
				$this->encodeJsonAttribute($k, $v);
			}

			public function publicEnsureJsonAttributesAreEncoded(array $fields): void
			{
				$this->ensureJsonAttributesAreEncoded($fields);
			}
		};
	}

	/* ══════════════ normalizeArrayField ══════════════ */

	#[Test]
	#[DataProvider('normalizeArrayFieldProvider')]
	public function normalize_array_field_returns_expected(mixed $input, array $expected): void
	{
		$host = self::host();
		$this->assertSame($expected, $host::normalizeArrayField($input));
	}

	public static function normalizeArrayFieldProvider(): array
	{
		return [
			'null' => [null, []],
			'empty string' => ['', []],
			'json array' => ['[1,2,3]', [1, 2, 3]],
			'json object' => ['{"a":"b"}', ['a' => 'b']],
			'invalid json' => ['not json', []],
			'already array' => [[1, 2], [1, 2]],
			'int' => [42, [42]],
			'bool' => [true, [true]],
			'nested json' => ['{"x":[1,2]}', ['x' => [1, 2]]],
		];
	}

	/* ══════════════ looksLikeJson ══════════════ */

	#[Test]
	#[DataProvider('looksLikeJsonProvider')]
	public function looks_like_json_returns_expected(string $input, bool $expected): void
	{
		$host = self::host();
		$this->assertSame($expected, $host::looksLikeJson($input));
	}

	public static function looksLikeJsonProvider(): array
	{
		return [
			'valid object' => ['{"a":1}', true],
			'valid array' => ['[1,2,3]', true],
			'plain string' => ['hello', false],
			'number' => ['42', false],
			'empty' => ['', false],
			'opening brace invalid' => ['{not json}', false],
			'nested' => ['{"a":{"b":1}}', true],
		];
	}

	/* ══════════════ encodeJsonValue ══════════════ */

	#[Test]
	public function encode_json_value_null_returns_null(): void
	{
		$this->assertNull(self::host()::encodeJsonValue(null, 'key'));
	}

	#[Test]
	public function encode_json_value_empty_string_returns_null(): void
	{
		$this->assertNull(self::host()::encodeJsonValue('', 'key'));
	}

	#[Test]
	public function encode_json_value_valid_json_passes_through(): void
	{
		$json = '{"a":1}';
		$this->assertSame($json, self::host()::encodeJsonValue($json, 'key'));
	}

	#[Test]
	public function encode_json_value_plain_string_wraps_in_array(): void
	{
		$result = self::host()::encodeJsonValue('hello', 'key');
		$this->assertSame('["hello"]', $result);
	}

	#[Test]
	public function encode_json_value_array_encodes(): void
	{
		$result = self::host()::encodeJsonValue([1, 2, 3], 'key');
		$this->assertSame('[1,2,3]', $result);
	}

	#[Test]
	public function encode_json_value_integer_wraps(): void
	{
		$result = self::host()::encodeJsonValue(42, 'key');
		$this->assertSame('[42]', $result);
	}

	/* ══════════════ normalizeStringList ══════════════ */

	#[Test]
	public function normalize_string_list_null_returns_null(): void
	{
		$this->assertNull(self::host()->publicNormalizeStringList(null));
	}

	#[Test]
	public function normalize_string_list_json_array(): void
	{
		$result = self::host()->publicNormalizeStringList('["a","b","a"]');
		$this->assertSame(['a', 'b'], $result);
	}

	#[Test]
	public function normalize_string_list_strips_blanks(): void
	{
		$result = self::host()->publicNormalizeStringList(['hello', '', ' ', 'world']);
		$this->assertSame(['hello', 'world'], $result);
	}

	#[Test]
	public function normalize_string_list_empty_array_returns_null(): void
	{
		$this->assertNull(self::host()->publicNormalizeStringList([]));
	}

	/* ══════════════ ensureJsonAttributesAreEncoded ══════════════ */

	#[Test]
	public function ensure_json_attributes_encodes_arrays(): void
	{
		$host = self::host();
		$host->attributes = ['tags' => ['a', 'b']];
		$host->publicEnsureJsonAttributesAreEncoded(['tags']);
		$this->assertSame('["a","b"]', $host->attributes['tags']);
	}

	#[Test]
	public function ensure_json_attributes_skips_valid_json_string(): void
	{
		$host = self::host();
		$host->attributes = ['data' => '{"x":1}'];
		$host->publicEnsureJsonAttributesAreEncoded(['data']);
		$this->assertSame('{"x":1}', $host->attributes['data']);
	}

	#[Test]
	public function ensure_json_attributes_skips_missing_field(): void
	{
		$host = self::host();
		$host->attributes = [];
		$host->publicEnsureJsonAttributesAreEncoded(['nonexistent']);
		$this->assertSame([], $host->attributes);
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function normalize_array_field_performance(): void
	{
		$host = self::host();
		$json = '{"a":1,"b":[1,2,3],"c":"hello"}';
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$host::normalizeArrayField($json);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '5000 normalizeArrayField should be < 200ms');
	}

	#[Test]
	public function looks_like_json_performance(): void
	{
		$host = self::host();
		$start = hrtime(true);
		for ($i = 0; $i < 10000; $i++) {
			$host::looksLikeJson('{"valid":true}');
			$host::looksLikeJson('not json');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '20000 looksLikeJson should be < 200ms');
	}
}
