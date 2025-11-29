<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasInheritedRules
{
	protected static function mergeRulesRecursively(array $base, array $local): array
	{
		if (!is_array($base) || !is_array($local))
			return $base;
		foreach ($local as $key => $localValue) {
			try {
				if (!isset($base[$key])) {
					$base[$key] = $localValue;
					continue;
				}
				$baseValue = $base[$key];
				if (is_array($baseValue) && is_array($localValue)) {
					try {
						if (
							static::isScalarList($baseValue)
							&& static::isScalarList($localValue)
						)
							$base[$key] = array_values(
								array_intersect($baseValue, $localValue)
							);
						else
							$base[$key] = static::mergeRulesRecursively($baseValue, $localValue);
						continue;
					} catch (\Throwable $e) {
						Log::warning(static::class . " error checking in array for key {$key}", [
							'base_value'  => $baseValue,
							'local_value' => $localValue,
							'exception'   => $e->getMessage(),
						]);
					}
				}
				if (is_numeric($baseValue) && is_numeric($localValue)) {
					if (in_array($key, ['min', 'min_length', 'min_value'], true)) {
						$base[$key] = max($baseValue, $localValue);
						continue;
					}
					if (in_array($key, ['max', 'max_length', 'max_value'], true)) {
						$base[$key] = min($baseValue, $localValue);
						continue;
					}
					$base[$key] = $baseValue;
					continue;
				}
				if (is_string($baseValue) && is_string($localValue)) {
					$base[$key] = ($baseValue === $localValue) ? $localValue : $baseValue;
					continue;
				}
				if (is_bool($baseValue) && is_bool($localValue)) {
					if (in_array($key, ['required', 'strict', 'unique', 'readonly'], true)) {
						$base[$key] = ($baseValue || $localValue);
						continue;
					}
					if (in_array($key, ['nullable', 'allow_null'], true)) {
						$base[$key] = ($baseValue && $localValue);
						continue;
					}
					$base[$key] = $baseValue;
					continue;
				}
				if ($baseValue === null && $localValue !== null) {
					$base[$key] = $localValue;
					continue;
				}
				if ($baseValue !== null && $localValue === null) {
					$base[$key] = $baseValue;
					continue;
				}
				$base[$key] = $baseValue;
			} catch (\Throwable $e) {
				Log::warning(static::class . " error merging rules for key {$key}", [
					'base'       => $base,
					'local'      => $local,
					'exception'  => $e->getMessage(),
				]);
				continue;
			}
		}
		return $base;
	}

	protected static function isScalarList(array $value): bool
	{
		if ($value === [])
			return true;
		if (array_keys($value) !== range(0, count($value) - 1))
			return false;
		// ? elementos escalares (int|float|string|bool|null)
		foreach ($value as $v)
			if (!static::isScalarOrNull($v))
				return false;
		return true;
	}

	protected static function isScalarOrNull(mixed $v): bool
	{
		return is_null($v) || is_scalar($v);
	}
}
