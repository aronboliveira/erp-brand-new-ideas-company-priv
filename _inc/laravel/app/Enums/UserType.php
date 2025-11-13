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

	public static function normalize(string $value): ?self
	{
		$slug = mb_strtolower(trim($value));
		return self::tryFrom($slug);
	}
}
