<?php

namespace App\Helpers;

use App\Enums\AppModuleType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AppModuleTypeCast implements CastsAttributes
{
	public function get($model, string $key, $value, array $attributes)
	{
		if ($value === null) return AppModuleType::Other;
		return AppModuleType::normalize($value) ?? AppModuleType::Other;
	}

	public function set($model, string $key, $value, array $attributes)
	{
		$normalizedValue = AppModuleType::normalize($value);
		return $value instanceof AppModuleType ? $normalizedValue->value : (in_array($normalizedValue, AppModuleType::values()) ? $normalizedValue : AppModuleType::Other->value);
	}
}
