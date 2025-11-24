<?php

namespace App\Enums;

enum ProductStatus: string
{
	case Active   = 'active';
	case Paused   = 'paused';
	case Inactive = 'inactive';
	case Undefined = 'undefined';

	public static function normalize(null|string|\BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;
		$k = strtolower(trim((string)$v));
		return self::tryFrom($k);
	}
	public static function labels(): array
	{
		return [
			self::Active->value    => 'Active',
			self::Paused->value    => 'Paused',
			self::Inactive->value  => 'Inactive',
			self::Undefined->value => 'Undefined',
		];
	}
}
