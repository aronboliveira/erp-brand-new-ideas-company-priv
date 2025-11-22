<?php

namespace App\Enums;

enum SalaryType: string
{
	case CLT        = 'clt';
	case PJ         = 'pj';
	case Internship = 'internship';
	case Freelancer = 'freelancer';
	case Other      = 'other';

	public static function normalize(?string $v): ?self
	{
		if ($v === null) return null;
		$v = strtolower(trim($v));
		return match ($v) {
			'clt', 'consolidação das leis do trabalho', 'consolidation of labor laws' => self::CLT,
			'pj', 'pessoa jurídica', 'legal entity'                                   => self::PJ,
			'internship', 'estágio', 'internato'                                      => self::Internship,
			'freelancer', 'freelance', 'autônomo'                                     => self::Freelancer,
			'other', 'outro', 'outros'                                               => self::Other,
			default => self::Other,
		};
	}
}
