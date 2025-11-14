<?php

namespace App\Enums;

enum Gender: string
{
	case Male = 'male';
	case Female = 'female';
	case NonBinary = 'non_binary';
	case Other = 'other';
	case PreferNotToSay = 'prefer_not_to_say';

	public static function normalize(?string $v): ?self
	{
		if ($v === null) return null;
		$v = strtolower(trim($v));
		return match ($v) {
			'm', 'masc', 'male', 'homem'                   => self::Male,
			'f', 'fem', 'female', 'mulher'                 => self::Female,
			'nb', 'non-binary', 'não binário', 'non_binary' => self::NonBinary,
			'other', 'outro', 'outros'                    => self::Other,
			'n/a', 'na', 'prefiro não informar', 'prefer_not_to_say' => self::PreferNotToSay,
			default => null,
		};
	}
}
