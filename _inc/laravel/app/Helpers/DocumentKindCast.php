<?php

namespace App\Helpers;

use App\Enums\DocumentKind;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DocumentKindCast implements CastsAttributes
{
	public function get($model, string $key, $value, array $attributes)
	{
		if ($value === null) return DocumentKind::UNKNOWN;
		return DocumentKind::normalize($value) ?? DocumentKind::UNKNOWN;
	}

	public function set($model, string $key, $value, array $attributes)
	{
		$normalizedValue = DocumentKind::normalize($value);
		return $value instanceof DocumentKind ? $normalizedValue->value : (in_array($normalizedValue, DocumentKind::values()) ? $normalizedValue : null);
	}
}
