<?php

namespace App\Enums;

use App\Config\Constants\PermissionsConstants as PC;

enum UserType: string
{
	case SuperAdmin = PC::SA;
	case Admin      = PC::ADM;
	case Company    = PC::CPN;
	case Client     = PC::CL;
	case Customer   = PC::CT;
	case Vendor     = PC::VD;
	case Accountant = PC::ACT;

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Customer;
		$slug = mb_strtolower(trim($value));
		return self::tryFrom($slug);
	}
	public static function isValidValue(string $value): bool
	{
		return self::tryFrom($value) !== null;
	}
	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function names(): array
	{
		return array_column(self::cases(), 'name');
	}
}
