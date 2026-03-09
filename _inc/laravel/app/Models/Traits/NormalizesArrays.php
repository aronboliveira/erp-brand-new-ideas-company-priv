<?php

namespace App\Traits;

use Illuminate\Support\Facades\{Log};

trait NormalizesArrays
{
	public static function normalizeArrayField(mixed $value): array
	{
		try {
			if ($value === null)
				return [];
			if (is_string($value)) {
				$decoded = json_decode($value, true);
				return is_array($decoded) ? $decoded : [];
			}
			return is_array($value) ? $value : (array) $value;
		} catch (\Throwable) {
			Log::debug('NormalizesArrays::normalizeArrayField - Failed to decode array field', [
				'value' => $value,
				'class' => static::class,
				'trait' => __TRAIT__,
				'method' => __METHOD__,
				'line' => __LINE__,
			]);
			return is_array($value) ? $value : (empty($value) ? [] : (array) $value);
		}
	}

		public static function encodeJsonValue(mixed $value, string $key): ?string
	{
		    try {
    		if ($value === null || $value === '')
    			return null;
    		if (is_string($value)) {
    			$trimmed = trim($value);
    			if ($trimmed === '')
    				return null;
    			if (self::looksLikeJson($trimmed))
    				return $trimmed;
    			$value = [$trimmed];
    		}
    		if (!is_array($value) && !is_object($value))
    			$value = [$value];
    		try {
    			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    		} catch (\Throwable) {
    						return json_encode((array) $value);
    		}
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::encodeJsonValue — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return '';
		    }
	}

		public static function looksLikeJson(string $value): bool
	{
		    try {
    		$first = $value[0] ?? '';
    		if ($first !== '{' && $first !== '[')
    			return false;
    		try {
    			json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    			return true;
    		} catch (\Throwable) {
    			return false;
    		}
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::looksLikeJson — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return false;
		    }
	}

		protected function encodeJsonAttribute(string $key, mixed $value): void
	{
		$this->attributes[$key] = self::encodeJsonValue($value, $key);
	}

		protected function ensureJsonAttributesAreEncoded($jsonFields): void
	{
		    try {
    		foreach ($jsonFields as $field) {
    			if (!array_key_exists($field, $this->attributes)) {
    				continue;
    			}

    			$current = $this->attributes[$field];

    			if (is_array($current) || is_object($current)) {
    				self::encodeJsonAttribute($field, $current);
    			} elseif (is_string($current) && $current !== '' && !self::looksLikeJson($current)) {
    				self::encodeJsonAttribute($field, $current);
    			}
    		}
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::ensureJsonAttributesAreEncoded — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		    }
	}

	protected function normalizeStringList(mixed $value): ?array
	{
	    try {
    		$arr = self::normalizeArrayField($value);
    		$out = [];
    		foreach ($arr as $v) {
    			if (!is_scalar($v)) continue;
    			$s = trim((string) $v);
    			if ($s === '') continue;
    			$out[] = $s;
    		}
    		$out = array_values(array_unique($out));
    		return $out ?: null;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeStringList — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
}
