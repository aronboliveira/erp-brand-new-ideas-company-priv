<?php

namespace App\Enums;

enum LoanType: string
{
	case Fixed      = 'fixed';
	case Percentage = 'percentage';
	case Other 	 = 'other';

	public static function normalize(?string $v): ?self
	{
		$v = strtolower(trim((string) $v));
		return match ($v) {
			'fixed'      => self::Fixed,
			'percentage' => self::Percentage,
			'other'      => self::Other,
			default      => null,
		};
	}

	public function isPercentage(): bool
	{
		return $this === self::Percentage;
	}
}
