<?php

namespace App\Traits;

trait NormalizesArrays
{
	public static function normalizeArrayField(mixed $value): array
	{
		if ($value === null)
			return [];

		if (is_string($value)) {
			$decoded = json_decode($value, true);
			return is_array($decoded) ? $decoded : [];
		}

		return is_array($value) ? $value : (array) $value;
	}
}
