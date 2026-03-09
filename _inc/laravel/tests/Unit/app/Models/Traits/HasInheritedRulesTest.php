<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\HasInheritedRules;
use PHPUnit\Framework\Attributes\{DataProvider, Group, Test};
use PHPUnit\Framework\TestCase;

#[Group('models-traits')]
class HasInheritedRulesTest extends TestCase
{
	private static function host(): object
	{
		return new class {
			use HasInheritedRules {
				mergeRulesRecursively as public;
				isScalarList as public;
				isScalarOrNull as public;
			}
		};
	}

	/* ══════════════ isScalarOrNull ══════════════ */

	#[Test]
	#[DataProvider('scalarOrNullProvider')]
	public function is_scalar_or_null_returns_expected(mixed $input, bool $expected): void
	{
		$this->assertSame($expected, self::host()::isScalarOrNull($input));
	}

	public static function scalarOrNullProvider(): array
	{
		return [
			'null' => [null, true],
			'int' => [42, true],
			'float' => [3.14, true],
			'string' => ['hello', true],
			'bool' => [true, true],
			'array' => [[], false],
			'object' => [new \stdClass(), false],
		];
	}

	/* ══════════════ isScalarList ══════════════ */

	#[Test]
	#[DataProvider('scalarListProvider')]
	public function is_scalar_list_returns_expected(array $input, bool $expected): void
	{
		$this->assertSame($expected, self::host()::isScalarList($input));
	}

	public static function scalarListProvider(): array
	{
		return [
			'empty' => [[], true],
			'ints' => [[1, 2, 3], true],
			'strings' => [['a', 'b'], true],
			'mixed scalars' => [[1, 'x', true], true],
			'with null' => [[1, null, 'x'], true],
			'associative' => [['a' => 1], false],
			'nested array' => [[1, [2]], false],
		];
	}

	/* ══════════════ mergeRulesRecursively ══════════════ */

	#[Test]
	public function merge_adds_new_keys(): void
	{
		$base = ['a' => 1];
		$local = ['b' => 2];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(['a' => 1, 'b' => 2], $result);
	}

	#[Test]
	public function merge_min_takes_max(): void
	{
		$base = ['min' => 5];
		$local = ['min' => 10];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(10, $result['min']);
	}

	#[Test]
	public function merge_min_length_takes_max(): void
	{
		$base = ['min_length' => 3];
		$local = ['min_length' => 8];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(8, $result['min_length']);
	}

	#[Test]
	public function merge_max_takes_min(): void
	{
		$base = ['max' => 100];
		$local = ['max' => 50];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(50, $result['max']);
	}

	#[Test]
	public function merge_max_length_takes_min(): void
	{
		$base = ['max_length' => 255];
		$local = ['max_length' => 100];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(100, $result['max_length']);
	}

	#[Test]
	public function merge_required_bool_is_or(): void
	{
		$this->assertTrue(self::host()::mergeRulesRecursively(
			['required' => false],
			['required' => true]
		)['required']);
		$this->assertTrue(self::host()::mergeRulesRecursively(
			['required' => true],
			['required' => false]
		)['required']);
	}

	#[Test]
	public function merge_nullable_bool_is_and(): void
	{
		$this->assertFalse(self::host()::mergeRulesRecursively(
			['nullable' => true],
			['nullable' => false]
		)['nullable']);
		$this->assertTrue(self::host()::mergeRulesRecursively(
			['nullable' => true],
			['nullable' => true]
		)['nullable']);
	}

	#[Test]
	public function merge_string_same_uses_local(): void
	{
		$result = self::host()::mergeRulesRecursively(
			['type' => 'text'],
			['type' => 'text']
		);
		$this->assertSame('text', $result['type']);
	}

	#[Test]
	public function merge_string_different_uses_base(): void
	{
		$result = self::host()::mergeRulesRecursively(
			['type' => 'text'],
			['type' => 'number']
		);
		$this->assertSame('text', $result['type']);
	}

	#[Test]
	public function merge_scalar_lists_intersect(): void
	{
		$result = self::host()::mergeRulesRecursively(
			['options' => ['a', 'b', 'c']],
			['options' => ['b', 'c', 'd']]
		);
		$this->assertSame(['b', 'c'], $result['options']);
	}

	#[Test]
	public function merge_nested_arrays_recurse(): void
	{
		$base = ['rules' => ['min' => 1, 'max' => 100]];
		$local = ['rules' => ['min' => 5, 'max' => 50]];
		$result = self::host()::mergeRulesRecursively($base, $local);
		$this->assertSame(5, $result['rules']['min']);
		$this->assertSame(50, $result['rules']['max']);
	}

	#[Test]
	public function merge_null_base_replaced_by_local(): void
	{
		$result = self::host()::mergeRulesRecursively(
			['x' => null],
			['x' => 'value']
		);
		$this->assertSame('value', $result['x']);
	}

	#[Test]
	public function merge_null_local_keeps_base(): void
	{
		$result = self::host()::mergeRulesRecursively(
			['x' => 'value'],
			['x' => null]
		);
		$this->assertSame('value', $result['x']);
	}

	#[Test]
	public function merge_empty_base_returns_empty(): void
	{
		$result = self::host()::mergeRulesRecursively([], ['a' => 1]);
		$this->assertSame(['a' => 1], $result);
	}

	#[Test]
	public function merge_empty_local_returns_base(): void
	{
		$result = self::host()::mergeRulesRecursively(['a' => 1], []);
		$this->assertSame(['a' => 1], $result);
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function merge_rules_performance(): void
	{
		$host = self::host();
		$base = ['min' => 1, 'max' => 100, 'required' => false, 'nullable' => true, 'options' => ['a', 'b', 'c']];
		$local = ['min' => 5, 'max' => 50, 'required' => true, 'nullable' => false, 'options' => ['b', 'c', 'd']];
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$host::mergeRulesRecursively($base, $local);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(300, $elapsed, '5000 mergeRulesRecursively should be < 300ms');
	}
}
