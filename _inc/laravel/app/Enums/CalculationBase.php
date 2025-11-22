<?php

namespace App\Enums;

use App\Config\Constants\BillsConstants as BC;

enum CalculationBase: string
{
	case GrossSalary        = BC::VL_GRS_SL;
	case NetSalary          = BC::VL_NET_SL;
	case SpecificAmount     = BC::VL_SPC_AMT;
	case Percentage         = 'percentage';
	case ProgressiveTable   = BC::VL_PRG_TBL;
	case ContributionSalary = BC::VL_CTRB_SL;
	case Mixed              = 'mixed';

	public static function normalize(null|string|\BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return null;
		$k = strtolower(trim((string)$v));
		$k = str_replace([' ', '-'], '_', $k);
		return self::tryFrom($k);
	}
}
