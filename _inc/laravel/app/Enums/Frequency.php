<?php

namespace App\Enums;

enum Frequency: string
{
	case Monthly     = 'monthly';
	case Weekly      = 'weekly';
	case Biweekly    = 'biweekly';
	case Semimonthly = 'semimonthly';
	case Semestral   = 'semestral';
	case Annual      = 'annual';
	case Once        = 'once';
	case Variable    = 'variable';
	case Hourly      = 'hourly';

	public static function normalize(null|string|\BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return null;
		$k = strtolower(trim((string)$v));
		$aliases = [
			'semiannual'   => 'semestral',
			'semi-annual'  => 'semestral',
			'semi_monthly' => 'semimonthly',
			'semi-monthly' => 'semimonthly',
		];
		$k = $aliases[$k] ?? $k;
		return self::tryFrom($k);
	}
}
