<?php

namespace App\Enums;

enum DeductionType: string
{
	case Legal      = 'legal';
	case Voluntary  = 'voluntary';
	case Judicial   = 'judicial';
	case Syndical   = 'syndical';
	case Benefit    = 'benefit';
	case Loan       = 'loan';
	case Advance    = 'advance';
	case Other      = 'other';

	public static function normalize(null|string|\BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return null;
		$k = strtolower(trim((string)$v));
		return self::tryFrom($k);
	}
}
